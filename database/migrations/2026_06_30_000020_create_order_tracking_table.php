<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * order_tracking — append-only log of every status change on an order
 * (Feature 4). One row per stage: paid, packed, shipped, delivered...
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('status', ['pending', 'paid', 'packed', 'shipped', 'delivered', 'cancelled']);
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('order_id', 'idx_tracking_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};
