<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** wishlist_items — the products saved inside a wishlist. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained('wishlists')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index('wishlist_id', 'idx_witems_wishlist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
