<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('absen');
});

Route::middleware('face.admin')->prefix('kelola-wajah')->group(function () {
    Route::get('/', [\App\Http\Controllers\FaceManagementController::class, 'index']);
    Route::get('/toko', [\App\Http\Controllers\FaceManagementController::class, 'stores']);
    Route::get('/kota', [\App\Http\Controllers\FaceManagementController::class, 'cities']);
    Route::get('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'employees']);
    Route::post('/lokasi', [\App\Http\Controllers\FaceManagementController::class, 'location']);
    Route::post('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'createEmployee']);
    Route::post('/daftar', [\App\Http\Controllers\FaceManagementController::class, 'enroll']);
    Route::post('/hapus', [\App\Http\Controllers\FaceManagementController::class, 'clear']);
});