<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anti-duplikat master data:
        // - nama kota unik
        // - nama toko unik per kota
        // - nama karyawan unik per toko (store_id NULL boleh berulang)
        Schema::table('cities', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->unique(['city_id', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->unique(['store_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'name']);
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropUnique(['city_id', 'name']);
        });
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
