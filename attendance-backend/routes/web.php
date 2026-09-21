<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AttendanceAdminController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FeatureController;
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

// ---------- Keamanan akun sendiri (semua role yang bisa login) ----------
Route::middleware(['admin.auth', 'role:admin,manager,supervisor'])->prefix('admin')->group(function () {
    Route::get('/akun', [AdminUserController::class, 'account'])->name('admin.akun');
    Route::post('/akun/password', [AdminUserController::class, 'changePassword'])->name('admin.akun.password');
});

// ---------- Menu khusus admin: kelola akun ----------
Route::middleware(['admin.auth', 'role:admin'])->prefix('admin/pengguna')->group(function () {
    Route::get('/', [AdminUserController::class, 'index'])->name('admin.pengguna');
    Route::post('/', [AdminUserController::class, 'store'])->name('admin.pengguna.tambah');
    Route::post('/{user}', [AdminUserController::class, 'update'])->name('admin.pengguna.ubah');
    Route::post('/{user}/hapus', [AdminUserController::class, 'destroy'])->name('admin.pengguna.hapus');
});

// ---------- Menu khusus admin: saklar fitur ----------
Route::middleware(['admin.auth', 'role:admin'])->prefix('admin/fitur')->group(function () {
    Route::get('/', [FeatureController::class, 'index'])->name('admin.fitur');
    Route::post('/{key}', [FeatureController::class, 'toggle'])->name('admin.fitur.toggle');
});

// ---------- Data Karyawan HRD (profil lengkap) ----------
Route::middleware(['admin.auth', 'role:admin,manager'])->prefix('admin/karyawan')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('admin.karyawan');
    Route::get('/data', [EmployeeController::class, 'data'])->name('admin.karyawan.data');
    Route::post('/', [EmployeeController::class, 'store'])->name('admin.karyawan.tambah');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('admin.karyawan.detail');
    Route::post('/{employee}', [EmployeeController::class, 'update'])->name('admin.karyawan.ubah');
    Route::post('/{employee}/hapus', [EmployeeController::class, 'destroy'])->name('admin.karyawan.hapus');
});

// ---------- Kelola wajah & master (kota/toko/karyawan) ----------
Route::middleware(['admin.auth', 'role:admin,manager'])->prefix('kelola-wajah')->group(function () {
    Route::get('/', [\App\Http\Controllers\FaceManagementController::class, 'index']);
    Route::get('/toko', [\App\Http\Controllers\FaceManagementController::class, 'stores']);
    Route::get('/kota', [\App\Http\Controllers\FaceManagementController::class, 'cities']);
    Route::get('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'employees']);
    Route::post('/kota', [\App\Http\Controllers\FaceManagementController::class, 'createCity']);
    Route::post('/lokasi', [\App\Http\Controllers\FaceManagementController::class, 'location']);
    Route::post('/lokasi/{store}', [\App\Http\Controllers\FaceManagementController::class, 'updateStore']);
    Route::post('/toko/{store}/hapus', [\App\Http\Controllers\FaceManagementController::class, 'deleteStore']);
    Route::post('/karyawan', [\App\Http\Controllers\FaceManagementController::class, 'createEmployee']);
    Route::post('/karyawan/{employee}', [\App\Http\Controllers\FaceManagementController::class, 'updateEmployee']);
    Route::post('/karyawan/{employee}/aktif', [\App\Http\Controllers\FaceManagementController::class, 'setEmployeeActive']);
    Route::post('/daftar', [\App\Http\Controllers\FaceManagementController::class, 'enroll']);
    Route::post('/hapus-satu', [\App\Http\Controllers\FaceManagementController::class, 'clearOne']);
    Route::post('/hapus', [\App\Http\Controllers\FaceManagementController::class, 'clear']);
});