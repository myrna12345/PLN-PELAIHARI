<?php

namespace App\Http\Controllers;

use App\Models\MaterialStandBy;
use App\Models\MaterialRetur;
use App\Models\MaterialKeluar;
use App\Models\MaterialKembali;
use App\Models\MaterialSiagaStandBy;
use App\Models\SiagaKeluar;
use App\Models\SiagaKembali;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // --- AMBIL HANYA 1 DATA TERBARU ---
        $recentStandby = MaterialStandBy::latest()->first();
        $recentSiaga = MaterialSiagaStandBy::latest()->first();

        // --- GRUP 1: MATERIAL GUDANG (STANDBY) ---
        $totalStandBy = MaterialStandBy::sum('jumlah');
        $totalMeter = MaterialStandBy::whereIn('satuan', ['meter', 'm', 'METER', 'M'])->sum('jumlah');
        $totalBuah = MaterialStandBy::whereIn('satuan', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah');
        
        // --- MATERIAL KELUAR ---
        $keluarMeter = MaterialKeluar::whereDate('created_at', Carbon::today())
            ->whereIn('satuan_material', ['meter', 'm', 'METER', 'M'])
            ->sum('jumlah_material');
        $keluarBuah = MaterialKeluar::whereDate('created_at', Carbon::today())
            ->whereIn('satuan_material', ['buah', 'bh', 'BUAH', 'BH'])
            ->sum('jumlah_material');
        $volumeKeluar = $keluarMeter + $keluarBuah;

        // --- MATERIAL KEMBALI (DIPERBAIKI: Menggunakan kolom 'satuan') ---
        $volumeKembali = MaterialKembali::sum('jumlah_material');
        $kembaliMeter = MaterialKembali::whereIn('satuan', ['meter', 'm', 'METER', 'M'])->sum('jumlah_material');
        $kembaliBuah = MaterialKembali::whereIn('satuan', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah_material');
        
        // --- RETUR ---
        $returAndal = MaterialRetur::where('status', 'baik')->sum('jumlah');
        $returRusak = MaterialRetur::where('status', 'rusak')->sum('jumlah');
        $totalRetur = $returAndal + $returRusak;

        // --- GRUP 2: MATERIAL SIAGA ---
        $siagaReady = MaterialSiagaStandBy::where('status', 'Ready')->count();
        $siagaKeluar = SiagaKeluar::count();
        $siagaKembali = SiagaKembali::count();
        $siaga1P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%1P%')->count();
        $siaga3P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%3P%')->count();
        
        $siagaKeluar1P = SiagaKeluar::whereHas('standbyDetail', function($q) { $q->where('nama_material', 'LIKE', '%1P%'); })->count();
        $siagaKeluar3P = SiagaKeluar::whereHas('standbyDetail', function($q) { $q->where('nama_material', 'LIKE', '%3P%'); })->count();
        
        $siagaKembali1P = SiagaKembali::whereHas('standbyDetail', function($q) { $q->where('nama_material', 'LIKE', '%1P%'); })->count();
        $siagaKembali3P = SiagaKembali::whereHas('standbyDetail', function($q) { $q->where('nama_material', 'LIKE', '%3P%'); })->count();

        return view('dashboard', compact(
            'totalStandBy', 'volumeKeluar', 'keluarMeter', 'keluarBuah', 
            'volumeKembali', 'kembaliMeter', 'kembaliBuah', 'totalRetur',
            'returAndal', 'returRusak', 'totalMeter', 'totalBuah',
            'siagaReady', 'siagaKeluar', 'siagaKembali', 'siaga1P', 'siaga3P',
            'siagaKeluar1P', 'siagaKeluar3P', 'siagaKembali1P', 'siagaKembali3P',
            'recentStandby', 'recentSiaga'
        ));
    }
}