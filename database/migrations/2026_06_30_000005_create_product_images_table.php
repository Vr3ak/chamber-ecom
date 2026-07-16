<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_images — the multi-angle / informative images for a product.
 * Optionally tied to a colour so the gallery can swap when the shopper
 * picks a colour variant. Supports the "informative image" part of Feature 1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete();
            $table->string('url');
            $table->string('alt')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0); // gallery ordering
            $table->boolean('is_primary')->default(false);          // main gallery image
            $table->timestamps();

            $table->index('product_id', 'idx_images_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
