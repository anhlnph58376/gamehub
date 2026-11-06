<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['music', 'sfx', 'ambient'])->default('sfx')->index();
            $table->enum('scope', ['website', 'game', 'both'])->default('both')->index();
            $table->string('event_key', 100)->nullable()->index();
            $table->string('file_path');
            $table->string('mime_type', 100)->default('audio/wav');
            $table->string('extension', 20)->default('wav');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->decimal('default_volume', 3, 2)->default(0.80);
            $table->boolean('loop')->default(false);

            // Copyright & Licensing Metadata
            $table->enum('source_type', ['project_generated', 'ai_generated', 'public_domain', 'licensed'])
                ->default('project_generated')
                ->index();
            $table->string('license_name')->default('CC0 1.0 Public Domain / Project Synthesized');
            $table->string('license_url')->nullable();
            $table->text('usage_conditions')->nullable();
            $table->string('author_attribution')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('game_audio_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('audio_asset_id')->constrained('audio_assets')->cascadeOnDelete();
            $table->string('sound_key', 100);
            $table->enum('audio_type', ['music', 'sfx', 'ambient'])->default('sfx')->index();
            $table->decimal('volume', 3, 2)->default(0.80);
            $table->boolean('loop')->default(false);
            $table->boolean('preload')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['game_id', 'sound_key']);
            $table->index(['game_id', 'is_active']);
        });

        Schema::create('user_audio_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('master_volume', 3, 2)->default(0.80);
            $table->decimal('music_volume', 3, 2)->default(0.50);
            $table->decimal('ui_volume', 3, 2)->default(0.70);
            $table->decimal('game_volume', 3, 2)->default(0.85);
            $table->boolean('mute_all')->default(false);
            $table->boolean('hover_sound_enabled')->default(true);
            $table->boolean('bgm_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_audio_settings');
        Schema::dropIfExists('game_audio_assets');
        Schema::dropIfExists('audio_assets');
    }
};

