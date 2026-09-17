<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = ['city_id', 'name', 'address', 'lat', 'lon', 'radius_m'];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lon' => 'float',
            'radius_m' => 'float',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}