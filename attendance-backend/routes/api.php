<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FaceController;
use App\Http\Controllers\Api\MasterController;
use App\Support\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {

    Route::get('/healthz', fn () => response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
        'timezone' => config('app.timezone'),
        'face_id' => Features::on('faceid'),
        'geofence' => Features::on('geofence'),
        'radius_m' => (int) config('faceid.radius'),
    ]));

    // IP klien yang terlihat server (chip "IP kamu" di halaman absen).
    Route::get('/my-ip', fn (Request $request) => response()->json([
        'ok' => true,
        'ip' => $request->header('CF-Connecting-IP')
            ?? $request->header('X-Real-IP')
            ?? $request->ip(),
    ]));


    // verifikasi wajah (proxy ke engine MITO)
    Route::post('/face/verify', [FaceController::class, 'verify']);

    // ---------- master data (dropdown) ----------
    Route::get('/cities', [MasterController::class, 'cities']);
    Route::get('/stores', [MasterController::class, 'stores']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/no-face', [EmployeeController::class, 'noFace']);

    // ---------- karyawan ----------
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::post('/employees/sync', [EmployeeController::class, 'sync']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

    // ---------- absensi ----------
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances', [AttendanceController::class, 'store']);
    Route::get('/attendances/summary', [AttendanceController::class, 'summary']);
    Route::delete('/attendances/clear', [AttendanceController::class, 'clear']);
    Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy']);
});