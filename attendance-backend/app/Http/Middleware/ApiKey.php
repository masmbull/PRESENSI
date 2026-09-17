<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = trim((string) config('faceid.api_key'));

        // FACEID_API_KEY kosong di .env = mode terbuka (buat dev lokal).
        if ($expected === '') {
            return $next($request);
        }

        $given = $request->header('X-Api-Key', $request->query('api_key', ''));

        if (! is_string($given) || $given === '' || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'API key tidak valid / hilang'], 401);
        }

        return $next($request);
    }
}