<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /** GET /api/cities — daftar kota (dropdown langkah 1). */
    public function cities(): JsonResponse
    {
        return response()->json(
            City::query()->withCount('stores')->orderBy('name')->get()
        );
    }

    /** GET /api/stores?city_id=1 — daftar toko di satu kota (langkah 2). */
    public function stores(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city_id' => 'nullable|integer|exists:cities,id',
        ]);

        return response()->json(
            Store::query()
                ->with('city:id,name')
                ->withCount('employees')
                ->when($data['city_id'] ?? null, fn ($b, $c) => $b->where('city_id', $c))
                ->orderBy('name')
                ->get()
        );
    }
}