<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * order_items — the line items of an order. Each links to the exact
 * product_variant bought, and snapshots the name/label/price at purchase
 * time so history stays correct even if the product later changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('product_name', 180);
            $table->string('variant_label', 60);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();

            $table->index('order_id', 'idx_oitems_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
