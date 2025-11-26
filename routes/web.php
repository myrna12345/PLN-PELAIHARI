<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialKeluarController;
use App\Http\Controllers\MaterialStandByController; 
use App\Http\Controllers\MaterialReturController;
use App\Http\Controllers\MaterialKembaliController;
use App\Http\Controllers\MaterialSiagaStandByController;
use App\Http\Controllers\SiagaKeluarController;
use App\Http\Controllers\SiagaKembaliController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- RUTE MATERIAL STAND BY ---
// PENTING: Rute kustom HARUS diletakkan SEBELUM Route::resource agar tidak tertimpa.
Route::get('material-stand-by/download-report', [MaterialStandByController::class, 'downloadReport'])
     ->name('material-stand-by.download-report');

Route::get('material-stand-by/{materialStandBy}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');

// Rute resource diletakkan SETELAH rute kustom di atas
Route::resource('material-stand-by', MaterialStandByController::class);


// --- RUTE MATERIAL KELUAR ---
Route::get('/material-keluar/{id}/lihat', [MaterialKeluarController::class, 'lihat'])->name('material_keluar.lihat');
Route::post('/material_keluar/download', [MaterialKeluarController::class, 'downloadReport'])->name('material_keluar.download');
Route::resource('material_keluar', MaterialKeluarController::class);


// --- RUTE MATERIAL KEMBALI ---
Route::get('/material_kembali/{id}/lihat', [MaterialKembaliController::class, 'lihat'])->name('material_kembali.lihat');
Route::post('/material_kembali/download', [MaterialKembaliController::class, 'downloadReport'])->name('material_kembali.download');
Route::resource('material_kembali', MaterialKembaliController::class);


// --- RUTE MATERIAL RETUR ---
Route::get('material-retur/download-report', [MaterialReturController::class, 'downloadReport'])
     ->name('material-retur.download-report');
Route::get('material-retur/{materialRetur}/download-foto', [MaterialReturController::class, 'downloadFoto'])
     ->name('material-retur.download-foto');
Route::resource('material-retur', MaterialReturController::class);


// --- RUTE LAINNYA ---
Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);
Route::resource('siaga-keluar', SiagaKeluarController::class);
Route::resource('siaga-kembali', SiagaKembaliController::class);


// Rute untuk Halaman Material Siaga Stand By
Route::get('/material-siaga', [MaterialSiagaStandByController::class, 'index'])->name('material-siaga.index');
Route::get('/material-siaga/create', [MaterialSiagaStandByController::class, 'create'])->name('material-siaga.create');
Route::post('/material-siaga', [MaterialSiagaStandByController::class, 'store'])->name('material-siaga.store');
Route::delete('/material-siaga/{id}', [MaterialSiagaStandByController::class, 'destroy'])->name('material-siaga.destroy');
Route::put('/material-siaga/update-status/{id}', [MaterialSiagaStandByController::class, 'updateStatus'])->name('material-siaga.update-status');
Route::get('/material-siaga/export', [MaterialSiagaStandByController::class, 'export'])->name('material-siaga.export');
Route::get('/material-siaga/{id}/edit', [MaterialSiagaStandByController::class, 'edit'])->name('material-siaga.edit');
Route::put('/material-siaga/{id}', [MaterialSiagaStandByController::class, 'update'])->name('material-siaga.update');
Route::get('/material-siaga/tambah', [MaterialSiagaStandByController::class, 'create'])
        ->name('materialsiaga.tambah');