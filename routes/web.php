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
Route::resource('material-stand-by', MaterialStandByController::class);

// ROUTES UNTUK MATERIAL KELUAR
Route::get('/material_keluar', [MaterialKeluarController::class, 'index'])->name('material_keluar.index');      // Tampilkan tabel
Route::get('/material_keluar/create', [MaterialKeluarController::class, 'create'])->name('material_keluar.create'); // Form tambah
Route::post('/material_keluar/store', [MaterialKeluarController::class, 'store'])->name('material_keluar.store');   // Simpan data
Route::get('/material-keluar/{id}/lihat', [MaterialKeluarController::class, 'lihat'])->name('material_keluar.lihat'); // lihat data
Route::get('/material_keluar/{id}/edit', [MaterialKeluarController::class, 'edit'])->name('material_keluar.edit');  // Form edit
Route::put('/material_keluar/{id}', [MaterialKeluarController::class, 'update'])->name('material_keluar.update');   // Update data
Route::delete('/material_keluar/{id}', [MaterialKeluarController::class, 'destroy'])->name('material_keluar.destroy'); // Hapus data
Route::post('/material_keluar/download', [MaterialKeluarController::class, 'downloadReport'])
    ->name('material_keluar.download');

// Material Kembali
Route::get('/material_kembali', [MaterialKembaliController::class, 'index'])->name('material_kembali.index');
Route::get('/material_kembali/create', [MaterialKembaliController::class, 'create'])->name('material_kembali.create');
Route::post('/material_kembali/store', [MaterialKembaliController::class, 'store'])->name('material_kembali.store');
Route::get('/material_kembali/{id}/lihat', [MaterialKembaliController::class, 'lihat'])->name('material_kembali.lihat');
Route::get('/material_kembali/{id}/edit', [MaterialKembaliController::class, 'edit'])->name('material_kembali.edit');
Route::put('/material_kembali/{id}', [MaterialKembaliController::class, 'update'])->name('material_kembali.update');
Route::delete('/material_kembali/{id}', [MaterialKembaliController::class, 'destroy'])->name('material_kembali.destroy');
Route::post('/material_kembali/download', [MaterialKembaliController::class, 'downloadReport'])->name('material_kembali.download');
    
// --- RUTE MATERIAL STAND BY (SUDAH ADA) ---
Route::get('material-stand-by/download-pdf', [MaterialStandByController::class, 'downloadPDF'])
     ->name('material-stand-by.download-pdf');
Route::get('material-stand-by/download-report', [MaterialStandByController::class, 'downloadReport'])
     ->name('material-stand-by.download-report');
     
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
