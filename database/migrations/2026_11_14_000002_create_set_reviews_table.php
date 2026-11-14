<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();              // URL slug: /blog/{slug}
            $table->string('product_slug')->nullable();   // mirrors products.slug for easy lookup
            $table->string('h1')->nullable();             // page heading
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->json('content')->nullable();          // all markdown/structured content fields
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_reviews');
    }
};
