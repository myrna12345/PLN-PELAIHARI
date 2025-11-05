<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangStandByController;

Route::get('/', function () {
    return view('welcome');
});

// Arahkan URL /dashboard ke fungsi index di DashboardController
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('barang-stand-by', BarangStandByController::class);