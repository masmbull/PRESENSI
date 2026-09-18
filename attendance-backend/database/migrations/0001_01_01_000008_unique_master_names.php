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
        // Idempotent: aman di-re-run (SQLite DDL gak transactional per schema() call).
        $this->ensureUnique('cities', ['name']);
        $this->ensureUnique('stores', ['city_id', 'name']);
        $this->ensureUnique('employees', ['store_id', 'name']);
    }

    private function ensureUnique(string $table, array $columns): void
    {
        $index = $table.'_'.implode('_', $columns).'_unique';

        $existing = collect(Schema::getIndexes($table))->pluck('name');
        if ($existing->contains($index)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($columns) {
            $t->unique($columns);
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
