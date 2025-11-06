<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialStandByController; 
// --- TAMBAHKAN CONTROLLER BARU DI SINI ---
use App\Http\Controllers\MaterialReturController;
use App\Http\Controllers\MaterialKeluarController;
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

// --- RUTE MATERIAL STAND BY (SUDAH ADA) ---
Route::get('material-stand-by/download-pdf', [MaterialStandByController::class, 'downloadPDF'])
     ->name('material-stand-by.download-pdf');
     
Route::get('material-stand-by/{materialStandBy}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');
     
Route::resource('material-stand-by', MaterialStandByController::class);


// --- TAMBAHKAN RUTE RESOURCE BARU UNTUK SETIAP MODUL ---

// Rute untuk Material Retur
Route::resource('material-retur', MaterialReturController::class);

// Rute untuk Material Keluar
Route::resource('material-keluar', MaterialKeluarController::class);

// Rute untuk Material Kembali
Route::resource('material-kembali', MaterialKembaliController::class);

// Rute untuk Material Siaga Stand By
Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);

// Rute untuk Siaga Keluar (YANG ANDA MINTA)
Route::resource('siaga-keluar', SiagaKeluarController::class);

// Rute untuk Siaga Kembali
Route::resource('siaga-kembali', SiagaKembaliController::class);