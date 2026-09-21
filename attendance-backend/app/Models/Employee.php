<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'name', 'employee_code', 'face_key', 'active', 'store_id',
        // Profil HRD
        'nik', 'birth_place', 'birth_date', 'gender', 'marital_status', 'religion',
        'education', 'phone', 'email', 'address', 'position', 'department',
        'employment_status', 'join_date', 'contract_end', 'resign_date',
        'emergency_name', 'emergency_relation', 'emergency_phone',
        'bank_name', 'bank_account', 'bank_holder', 'bpjs_health', 'bpjs_labor',
        'photo', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'birth_date' => 'date',
            'join_date' => 'date',
            'contract_end' => 'date',
            'resign_date' => 'date',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Umur (tahun) dari birth_date — null kalau tanggal lahir belum diisi. */
    public function age(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /** Masa kerja teks singkat ("2 thn 3 bln") dari join_date. */
    public function tenure(): ?string
    {
        if (! $this->join_date) return null;
        $end = $this->resign_date ? $this->resign_date->copy() : now();
        $diff = $this->join_date->diff($end);
        if ($diff->y === 0 && $diff->m === 0) return $diff->d.' hri';
        if ($diff->y === 0) return $diff->m.' bln';
        return $diff->y.' thn'.($diff->m > 0 ? ' '.$diff->m.' bln' : '');
    }
}