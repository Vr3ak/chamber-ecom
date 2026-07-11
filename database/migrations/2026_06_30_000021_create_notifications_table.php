<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * notifications — a record of every email/SMS sent to a customer
 * (Feature 4). Linked to an order and/or a user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('channel', ['email', 'sms']);
            $table->string('type', 60);
            $table->string('recipient', 180);
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->timestamp('sent_at')->nullable();

            $table->index('order_id', 'idx_notif_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
