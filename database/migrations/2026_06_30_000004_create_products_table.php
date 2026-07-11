<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products — one row per shoe model (Air Max 90, UltraBoost...).
 * Holds the data the Detailed Product Page (Feature 1) shows: name,
 * description, base price and brand. The size guide now comes from the
 * sizes table (foot_length_cm); variant colour/size/stock/price live in
 * product_variants.
 *
 * Indexes match the Advanced SQL doc:
 *   idx_products_brand (brand_id), idx_products_price (base_price).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();          // SEO / front-end route key
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);           // fallback when a variant has no price
            $table->boolean('is_active')->default(true);    // hide from catalogue without deleting
            $table->timestamps();

            $table->index('brand_id', 'idx_products_brand');
            $table->index('base_price', 'idx_products_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
