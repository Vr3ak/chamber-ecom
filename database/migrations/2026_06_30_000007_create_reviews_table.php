<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * reviews — a star rating (1-5) and optional body left on a product.
 * The detailed product page shows AVG(rating) and COUNT(*) per product
 * (Advanced SQL doc, section 1.1).
 *
 * order_id is nullable and links a review to the order it came from
 * ("verified purchase"). The FK is added with the orders module; kept as a
 * plain nullable column here so the Feature-1 build runs without orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // FK added with orders module
            $table->unsignedTinyInteger('rating');              // 1..5, enforced in the FormRequest
            $table->text('body')->nullable();
            $table->boolean('is_verified')->default(false);     // "verified purchase" label
            $table->timestamps();

            $table->index('product_id', 'idx_reviews_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
