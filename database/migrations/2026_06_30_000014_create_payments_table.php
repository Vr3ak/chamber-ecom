<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * payments — the money side of Feature 3. An order can have several
 * payment attempts (e.g. a failed KHQR then a successful card). `method`
 * is 'khqr' or 'credit_card'; `status` tracks the attempt's outcome.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('method', ['khqr']);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('transaction_ref', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_payments_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
