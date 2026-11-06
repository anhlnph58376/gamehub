<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('version_number');
            $table->string('storage_path');
            $table->string('entry_point')->default('index.html');
            $table->string('zip_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('extracted_size')->default(0);
            $table->unsignedInteger('file_count')->default(0);
            $table->json('manifest_data')->nullable();
            $table->json('compatibility_report')->nullable();
            $table->boolean('is_compatible')->default(true);
            $table->enum('status', ['draft', 'preview', 'published', 'archived'])->default('preview')->index();
            $table->boolean('is_active')->default(false)->index();
            $table->text('changelog')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_versions');
    }
};
