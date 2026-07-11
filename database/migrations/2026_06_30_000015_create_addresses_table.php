<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** addresses — a customer's saved shipping addresses. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_name', 120);
            $table->string('phone', 30);
            $table->string('street_line', 255);
            $table->string('city', 120);
            $table->string('province', 120)->nullable();
            $table->string('country', 120)->default('Cambodia');
            $table->boolean('is_default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
