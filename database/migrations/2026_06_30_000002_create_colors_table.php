<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * colors — the colour options a variant can have (Red, Blue, Black...).
 * Part of the variant picker on the detailed product page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->char('hex_code', 7)->nullable(); // e.g. #E24B4A for the swatch
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
