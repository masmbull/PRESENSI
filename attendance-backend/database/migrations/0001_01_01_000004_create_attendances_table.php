<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name', 120)->nullable();     // snapshot nama saat absen
            $table->string('status', 20)->default('unknown'); // hadir | unknown | no_face
            $table->string('face_key', 64)->nullable();
            $table->float('cosine')->nullable();
            $table->float('liveness')->nullable();
            $table->string('reason', 120)->nullable();
            $table->string('device', 120)->nullable();
            $table->double('lat', 10, 7)->nullable();
            $table->double('lon', 10, 7)->nullable();
            $table->float('acc')->nullable();
            $table->mediumText('thumb')->nullable();     // thumbnail base64 dari engine
            $table->json('attrs')->nullable();           // gender/age/emosi
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('face_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};