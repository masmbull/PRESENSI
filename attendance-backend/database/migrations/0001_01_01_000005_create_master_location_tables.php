<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('address', 200)->nullable();
            $table->double('lat', 10, 7);
            $table->double('lon', 10, 7);
            // radius geofence khusus toko (meter) — null = pakai default dari config (150)
            $table->float('radius_m')->nullable();
            $table->timestamps();
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
        Schema::dropIfExists('cities');
    }
};