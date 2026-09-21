<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil HRD karyawan SPG: identitas, kontak, kepegawaian, kontak darurat,
     * bank, BPJS, kontrak. Kolom lama (name/code/face_key/active/store_id) tetap.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Identitas
            $table->string('nik', 20)->nullable()->unique()->after('employee_code');
            $table->string('birth_place', 80)->nullable()->after('nik');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('gender', 2)->nullable()->after('birth_date'); // L | P
            $table->string('marital_status', 20)->nullable()->after('gender');
            $table->string('religion', 20)->nullable()->after('marital_status');
            $table->string('education', 30)->nullable()->after('religion');
            // Kontak & domisili
            $table->string('phone', 20)->nullable()->after('education');
            $table->string('email', 120)->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            // Kepegawaian
            $table->string('position', 60)->nullable()->after('address');
            $table->string('department', 60)->nullable()->after('position');
            $table->string('employment_status', 30)->nullable()->after('department');
            $table->date('join_date')->nullable()->after('employment_status');
            $table->date('contract_end')->nullable()->after('join_date');
            $table->date('resign_date')->nullable()->after('contract_end');
            // Kontak darurat
            $table->string('emergency_name', 120)->nullable()->after('resign_date');
            $table->string('emergency_relation', 40)->nullable()->after('emergency_name');
            $table->string('emergency_phone', 20)->nullable()->after('emergency_relation');
            // Bank & BPJS
            $table->string('bank_name', 40)->nullable()->after('emergency_phone');
            $table->string('bank_account', 40)->nullable()->after('bank_name');
            $table->string('bank_holder', 120)->nullable()->after('bank_account');
            $table->string('bpjs_health', 30)->nullable()->after('bank_holder');
            $table->string('bpjs_labor', 30)->nullable()->after('bpjs_health');
            // Foto profil (path relatif di disk 'public')
            $table->string('photo', 255)->nullable()->after('bpjs_labor');
            $table->text('notes')->nullable()->after('photo');

            $table->index(['active', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['active', 'store_id']);
            $table->dropUnique(['nik']);
            $table->dropColumn([
                'nik', 'birth_place', 'birth_date', 'gender', 'marital_status',
                'religion', 'education', 'phone', 'email', 'address', 'position',
                'department', 'employment_status', 'join_date', 'contract_end',
                'resign_date', 'emergency_name', 'emergency_relation', 'emergency_phone',
                'bank_name', 'bank_account', 'bank_holder', 'bpjs_health', 'bpjs_labor',
                'photo', 'notes',
            ]);
        });
    }
};
