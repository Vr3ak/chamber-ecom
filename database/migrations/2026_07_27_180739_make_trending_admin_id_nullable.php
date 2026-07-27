<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The admin panel authenticates a User (users.is_admin), while trending.admin_id
 * points at the separate Sanctum `admins` table. Featuring a product from the
 * panel can therefore have no admins row to attribute it to, so the column
 * becomes nullable — "who featured it" is metadata, not a requirement.
 *
 * Existing rows keep their admin_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trending', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trending', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });
    }
};
