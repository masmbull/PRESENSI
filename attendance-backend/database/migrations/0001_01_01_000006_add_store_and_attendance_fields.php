<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->index('store_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('type', 10)->default('masuk'); // masuk | pulang
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->float('distance_m')->nullable();
            $table->boolean('within_radius')->nullable();
            $table->index('type');
            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['type', 'store_id', 'distance_m', 'within_radius']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
    }
};