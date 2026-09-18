<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceController extends Controller
{
    /**
     * POST /api/face/verify — body: bytes foto (image/jpeg).
     *
     * Terusin foto ke engine MITO (ai-service Python, /api/pro/verify),
     * balikin wajah yang di-accept dengan skor paling tinggi.
     * Diproxy lewat Laravel biar HP (https) gak kena mixed-content/CORS
     * kalau manggil langsung ke server Python (http).
     */
    public function verify(Request $request): JsonResponse
    {
        $img = $request->getContent();

        if ($img === '' || strlen($img) < 500) {
            return response()->json(['ok' => false, 'message' => 'Foto kosong / kecil banget'], 422);
        }
        if (strlen($img) > 8 * 1024 * 1024) {
            return response()->json(['ok' => false, 'message' => 'Foto kegedean (maks 8 MB)'], 422);
        }

        try {
            $res = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
                ->timeout(30)
                ->send('POST', rtrim((string) config('faceid.api_base'), '/').'/api/pro/verify', ['body' => $img]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Engine wajah lagi mati — nyalain ai-service dulu (python restart.py di folder induk)',
            ], 503);
        }

        if ($res->failed()) {
            return response()->json(['ok' => false, 'message' => 'Engine wajah error (HTTP '.$res->status().')'], 502);
        }

        $faces = $res->json('faces') ?? [];

        $accepted = collect($faces)
            ->filter(fn ($f) => (($f['decision']['status'] ?? '') === 'accept'))
            ->sortByDesc(fn ($f) => $f['match']['cosine'] ?? 0)
            ->values();

        if ($accepted->isEmpty()) {
            $first = $faces[0] ?? null;
            $reason = $first['decision']['reason'] ?? null;

            return response()->json([
                'ok' => false,
                'message' => $reason ? 'Wajah gak lolos verifikasi ('.$reason.')' : 'Wajah gak dikenali',
            ]);
        }

        $best = $accepted->first();
        $faceKey = $best['match']['id'] ?? null;

        // nama kanonik dari DB (engine pakai "nama — toko" biar unik) —
        // tampil di popup absen jadi "Halo Ayu Kartika!", bukan "nama — toko"
        $canonical = $faceKey !== null
            ? Employee::where('face_key', $faceKey)->first()?->name
            : null;

        return response()->json([
            'ok' => true,
            'face_key' => $faceKey,
            'name' => $canonical ?? ($best['match']['name'] ?? null),
            'cosine' => $best['match']['cosine'] ?? null,
            'liveness' => $best['liveness']['live_prob'] ?? null,
        ]);
    }
}