<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // IP klien saat absen (bisa IPv4/IPv6 → maks 45 char).
            $table->string('ip', 45)->nullable()->after('device');
            $table->index('ip');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['ip']);
            $table->dropColumn('ip');
        });
    }
};
