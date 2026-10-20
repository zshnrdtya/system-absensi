<?php

use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\PiketDelegasiController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IzinApprovalController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\Siswa\IzinController as SiswaIzinController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Master Data (Khusus Administrator)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['create', 'edit', 'show']);
        Route::resource('kelas', KelasController::class)->except(['create', 'edit', 'show']);
        Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show']);
        Route::resource('mapel', MataPelajaranController::class)->except(['create', 'edit', 'show']);
        Route::resource('jadwal', JadwalController::class)->except(['create', 'edit', 'show']);
        Route::resource('piket-delegasi', PiketDelegasiController::class)->except(['create', 'edit', 'show']);
    });

    // Modul Pengajuan Izin / Sakit (Khusus Siswa)
    Route::middleware('role:siswa')->prefix('izin')->name('siswa.izin.')->group(function () {
        Route::get('/ajukan', [SiswaIzinController::class, 'create'])->name('create');
        Route::post('/ajukan', [SiswaIzinController::class, 'store'])->name('store');
        Route::delete('/ajukan/{izin}', [SiswaIzinController::class, 'destroy'])->name('destroy');
        Route::get('/riwayat', [SiswaIzinController::class, 'riwayat'])->name('riwayat');
    });

    // Modul Persetujuan Izin / Sakit (Admin, Wali Kelas, Guru Piket)
    Route::middleware('role:admin,wali_kelas,guru_piket')->prefix('izin')->name('izin.')->group(function () {
        Route::get('/approval', [IzinApprovalController::class, 'index'])->name('approval.index');
        Route::post('/approval/{izin}/approve', [IzinApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{izin}/reject', [IzinApprovalController::class, 'reject'])->name('approval.reject');
    });

    // Akses Berkas Bukti Izin Terproteksi (FR-LV-06)
    Route::get('/files/bukti-izin/{izin}', [PrivateFileController::class, 'viewBuktiIzin'])->name('files.bukti-izin');
});
