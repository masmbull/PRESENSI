<?php

use App\Http\Controllers\Admin\AttendanceAdminController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('absen');
});

// ---------- Login & logout admin ----------
Route::get('/admin/login', [LoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout')
    ->middleware('web');

// ---------- Dashboard admin (absensi masuk/pulang) ----------
Route::middleware(['admin.auth', 'role:admin,manager,supervisor'])->prefix('admin')->group(function () {
    Route::get('/', [AttendanceAdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/absensi', [AttendanceAdminController::class, 'index'])->name('admin.absensi');
    Route::get('/absensi/data', [AttendanceAdminController::class, 'data'])->name('admin.absensi.data');
    Route::get('/absensi/{attendance}/foto', [AttendanceAdminController::class, 'photo'])->name('admin.absensi.foto');
    Route::post('/absensi/manual', [AttendanceAdminController::class, 'storeManual'])->name('admin.absensi.manual');
    Route::post('/absensi/{attendance}/hapus', [AttendanceAdminController::class, 'destroy'])->name('admin.absensi.hapus');
});

// ---------- Kelola wajah & master (kota/toko/karyawan) ----------
Route::middleware(['admin.auth', 'role:admin,manager'])->prefix('kelola-wajah')->group(function () {
    Route::get('/', [\App\Http\Controllers\FaceManagementController::class, 'index']);
    Route::get('/toko', [\App\Http\Controllers\FaceManagementController::class, 'stores']);
    Route::get('/kota', [\App\Http\Controllers\FaceManagementController::class, 'cities']);
    Route::get('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'employees']);
    Route::post('/kota', [\App\Http\Controllers\FaceManagementController::class, 'createCity']);
    Route::post('/lokasi', [\App\Http\Controllers\FaceManagementController::class, 'location']);
    Route::post('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'createEmployee']);
    Route::post('/daftar', [\App\Http\Controllers\FaceManagementController::class, 'enroll']);
    Route::post('/hapus', [\App\Http\Controllers\FaceManagementController::class, 'clear']);
});