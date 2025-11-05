<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Impor semua model yang akan kita gunakan
use App\Models\BarangStandBy;
use App\Models\BarangRetur;
use App\Models\MonitoringPemasangan;
use App\Models\BarangSisa;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data summary.
     */
    public function index()
    {
        // Hitung total untuk setiap widget
        $totalStandBy      = BarangStandBy::sum('jumlah');
        $pemasanganHariIni = MonitoringPemasangan::whereDate('tanggal', Carbon::today())->count();
        $returRusak        = BarangRetur::where('status', 'rusak')->sum('jumlah');
        $returBaik         = BarangRetur::where('status', 'baik')->sum('jumlah');
        $totalBarangSisa   = BarangSisa::sum('jumlah');

        // Kirim semua data yang sudah dihitung ke view 'dashboard'
        return view('dashboard', [
            'totalStandBy'      => $totalStandBy,
            'pemasanganHariIni' => $pemasanganHariIni,
            'returRusak'        => $returRusak,
            'returBaik'         => $returBaik,
            'totalBarangSisa'   => $totalBarangSisa
        ]);
    }
}