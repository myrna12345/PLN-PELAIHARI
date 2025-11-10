<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialStandByController; 
use App\Http\Controllers\MaterialSiagaStandByController;
use App\Http\Controllers\MaterialReturController;
use App\Http\Controllers\MaterialKeluarController;
use App\Http\Controllers\MaterialKembaliController;
use App\Http\Controllers\SiagaKeluarController;
use App\Http\Controllers\SiagaKembaliController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- Material Stand By ---
// Rute spesifik (download) HARUS diletakkan DI ATAS route resource
Route::get('material-stand-by/download-report', [MaterialStandByController::class, 'downloadReport'])
     ->name('material-stand-by.download-report');
Route::get('material-stand-by/{materialStandBy}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');

Route::resource('material-stand-by', MaterialStandByController::class);


// Rute untuk Halaman Material Siaga Stand By
Route::get('/material-siaga', [MaterialSiagaStandByController::class, 'index'])->name('material-siaga.index');
Route::get('/material-siaga/create', [MaterialSiagaStandByController::class, 'create'])->name('material-siaga.create');
Route::post('/material-siaga', [MaterialSiagaStandByController::class, 'store'])->name('material-siaga.store');
Route::delete('/material-siaga/{id}', [MaterialSiagaStandByController::class, 'destroy'])->name('material-siaga.destroy');
Route::put('/material-siaga/update-status/{id}', [MaterialSiagaStandByController::class, 'updateStatus'])->name('material-siaga.update-status');
Route::get('/material-siaga/export', [MaterialSiagaStandByController::class, 'export'])->name('material-siaga.export');
Route::get('/material-siaga/{id}/edit', [MaterialSiagaStandByController::class, 'edit'])->name('material-siaga.edit');
Route::put('/material-siaga/{id}', [MaterialSiagaStandByController::class, 'update'])->name('material-siaga.update');
Route::resource('material-stand-by', MaterialStandByController::class);


// --- Material Retur ---
Route::get('material-retur/download-report', [MaterialReturController::class, 'downloadReport'])
     ->name('material-retur.download-report');
Route::get('material-retur/{materialRetur}/download-foto', [MaterialReturController::class, 'downloadFoto'])
     ->name('material-retur.download-foto');
Route::resource('material-retur', MaterialReturController::class);


// --- Material Keluar ---
// Rute manual yang duplikat (baris 20-36) telah dihapus.
// Tambahkan route download/foto di sini jika perlu.
Route::resource('material-keluar', MaterialKeluarController::class);


// --- Material Kembali ---
// Rute manual yang duplikat (baris 20-36) telah dihapus.
// Tambahkan route download/foto di sini jika perlu.
Route::resource('material-kembali', MaterialKembaliController::class);


// --- Rute Siaga ---
Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);
Route::resource('siaga-keluar', SiagaKeluarController::class);
Route::resource('siaga-kembali', SiagaKembaliController::class);