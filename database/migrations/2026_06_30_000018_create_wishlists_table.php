<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** wishlists — named lists of saved products per customer. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120)->default('My Wishlist');

            $table->index('user_id', 'idx_wishlists_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
