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
Route::get('material-stand-by/download-report', [MaterialStandByController::class, 'downloadReport'])
     ->name('material-stand-by.download-report');

Route::get('material-stand-by/foto/{id}', [MaterialStandByController::class, 'showFoto'])
     ->name('material-stand-by.show-foto');

Route::get('material-stand-by/{id}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');

Route::get('material-stand-by/{id}/download-petugas', [MaterialStandByController::class, 'downloadFotoPetugas'])
     ->name('material-stand-by.download-foto-petugas');

Route::resource('material-stand-by', MaterialStandByController::class);


// --- RUTE MATERIAL KELUAR ---
Route::get('material-keluar/foto/{materialKeluar}', [MaterialKeluarController::class, 'showFoto'])
     ->name('material_keluar.show-foto'); 

Route::get('/material-keluar/{materialKeluar}/lihat', [MaterialKeluarController::class, 'lihat'])
     ->name('material_keluar.lihat');

Route::get('/material-keluar/{materialKeluar}/download-foto', [MaterialKeluarController::class, 'downloadFoto'])
     ->name('material_keluar.download-foto'); 

Route::post('/material-keluar/download', [MaterialKeluarController::class, 'downloadReport'])
     ->name('material_keluar.download');

Route::resource('material_keluar', MaterialKeluarController::class);


// --- RUTE MATERIAL KEMBALI ---
Route::get('material-kembali/foto/{materialKembali}', [MaterialKembaliController::class, 'showFoto'])
     ->name('material_kembali.show-foto'); 

Route::get('/material_kembali/{materialKembali}/lihat', [MaterialKembaliController::class, 'lihat'])
     ->name('material_kembali.lihat');

Route::get('/material_kembali/{materialKembali}/download-foto', [MaterialKembaliController::class, 'downloadFoto'])
     ->name('material_kembali.download-foto'); 

Route::post('/material_kembali/download', [MaterialKembaliController::class, 'downloadReport'])
     ->name('material_kembali.download');

Route::resource('material_kembali', MaterialKembaliController::class);


// --- RUTE MATERIAL RETUR ---
Route::get('material-retur/download-report', [MaterialReturController::class, 'downloadReport'])
     ->name('material-retur.download-report');

Route::get('material-retur/{id}/download-foto', [MaterialReturController::class, 'downloadFoto'])
     ->name('material-retur.download-foto');

Route::get('material-retur/foto/{id}', [MaterialReturController::class, 'showFoto'])
     ->name('material-retur.show-foto');

Route::get('material-retur/{id}/download-petugas', [MaterialReturController::class, 'downloadFotoPetugas'])
     ->name('material-retur.download-foto-petugas');

Route::resource('material-retur', MaterialReturController::class);


// --- RUTE MATERIAL SIAGA STAND BY ---
Route::get('material-siaga-stand-by/export', [MaterialSiagaStandByController::class, 'export'])
    ->name('material-siaga-stand-by.export'); 
    
Route::get('material-siaga-stand-by/foto/{materialSiagaStandBy}', [MaterialSiagaStandByController::class, 'showFoto'])
    ->name('material-siaga-stand-by.show-foto');

Route::get('material-siaga-stand-by/{materialSiagaStandBy}/download-foto', [MaterialSiagaStandByController::class, 'downloadFoto'])
     ->name('material-siaga-stand-by.download-foto');

Route::put('material-siaga-stand-by/update-status/{id}', [MaterialSiagaStandByController::class, 'updateStatus'])
    ->name('material-siaga-stand-by.updateStatus');

Route::resource('material-siaga-stand-by', MaterialSiagaStandByController::class);


// --- RUTE SIAGA KELUAR ---
Route::get('siaga-keluar/download-report', [SiagaKeluarController::class, 'downloadReport'])
     ->name('siaga-keluar.download-report');

Route::get('siaga-keluar/foto/{siagaKeluar}', [SiagaKeluarController::class, 'showFoto'])
     ->name('siaga-keluar.show-foto');

Route::get('siaga-keluar/{siagaKeluar}/download-foto', [SiagaKeluarController::class, 'downloadFoto'])
     ->name('siaga-keluar.download-foto');

Route::get('siaga-keluar/{siagaKeluar}/download-foto-petugas', [SiagaKeluarController::class, 'downloadFotoPetugas'])
     ->name('siaga-keluar.download-foto-petugas');

Route::resource('siaga-keluar', SiagaKeluarController::class);


// --- RUTE SIAGA KEMBALI ---
Route::get('siaga-kembali/download-report', [SiagaKembaliController::class, 'downloadReport'])
     ->name('siaga-kembali.download-report');

Route::get('siaga-kembali/foto/{siagaKembali}', [SiagaKembaliController::class, 'showFoto'])
     ->name('siaga-kembali.show-foto');

Route::get('siaga-kembali/{siagaKembali}/download-foto', [SiagaKembaliController::class, 'downloadFoto'])
     ->name('siaga-kembali.download-foto'); 

// [BARU] Tambahan Route Download Foto Petugas Siaga Kembali
Route::get('siaga-kembali/{siagaKembali}/download-foto-petugas', [SiagaKembaliController::class, 'downloadFotoPetugas'])
     ->name('siaga-kembali.download-foto-petugas');

Route::resource('siaga-kembali', SiagaKembaliController::class);


// --- RUTE MATERIAL SIAGA (PARAMETER CUSTOM) ---
Route::get('material-siaga/export', [MaterialSiagaStandByController::class, 'export'])
    ->name('material-siaga.export'); 

Route::get('material-siaga/foto/{id}', [MaterialSiagaStandByController::class, 'showFoto'])
    ->name('material-siaga.show-foto');

Route::get('material-siaga/download-foto/{id}', [MaterialSiagaStandByController::class, 'downloadFoto'])
    ->name('material-siaga.download-foto');

Route::put('material-siaga/update-status/{id}', [MaterialSiagaStandByController::class, 'updateStatus'])
    ->name('material-siaga.update-status');

Route::resource('material-siaga', MaterialSiagaStandByController::class)->parameters([
    'material-siaga' => 'id'
]);