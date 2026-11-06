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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('controls')->nullable();
            $table->string('author')->nullable()->default('GameHub Studio');
            $table->string('thumbnail_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->enum('status', ['draft', 'pending', 'published', 'hidden', 'rejected'])->default('draft')->index();
            $table->string('orientation', 30)->default('landscape');
            $table->boolean('mobile_support')->default(true);
            $table->unsignedBigInteger('play_count')->default(0)->index();
            $table->unsignedBigInteger('favorite_count')->default(0);
            $table->unsignedBigInteger('review_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_hot')->default(false)->index();
            $table->boolean('is_new')->default(true)->index();
            $table->unsignedBigInteger('current_version_id')->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
