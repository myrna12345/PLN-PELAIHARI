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
|
| Semua route web Anda di sini. Route kustom harus diletakkan 
| SEBELUM Route::resource agar tidak tertimpa.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- RUTE MATERIAL STAND BY ---
Route::get('material-stand-by/download-report', [MaterialStandByController::class, 'downloadReport'])
     ->name('material-stand-by.download-report');

// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI ANTI-SYMLINK)
Route::get('material-stand-by/foto/{materialStandBy}', [MaterialStandByController::class, 'showFoto'])
     ->name('material-stand-by.show-foto');

Route::get('material-stand-by/{materialStandBy}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');

// Rute resource diletakkan SETELAH rute kustom di atas
Route::resource('material-stand-by', MaterialStandByController::class);


// --- RUTE MATERIAL KELUAR ---
// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI ANTI-SYMLINK)
// PERBAIKAN: Mengganti {id} dengan {materialKeluar} untuk Route Model Binding
Route::get('material-keluar/foto/{materialKeluar}', [MaterialKeluarController::class, 'showFoto'])->name('material_keluar.show-foto'); 

Route::get('/material-keluar/{materialKeluar}/lihat', [MaterialKeluarController::class, 'lihat'])->name('material_keluar.lihat');

// PERBAIKAN: Menambahkan rute download foto yang hilang (Mengganti {id} menjadi {materialKeluar})
Route::get('/material-keluar/{materialKeluar}/download-foto', [MaterialKeluarController::class, 'downloadFoto'])->name('material_keluar.download-foto'); 

Route::post('/material-keluar/download', [MaterialKeluarController::class, 'downloadReport'])->name('material_keluar.download');
// PERBAIKAN: Menggunakan nama resource yang benar
Route::resource('material_keluar', MaterialKeluarController::class);


// --- RUTE MATERIAL KEMBALI ---
// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI ANTI-SYMLINK)
// PERBAIKAN: Mengganti {id} dengan {materialKembali} untuk Route Model Binding
Route::get('material-kembali/foto/{materialKembali}', [MaterialKembaliController::class, 'showFoto'])->name('material_kembali.show-foto'); 

// PERBAIKAN: Mengganti {id} dengan {materialKembali}
Route::get('/material_kembali/{materialKembali}/lihat', [MaterialKembaliController::class, 'lihat'])->name('material_kembali.lihat');

// PERBAIKAN: Menambahkan rute download foto yang hilang (Mengganti {id} menjadi {materialKembali})
Route::get('/material_kembali/{materialKembali}/download-foto', [MaterialKembaliController::class, 'downloadFoto'])->name('material_kembali.download-foto'); 

Route::post('/material_kembali/download', [MaterialKembaliController::class, 'downloadReport'])->name('material_kembali.download');
Route::resource('material_kembali', MaterialKembaliController::class);


// --- RUTE MATERIAL RETUR ---
Route::get('material-retur/download-report', [MaterialReturController::class, 'downloadReport'])
     ->name('material-retur.download-report');
Route::get('material-retur/{materialRetur}/download-foto', [MaterialReturController::class, 'downloadFoto'])
     ->name('material-retur.download-foto');

// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI SYMLINK PUTUS)
Route::get('material-retur/foto/{materialRetur}', [MaterialReturController::class, 'showFoto'])
     ->name('material-retur.show-foto');

Route::resource('material-retur', MaterialReturController::class);


// --- RUTE MATERIAL SIAGA STAND BY ---
// Rute Export Kustom (Untuk Download PDF/Excel)
Route::get('material-siaga-stand-by/export', [MaterialSiagaStandByController::class, 'export'])
    ->name('material-siaga-stand-by.export'); 
    
// Rute Foto Langsung
Route::get('material-siaga-stand-by/foto/{materialSiagaStandBy}', [MaterialSiagaStandByController::class, 'showFoto'])
    ->name('material-siaga-stand-by.show-foto');

// Rute Update Status Kustom
Route::put('material-siaga-stand-by/update-status/{id}', [MaterialSiagaStandByController::class, 'updateStatus'])
    ->name('material-siaga-stand-by.updateStatus');

Route::get('material-siaga-stand-by/{materialSiagaStandBy}/download-foto', [MaterialSiagaStandByController::class, 'downloadFoto'])
     ->name('material-siaga-stand-by.download-foto');
     
// Route Resource (HARUS PALING AKHIR di blok ini)
Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);


// --- RUTE SIAGA KELUAR ---
// Tambahkan rute download report dan foto SEBELUM resource
Route::get('siaga-keluar/download-report', [SiagaKeluarController::class, 'downloadReport'])->name('siaga-keluar.download-report');

// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI ANTI-SYMLINK)
Route::get('siaga-keluar/foto/{siagaKeluar}', [SiagaKeluarController::class, 'showFoto'])->name('siaga-keluar.show-foto');

Route::get('siaga-keluar/{siagaKeluar}/download-foto', [SiagaKeluarController::class, 'downloadFoto'])->name('siaga-keluar.download-foto');
Route::resource('siaga-keluar', SiagaKeluarController::class);


// --- RUTE SIAGA KEMBALI ---
// Tambahkan rute download report dan foto SEBELUM resource
Route::get('siaga-kembali/download-report', [SiagaKembaliController::class, 'downloadReport'])->name('siaga-kembali.download-report');

// RUTE BARU UNTUK MENAMPILKAN FOTO SECARA LANGSUNG (SOLUSI ANTI-SYMLINK)
Route::get('siaga-kembali/foto/{siagaKembali}', [SiagaKembaliController::class, 'showFoto'])->name('siaga-kembali.show-foto');

Route::get('siaga-kembali/{siagaKembali}/download-foto', [SiagaKembaliController::class, 'downloadFoto'])
     // Mengganti {id} dengan {siagaKembali} agar Route Model Binding bekerja
     ->name('siaga-kembali.download-foto'); 

Route::resource('siaga-kembali', SiagaKembaliController::class);