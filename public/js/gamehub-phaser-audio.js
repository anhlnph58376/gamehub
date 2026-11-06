/**
 * GameHub Phaser 3 Sound Manager Integration SDK
 * Cung cấp bộ quản lý âm thanh độc lập cho từng game sử dụng Phaser 3 Sound Manager
 * (tự động tương thích với cả Phaser.Scene.sound và standalone Phaser/WebAudio).
 */
(function (global) {
    'use strict';

    class GameHubPhaserSoundController {
        /**
         * @param {Object} options
         * @param {Phaser.Scene} [options.scene] - Phaser 3 Scene instance (nếu khởi tạo trong Scene)
         * @param {Object} [options.manifest] - Danh sách âm thanh riêng của game { key: { url, type: 'music'|'sfx'|'ambient', volume, loop, preload } }
         */
        constructor(options = {}) {
            this.scene = options.scene || null;
            this.manifest = options.manifest || {};
            this.sounds = {};
            this.currentMusicKey = null;
            this.currentMusicInstance = null;
            this.currentAmbientInstance = null;
            this.isPaused = false;
            this.unlocked = false;

            this.volumes = {
                master: 0.80,
                music: 0.50,
                game: 0.85,
                muted: false
            };

            this.loadSavedParentSettings();
            this.bindLifecycleAndParentMessages();
        }

        loadSavedParentSettings() {
            try {
                const raw = localStorage.getItem('gamehub_audio_settings_v1');
                if (raw) {
                    const parsed = JSON.parse(raw);
                    this.volumes.master = typeof parsed.master_volume === 'number' ? parsed.master_volume : 0.80;
                    this.volumes.music = typeof parsed.music_volume === 'number' ? parsed.music_volume : 0.50;
                    this.volumes.game = typeof parsed.game_volume === 'number' ? parsed.game_volume : 0.85;
                    this.volumes.muted = Boolean(parsed.mute_all);
                }
            } catch (e) {}
        }

        /**
         * Gọi trong hàm preload() của Phaser.Scene để chỉ tải các âm thanh cần thiết (preload: true)
         */
        preloadInScene(scene) {
            this.scene = scene || this.scene;
            if (!this.scene || !this.scene.load) return;

            Object.entries(this.manifest).forEach(([key, cfg]) => {
                if (cfg.preload !== false && cfg.url) {
                    this.scene.load.audio(key, [cfg.url]);
                }
            });
        }

        /**
         * Gọi trong hàm create() của Phaser.Scene để khởi tạo các đối tượng âm thanh trong Phaser Sound Manager
         */
        createInScene(scene) {
            this.scene = scene || this.scene;
            if (!this.scene || !this.scene.sound) return;

            this.scene.sound.pauseOnBlur = true;
            this.scene.sound.mute = Boolean(this.volumes.muted);

            Object.entries(this.manifest).forEach(([key, cfg]) => {
                if (this.scene.cache && this.scene.cache.audio && this.scene.cache.audio.exists(key)) {
                    this.sounds[key] = this.scene.sound.add(key, {
                        loop: Boolean(cfg.loop || cfg.type === 'music' || cfg.type === 'ambient'),
                        volume: this.computeVolume(cfg.type || 'sfx', cfg.volume ?? 0.8)
                    });
                }
            });
        }

        /**
         * Khởi tạo bộ phát HTML5/WebAudio dự phòng hoặc song hành khi game khai báo manifest trực tiếp
         */
        preloadStandalone() {
            Object.entries(this.manifest).forEach(([key, cfg]) => {
                if (!cfg.url || this.sounds[key]) return;
                const audio = new Audio();
                audio.preload = cfg.preload === false ? 'none' : 'auto';
                audio.loop = Boolean(cfg.loop || cfg.type === 'music' || cfg.type === 'ambient');
                audio.src = cfg.url;
                audio.volume = this.computeVolume(cfg.type || 'sfx', cfg.volume ?? 0.8);
                this.sounds[key] = audio;
            });
        }

        computeVolume(type, baseVol = 0.8) {
            if (this.volumes.muted) return 0;
            const channel = type === 'music' ? this.volumes.music : this.volumes.game;
            return Math.max(0, Math.min(1, this.volumes.master * channel * baseVol));
        }

        /**
         * Phát nhạc nền (Đảm bảo tuyệt đối không phát chồng nhiều bản nhạc nền lên nhau)
         */
        playMusic(key) {
            const cfg = this.manifest[key] || { type: 'music', volume: 0.45, loop: true };

            if (this.currentMusicKey === key && this.currentMusicInstance) {
                this.updateSoundVolume(key, this.currentMusicInstance, cfg);
                if (!this.isPlaying(this.currentMusicInstance) && !this.volumes.muted && !this.isPaused) {
                    this.safePlay(this.currentMusicInstance);
                }
                return;
            }

            this.stopMusic();

            const soundObj = this.sounds[key];
            if (!soundObj) return;

            this.currentMusicKey = key;
            this.currentMusicInstance = soundObj;
            this.updateSoundVolume(key, soundObj, cfg);

            if (!this.volumes.muted && !this.isPaused) {
                this.safePlay(soundObj);
            }
        }

        stopMusic() {
            if (this.currentMusicInstance) {
                try {
                    if (typeof this.currentMusicInstance.stop === 'function') {
                        this.currentMusicInstance.stop();
                    } else {
                        this.currentMusicInstance.pause();
                        this.currentMusicInstance.currentTime = 0;
                    }
                } catch (e) {}
            }
            this.currentMusicKey = null;
            this.currentMusicInstance = null;
        }

        /**
         * Phát hiệu ứng âm thanh (SFX)
         */
        playSfx(key) {
            if (this.volumes.muted || this.isPaused) return;
            const cfg = this.manifest[key] || { type: 'sfx', volume: 0.8 };
            const soundObj = this.sounds[key];
            if (!soundObj) return;

            const vol = this.computeVolume('sfx', cfg.volume ?? 0.8);
            if (vol <= 0.005) return;

            if (typeof soundObj.play === 'function' && this.scene && this.scene.sound) {
                soundObj.play({ volume: vol, loop: false });
            } else if (soundObj instanceof HTMLAudioElement) {
                try {
                    const clone = soundObj.cloneNode();
                    clone.volume = vol;
                    clone.play().catch(() => {});
                } catch (e) {}
            }
        }

        updateSoundVolume(key, soundObj, cfg) {
            const vol = this.computeVolume(cfg.type || 'sfx', cfg.volume ?? 0.8);
            if (!soundObj) return;
            if (typeof soundObj.setVolume === 'function') {
                soundObj.setVolume(vol);
            } else {
                soundObj.volume = vol;
            }
        }

        applyAllVolumes() {
            if (this.scene && this.scene.sound) {
                this.scene.sound.mute = Boolean(this.volumes.muted);
            }
            Object.entries(this.sounds).forEach(([key, soundObj]) => {
                const cfg = this.manifest[key] || { type: 'sfx', volume: 0.8 };
                this.updateSoundVolume(key, soundObj, cfg);
            });
        }

        setMute(muted) {
            this.volumes.muted = Boolean(muted);
            this.applyAllVolumes();
            if (this.volumes.muted && this.currentMusicInstance) {
                this.safePause(this.currentMusicInstance);
            } else if (!this.volumes.muted && !this.isPaused && this.currentMusicInstance) {
                this.safePlay(this.currentMusicInstance);
            }
        }

        pauseAll() {
            this.isPaused = true;
            if (this.scene && this.scene.sound && typeof this.scene.sound.pauseAll === 'function') {
                this.scene.sound.pauseAll();
            }
            if (this.currentMusicInstance) {
                this.safePause(this.currentMusicInstance);
            }
        }

        resumeAll() {
            this.isPaused = false;
            if (this.volumes.muted) return;
            if (this.scene && this.scene.sound && typeof this.scene.sound.resumeAll === 'function') {
                this.scene.sound.resumeAll();
            }
            if (this.currentMusicInstance) {
                this.safePlay(this.currentMusicInstance);
            }
        }

        stopAll() {
            this.stopMusic();
            if (this.scene && this.scene.sound && typeof this.scene.sound.stopAll === 'function') {
                this.scene.sound.stopAll();
            }
            Object.values(this.sounds).forEach((s) => {
                this.safePause(s);
            });
        }

        isPlaying(soundObj) {
            if (!soundObj) return false;
            if (typeof soundObj.isPlaying === 'boolean') return soundObj.isPlaying;
            return !soundObj.paused;
        }

        safePlay(soundObj) {
            if (!soundObj) return;
            try {
                const res = soundObj.play();
                if (res && typeof res.catch === 'function') {
                    res.catch(() => {});
                }
            } catch (e) {}
        }

        safePause(soundObj) {
            if (!soundObj) return;
            try {
                soundObj.pause();
            } catch (e) {}
        }

        bindLifecycleAndParentMessages() {
            // Lắng nghe đồng bộ âm lượng và Mute/Pause từ trang chủ GameHub
            window.addEventListener('message', (event) => {
                if (!event.data || typeof event.data !== 'object') return;

                if (event.data.type === 'GAMEHUB_MUTE_TOGGLE') {
                    this.setMute(Boolean(event.data.muted));
                } else if (event.data.type === 'GAMEHUB_AUDIO_SYNC' && event.data.settings) {
                    const s = event.data.settings;
                    if (typeof s.master_volume === 'number') this.volumes.master = s.master_volume;
                    if (typeof s.music_volume === 'number') this.volumes.music = s.music_volume;
                    if (typeof s.game_volume === 'number') this.volumes.game = s.game_volume;
                    this.setMute(Boolean(s.mute_all));
                } else if (event.data.type === 'GAMEHUB_PAUSE') {
                    this.pauseAll();
                } else if (event.data.type === 'GAMEHUB_RESUME') {
                    this.resumeAll();
                }
            });

            // Tự động Pause khi ẩn tab và dừng hẳn nhạc khi rời game
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseAll();
                } else {
                    this.resumeAll();
                }
            });

            window.addEventListener('pagehide', () => this.stopAll());
            window.addEventListener('beforeunload', () => this.stopAll());
        }
    }

    global.GameHubPhaserSoundController = GameHubPhaserSoundController;
})(window);

