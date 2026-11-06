/**
 * GameHub Master Website Audio Engine (Powered by Howler.js)
 * Quản lý âm thanh giao diện, nhạc nền theo khu vực (Zone BGM),
 * Global Audio Settings (localStorage + Account DB Sync) và đồng bộ Iframe Game.
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'gamehub_audio_settings_v1';

    const DEFAULT_SETTINGS = {
        master_volume: 0.80,
        music_volume: 0.50,
        ui_volume: 0.70,
        game_volume: 0.85,
        mute_all: false,
        hover_sound_enabled: true,
        bgm_enabled: false
    };

    class GameHubAudioManager {
        constructor() {
            this.settings = this.loadLocalSettings();
            this.manifest = {};
            this.howls = {};
            this.currentBgmKey = null;
            this.currentBgmHowl = null;
            this.unlocked = false;
            this.lastHoverAt = 0;
            this.syncTimer = null;
            this.zone = document.body?.dataset?.audioZone || 'default';

            this.setupUnlockListeners();
            this.setupDelegatedUiEvents();
            this.fetchManifestAndServerSettings();
        }

        loadLocalSettings() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return { ...DEFAULT_SETTINGS };
                const parsed = JSON.parse(raw);
                return {
                    master_volume: this.clamp(parsed.master_volume ?? DEFAULT_SETTINGS.master_volume),
                    music_volume: this.clamp(parsed.music_volume ?? DEFAULT_SETTINGS.music_volume),
                    ui_volume: this.clamp(parsed.ui_volume ?? DEFAULT_SETTINGS.ui_volume),
                    game_volume: this.clamp(parsed.game_volume ?? DEFAULT_SETTINGS.game_volume),
                    mute_all: Boolean(parsed.mute_all ?? DEFAULT_SETTINGS.mute_all),
                    hover_sound_enabled: Boolean(parsed.hover_sound_enabled ?? DEFAULT_SETTINGS.hover_sound_enabled),
                    bgm_enabled: Boolean(parsed.bgm_enabled ?? DEFAULT_SETTINGS.bgm_enabled),
                };
            } catch (e) {
                return { ...DEFAULT_SETTINGS };
            }
        }

        saveLocalSettings() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(this.settings));
            } catch (e) {}
        }

        clamp(val) {
            const num = parseFloat(val);
            if (Number.isNaN(num)) return 0.8;
            return Math.max(0, Math.min(1, Math.round(num * 100) / 100));
        }

        async fetchManifestAndServerSettings() {
            try {
                const apiUrl = window.GAMEHUB_AUDIO_API_URL || '/api/audio-settings';
                const res = await fetch(apiUrl, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.manifest && typeof data.manifest === 'object') {
                    this.manifest = data.manifest;
                }

                // Nếu người dùng đã đăng nhập, đồng bộ cấu hình từ tài khoản
                if (data.authenticated && data.settings) {
                    this.settings = {
                        ...this.settings,
                        master_volume: this.clamp(data.settings.master_volume),
                        music_volume: this.clamp(data.settings.music_volume),
                        ui_volume: this.clamp(data.settings.ui_volume),
                        game_volume: this.clamp(data.settings.game_volume),
                        mute_all: Boolean(data.settings.mute_all),
                        hover_sound_enabled: Boolean(data.settings.hover_sound_enabled),
                        bgm_enabled: Boolean(data.settings.bgm_enabled),
                    };
                    this.saveLocalSettings();
                }

                this.applyVolumes();
                window.dispatchEvent(new CustomEvent('gamehub:audio-updated', { detail: this.settings }));

                if (this.unlocked) {
                    this.evaluateZoneBgm();
                }
            } catch (e) {}
        }

        setupUnlockListeners() {
            const unlockHandler = () => {
                if (this.unlocked) return;
                this.unlocked = true;

                if (window.Howler && window.Howler.ctx && window.Howler.ctx.state === 'suspended') {
                    window.Howler.ctx.resume().catch(() => {});
                }

                this.evaluateZoneBgm();
                this.syncToGameIframe();
            };

            window.addEventListener('pointerdown', unlockHandler, { passive: true });
            window.addEventListener('keydown', unlockHandler, { passive: true });
            window.addEventListener('touchstart', unlockHandler, { passive: true });

            // Dừng nhạc nền khi người dùng chuyển tab ẩn hoặc rời trang
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    if (this.currentBgmHowl && this.currentBgmHowl.playing()) {
                        this.currentBgmHowl.pause();
                    }
                } else if (this.unlocked && this.settings.bgm_enabled && !this.settings.mute_all && this.zone !== 'game-player') {
                    if (this.currentBgmHowl && !this.currentBgmHowl.playing()) {
                        this.currentBgmHowl.play();
                    }
                }
            });
        }

        setupDelegatedUiEvents() {
            // Click sound trên các nút bấm và liên kết điều hướng
            document.addEventListener('click', (e) => {
                const target = e.target.closest('button, a, [data-audio-click]');
                if (!target) return;
                this.unlocked = true;
                this.playUi('ui.click');
            }, { passive: true });

            // Hover sound có throttle chống lặp âm thanh
            document.addEventListener('mouseover', (e) => {
                if (!this.unlocked || !this.settings.hover_sound_enabled || this.settings.mute_all) return;
                const target = e.target.closest('a.group, button, nav a, [data-audio-hover]');
                if (!target) return;

                const now = performance.now();
                if (now - this.lastHoverAt < 95) return;
                this.lastHoverAt = now;
                this.playUi('ui.hover');
            }, { passive: true });
        }

        getOrCreateHowl(eventKey) {
            if (!window.Howl) return null;
            const item = this.manifest[eventKey];
            if (!item || !item.url) return null;

            if (!this.howls[eventKey]) {
                const isMusic = item.category === 'music' || item.loop;
                this.howls[eventKey] = new window.Howl({
                    src: [item.url],
                    loop: Boolean(item.loop),
                    preload: true,
                    html5: false,
                    volume: this.computeEffectiveVolume(isMusic ? 'music' : 'ui', item.volume ?? 0.8)
                });
            }

            return this.howls[eventKey];
        }

        computeEffectiveVolume(channel, assetDefaultVol = 0.8) {
            if (this.settings.mute_all) return 0;
            const master = this.settings.master_volume;
            const channelVol = channel === 'music'
                ? this.settings.music_volume
                : (channel === 'game' ? this.settings.game_volume : this.settings.ui_volume);
            return Math.max(0, Math.min(1, master * channelVol * assetDefaultVol));
        }

        playUi(eventKey) {
            if (!this.unlocked || this.settings.mute_all) return;
            const howl = this.getOrCreateHowl(eventKey);
            if (!howl) return;

            const item = this.manifest[eventKey] || {};
            const vol = this.computeEffectiveVolume('ui', item.volume ?? 0.75);
            if (vol <= 0.005) return;

            howl.volume(vol);
            howl.play();
        }

        notify(type = 'success') {
            this.playUi(type === 'error' ? 'ui.error' : 'ui.success');
        }

        modalOpen() {
            this.playUi('ui.modal_open');
        }

        modalClose() {
            this.playUi('ui.modal_close');
        }

        /**
         * Quản lý nhạc nền theo từng khu vực (Homepage / Explore) và tắt hẳn khi vào Game Player
         */
        evaluateZoneBgm() {
            this.zone = document.body?.dataset?.audioZone || 'default';

            // Không bao giờ phát nhạc nền website đè lên nhạc nền của Game đang chơi
            if (this.zone === 'game-player' || !this.settings.bgm_enabled || this.settings.mute_all || !this.unlocked) {
                this.stopBgmWithFade();
                return;
            }

            const desiredKey = this.zone === 'explore' ? 'bgm.explore' : 'bgm.home';
            const fallbackKey = this.manifest[desiredKey] ? desiredKey : (this.manifest['bgm.home'] ? 'bgm.home' : null);

            if (!fallbackKey) {
                this.stopBgmWithFade();
                return;
            }

            if (this.currentBgmKey === fallbackKey && this.currentBgmHowl) {
                const item = this.manifest[fallbackKey] || {};
                const targetVol = this.computeEffectiveVolume('music', item.volume ?? 0.4);
                this.currentBgmHowl.volume(targetVol);
                if (!this.currentBgmHowl.playing()) {
                    this.currentBgmHowl.play();
                }
                return;
            }

            // Nếu đang phát bản nhạc nền khác, dừng bản cũ trước khi phát bản mới
            this.stopBgmImmediate();

            const howl = this.getOrCreateHowl(fallbackKey);
            if (!howl) return;

            const item = this.manifest[fallbackKey] || {};
            const targetVol = this.computeEffectiveVolume('music', item.volume ?? 0.4);

            this.currentBgmKey = fallbackKey;
            this.currentBgmHowl = howl;
            howl.volume(0);
            howl.play();
            howl.fade(0, targetVol, 500);
        }

        stopBgmWithFade() {
            if (!this.currentBgmHowl) return;
            const howlToStop = this.currentBgmHowl;
            this.currentBgmKey = null;
            this.currentBgmHowl = null;
            try {
                howlToStop.stop();
            } catch (e) {}
        }

        stopBgmImmediate() {
            if (this.currentBgmHowl) {
                try {
                    this.currentBgmHowl.stop();
                } catch (e) {}
            }
            this.currentBgmKey = null;
            this.currentBgmHowl = null;
        }

        updateSettings(partial) {
            this.unlocked = true;
            this.settings = {
                ...this.settings,
                ...partial,
                master_volume: this.clamp(partial.master_volume ?? this.settings.master_volume),
                music_volume: this.clamp(partial.music_volume ?? this.settings.music_volume),
                ui_volume: this.clamp(partial.ui_volume ?? this.settings.ui_volume),
                game_volume: this.clamp(partial.game_volume ?? this.settings.game_volume),
            };

            this.saveLocalSettings();
            this.applyVolumes();
            this.evaluateZoneBgm();
            this.syncToGameIframe();
            this.persistSettingsToServerDebounced();

            window.dispatchEvent(new CustomEvent('gamehub:audio-updated', { detail: this.settings }));
        }

        applyVolumes() {
            if (window.Howler) {
                window.Howler.mute(Boolean(this.settings.mute_all));
            }
            if (this.currentBgmHowl && this.currentBgmKey) {
                const item = this.manifest[this.currentBgmKey] || {};
                const targetVol = this.computeEffectiveVolume('music', item.volume ?? 0.4);
                this.currentBgmHowl.volume(targetVol);
            }
        }

        syncToGameIframe() {
            const iframe = document.getElementById('game-sandbox-iframe');
            if (!iframe || !iframe.contentWindow) return;

            iframe.contentWindow.postMessage({
                type: 'GAMEHUB_AUDIO_SYNC',
                settings: {
                    master_volume: this.settings.master_volume,
                    music_volume: this.settings.music_volume,
                    game_volume: this.settings.game_volume,
                    mute_all: this.settings.mute_all
                }
            }, '*');
        }

        persistSettingsToServerDebounced() {
            if (this.syncTimer) clearTimeout(this.syncTimer);
            this.syncTimer = setTimeout(async () => {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrf) return;
                try {
                    const apiUrl = window.GAMEHUB_AUDIO_API_URL || '/api/audio-settings';
                    await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.settings)
                    });
                } catch (e) {}
            }, 350);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        window.GameHubAudio = new GameHubAudioManager();
    });
})();

