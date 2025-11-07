<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

// Impor model yang sudah ada
use App\Models\MaterialStandBy;
use App\Models\MaterialRetur;
use App\Models\MaterialKeluar;
use App\Models\MaterialKembali;

/* CATATAN: Pastikan Anda sudah membuat model-model ini:
  use App\Models\MaterialSiagaStandBy;
  use App\Models\SiagaKeluar;
  use App\Models\SiagaKembali;
*/


class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data summary.
     */
    public function index()
    {
        // 1. Material Stand By
        $totalStandBy = MaterialStandBy::sum('jumlah');

        // 2. Material Keluar
        // Ganti 'MaterialKeluar' dengan model Anda yang benar jika namanya beda
        // $materialKeluarHariIni = MaterialKeluar::whereDate('created_at', Carbon::today())->count(); 
        $materialKeluarHariIni = 10; // Placeholder

        // 3. Material Retur
        // $returAndal = MaterialRetur::where('status', 'baik')->sum('jumlah');
        // $returRusak = MaterialRetur::where('status', 'rusak')->sum('jumlah');
        $returAndal = 0; // Placeholder
        $returRusak = 0; // Placeholder

        // 4. Material Kembali
        // $totalMaterialKembali = MaterialKembali::sum('jumlah');
        $totalMaterialKembali = 150; // Placeholder

        // 5. WIDGET BARU (Gunakan data placeholder 150 unit seperti di gambar)
        $totalSiagaStandBy = 150; // Placeholder
        $totalSiagaKeluar = 150;  // Placeholder
        $totalSiagaKembali = 150; // Placeholder

        // Kirim semua data ke view
        return view('dashboard', [
            'totalStandBy'           => $totalStandBy,
            'materialKeluarHariIni'  => $materialKeluarHariIni,
            'returAndal'             => $returAndal,
            'returRusak'             => $returRusak,
            'totalMaterialKembali'   => $totalMaterialKembali,
            'totalSiagaStandBy'      => $totalSiagaStandBy,
            'totalSiagaKeluar'       => $totalSiagaKeluar,
            'totalSiagaKembali'      => $totalSiagaKembali
        ]);
    }
}