<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * sizes — the shoe sizes (label = "42"). `foot_length_cm` feeds the
 * foot-size guide on the product page; `sort_order` sets picker order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('label', 20)->unique();               // shopper-facing, e.g. "42"
            $table->decimal('foot_length_cm', 4, 1)->nullable();  // for the size guide
            $table->integer('sort_order')->default(0);            // display order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
