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
        Schema::create('seo_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_id')->nullable()->constrained('seo_metadata')->onDelete('cascade');
            $table->string('url')->index();
            $table->float('score')->default(0);
            $table->integer('content_length')->nullable();
            $table->float('keyword_density')->nullable();
            $table->float('readability_score')->nullable();
            $table->boolean('has_meta_title')->default(false);
            $table->boolean('has_meta_description')->default(false);
            $table->boolean('has_meta_keywords')->default(false);
            $table->boolean('has_canonical')->default(false);
            $table->boolean('has_h1')->default(false);
            $table->boolean('has_images_alt')->default(false);
            $table->boolean('has_broken_links')->default(false);
            $table->float('load_time')->nullable();
            $table->json('issues')->nullable();
            $table->json('suggestions')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_analyses');
    }
}; 