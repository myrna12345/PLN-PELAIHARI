<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialStandByController; 
use App\Http\Controllers\MaterialSiagaStandByController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('material-stand-by/download-pdf', [MaterialStandByController::class, 'downloadPDF'])
     ->name('material-stand-by.download-pdf');
     
Route::get('material-stand-by/{materialStandBy}/download-foto', [MaterialStandByController::class, 'downloadFoto'])
     ->name('material-stand-by.download-foto');
     
// Route resource untuk CRUD
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