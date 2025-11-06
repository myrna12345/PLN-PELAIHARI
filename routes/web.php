<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialStandByController; 

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