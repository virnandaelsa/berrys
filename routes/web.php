<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard.index');

Route::get('/karyawan/riwayat', [KaryawanController::class, 'riwayat'])->name('karyawan.riwayat');
Route::resource('karyawan', KaryawanController::class);
Route::get('/karyawan/{id}', [KaryawanController::class, 'show']);
Route::put('/karyawan/{id}', [KaryawanController::class, 'update']);

Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
Route::get('/jadwal/create', [JadwalController::class, 'create'])->name('jadwal.create');
Route::post('/jadwal/store', [JadwalController::class, 'store'])->name('jadwal.store');
Route::put('/jadwal/update', [JadwalController::class, 'update'])->name('jadwal.update');
Route::get('/jadwal/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');

Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
Route::put('/cuti/{id}', [CutiController::class, 'update'])->name('cuti.update');

// Route untuk laporan donat
Route::match(['get', 'post'], '/penjualan/laporan-donat', [PenjualanController::class, 'laporanDonat'])->name('laporan.donat');

// Route untuk detail laporan donat
Route::get('/laporan-donat/detail', [PenjualanController::class, 'detail'])->name('laporan.donat.detail');
Route::get('/penjualan/pendapatan', [PenjualanController::class, 'pendapatan'])->name('laporan.pendapatan');

// Route untuk pendapatan
Route::get('/pendapatan', [PenjualanController::class, 'pendapatan'])->name('pendapatan');

Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
Route::get('/penggajian/karyawan/{id}', [PenggajianController::class, 'showByKaryawan'])->name('penggajian.detail');
Route::get('/karyawan/{id_karyawan}/bulan-tahun', [PenggajianController::class, 'getByMonthYear']);
Route::post('/penggajian/edit-gaji', [PenggajianController::class, 'tambahEditGaji'])->name('penggajian.tambahEditGaji');
Route::get('/penggajian/edit/{id_karyawan}', [PenggajianController::class, 'formEditGaji'])->name('penggajian.edit');

Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');



