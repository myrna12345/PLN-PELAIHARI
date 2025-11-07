<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangStandByController;
use App\Http\Controllers\MaterialStandByController;
use App\Http\Controllers\MaterialKeluarController;

Route::get('/', function () {
    return view('welcome');
});

// Arahkan URL /dashboard ke fungsi index di DashboardController
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('material-stand-by', MaterialStandByController::class);

// ROUTES UNTUK MATERIAL KELUAR
Route::get('/material_keluar', [MaterialKeluarController::class, 'index'])->name('material_keluar.index');      // Tampilkan tabel
Route::get('/material_keluar/create', [MaterialKeluarController::class, 'create'])->name('material_keluar.create'); // Form tambah
Route::post('/material_keluar/store', [MaterialKeluarController::class, 'store'])->name('material_keluar.store');   // Simpan data
Route::get('/material_keluar/{id}/edit', [MaterialKeluarController::class, 'edit'])->name('material_keluar.edit');  // Form edit
Route::put('/material_keluar/{id}', [MaterialKeluarController::class, 'update'])->name('material_keluar.update');   // Update data
Route::delete('/material_keluar/{id}', [MaterialKeluarController::class, 'destroy'])->name('material_keluar.destroy'); // Hapus data