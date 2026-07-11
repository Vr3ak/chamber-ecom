<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_variants — a buyable product + colour + size combination.
 * Carries stock and (optionally) its own price. When price is null the
 * product's base_price applies (the doc's COALESCE(v.price, p.base_price)).
 *
 * Indexes match the Advanced SQL doc:
 *   idx_variants_color (color_id), idx_variants_size (size_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('color_id')->constrained('colors')->restrictOnDelete();
            $table->foreignId('size_id')->constrained('sizes')->restrictOnDelete();
            $table->decimal('price', 10, 2)->nullable();      // null => use product.base_price
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->timestamps();

            // one colour+size combination can only exist once per product
            $table->unique(['product_id', 'color_id', 'size_id'], 'uq_variant_combo');

            $table->index('color_id', 'idx_variants_color');
            $table->index('size_id', 'idx_variants_size');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
