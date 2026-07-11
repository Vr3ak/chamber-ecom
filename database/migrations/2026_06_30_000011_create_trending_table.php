<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * trending — admin-curated "featured" shoes. One row per product; the
 * product page shows a Trending badge and the homepage lists them by
 * sort_order. Managed by admins (fk to admins).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trending', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('product_id', 'uq_trending_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trending');
    }
};
