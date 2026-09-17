<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'store_id', 'name', 'status', 'type', 'face_key', 'cosine',
        'liveness', 'reason', 'device', 'lat', 'lon', 'acc', 'distance_m',
        'within_radius', 'thumb', 'attrs',
    ];

    protected function casts(): array
    {
        return [
            'cosine' => 'float',
            'liveness' => 'float',
            'lat' => 'float',
            'lon' => 'float',
            'acc' => 'float',
            'distance_m' => 'float',
            'within_radius' => 'boolean',
            'attrs' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}