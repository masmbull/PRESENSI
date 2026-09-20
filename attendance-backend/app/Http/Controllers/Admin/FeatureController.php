<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Features;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Saklar fitur (face id / geo location / anti dobel) dari sidebar admin. */
class FeatureController extends Controller
{
    /** GET /admin/fitur — daftar fitur + status (dipakai refresh setelah aksi). */
    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'features' => Features::list()]);
    }

    /** POST /admin/fitur/{key} — nyalain/matiin satu fitur. body: {on: bool} */
    public function toggle(Request $request, string $key): JsonResponse
    {
        if (! array_key_exists($key, Features::DEFS)) {
            return response()->json(['ok' => false, 'message' => 'Fitur tidak dikenal: '.$key], 404);
        }

        $data = $request->validate(['on' => 'required|boolean']);
        $on = Features::set($key, (bool) $data['on']);

        return response()->json([
            'ok' => true,
            'features' => Features::list(),
            'message' => Features::DEFS[$key]['label'].($on ? ' diaktifkan.' : ' dimatikan.'),
        ]);
    }
}
