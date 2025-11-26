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


<<<<<<< HEAD
// --- RUTE SIAGA KELUAR  ---
=======
// --- RUTE MATERIAL SIAGA STAND BY ---
>>>>>>> ea3570bcfd672a9fd9b075f13525cbbd3c2c5d89
Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);


// --- RUTE SIAGA KELUAR ---
// Tambahkan rute download report dan foto SEBELUM resource
Route::get('siaga-keluar/download-report', [SiagaKeluarController::class, 'downloadReport'])->name('siaga-keluar.download-report');
Route::get('siaga-keluar/{siagaKeluar}/download-foto', [SiagaKeluarController::class, 'downloadFoto'])->name('siaga-keluar.download-foto');
Route::resource('siaga-keluar', SiagaKeluarController::class);
<<<<<<< HEAD
//RUTE SIAGA KEMBALI
=======


// --- RUTE SIAGA KEMBALI ---
// Tambahkan rute download report dan foto SEBELUM resource
Route::get('siaga-kembali/download-report', [SiagaKembaliController::class, 'downloadReport'])->name('siaga-kembali.download-report');
Route::get('siaga-kembali/{id}/download-foto', [SiagaKembaliController::class, 'downloadFoto'])->name('siaga-kembali.download-foto');
>>>>>>> ea3570bcfd672a9fd9b075f13525cbbd3c2c5d89
Route::resource('siaga-kembali', SiagaKembaliController::class);