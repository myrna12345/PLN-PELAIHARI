<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// GANTI MODEL LAMA DENGAN YANG BARU
use App\Models\MaterialStandBy;
use App\Models\MaterialRetur;
use App\Models\MaterialKeluar;
use App\Models\MaterialKembali;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data summary.
     */
    public function index()
    {
        // DATA INI MASIH PERLU DISESUAIKAN
        // Pastikan Anda sudah membuat model untuk 'MaterialRetur', 'MaterialKeluar', 'MaterialKembali'
        
        // 1. Saldo Material Stand By
        $totalStandBy = MaterialStandBy::sum('jumlah');

        // 2. Material Keluar Hari Ini
        // Ganti 'MonitoringPemasangan' dengan model Anda yang benar (misal: 'MaterialKeluar')
        // $materialKeluarHariIni = MaterialKeluar::whereDate('tanggal', Carbon::today())->count(); 
        $materialKeluarHariIni = 10; // Placeholder

        // 3. Material Retur (dipisah)
        // $returAndal = MaterialRetur::where('status', 'baik')->sum('jumlah');
        // $returRusak = MaterialRetur::where('status', 'rusak')->sum('jumlah');
        $returAndal = 0; // Placeholder
        $returRusak = 0; // Placeholder

        // 4. Material Kembali
        // $totalMaterialKembali = MaterialKembali::sum('jumlah');
        $totalMaterialKembali = 150; // Placeholder

        // Kirim semua data ke view
        return view('dashboard', [
            'totalStandBy'           => $totalStandBy,
            'materialKeluarHariIni'  => $materialKeluarHariIni,
            'returAndal'             => $returAndal,
            'returRusak'             => $returRusak,
            'totalMaterialKembali'   => $totalMaterialKembali
        ]);
    }
}