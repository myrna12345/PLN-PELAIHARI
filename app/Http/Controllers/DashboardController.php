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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Menggunakan WITA sesuai lokasi ULP Pelaihari
        $today = Carbon::today('Asia/Makassar');

        // ============================================================
        // 📦 GRUP 1: MATERIAL GUDANG
        // ============================================================
        
        // 1. STOK STANDBY (TETAP MUNCUL SETIAP HARI)
        $totalMeter = MaterialStandBy::whereIn('satuan', ['meter', 'm', 'METER', 'M'])->sum('jumlah');
        $totalBuah = MaterialStandBy::whereIn('satuan', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah');
        $totalStandBy = $totalMeter + $totalBuah;
        
        $detailsStandby = MaterialStandBy::join('materials', 'material_stand_by.material_id', '=', 'materials.id')
                            ->select('materials.nama_material', 'material_stand_by.satuan', DB::raw('SUM(material_stand_by.jumlah) as total'))
                            ->groupBy('materials.nama_material', 'material_stand_by.satuan')->get();

        // 2. KELUAR (HANYA HARI INI - BESOK HILANG)
        $keluarMeter = MaterialKeluar::whereDate('created_at', $today)->whereIn('satuan_material', ['meter', 'm', 'METER', 'M'])->sum('jumlah_material');
        $keluarBuah = MaterialKeluar::whereDate('created_at', $today)->whereIn('satuan_material', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah_material');
        $volumeKeluar = $keluarMeter + $keluarBuah;

        $detailsKeluar = MaterialKeluar::join('materials', 'material_keluar.material_id', '=', 'materials.id')
                            ->whereDate('material_keluar.created_at', $today)
                            ->select('materials.nama_material', 'material_keluar.satuan_material', DB::raw('SUM(material_keluar.jumlah_material) as total'))
                            ->groupBy('materials.nama_material', 'material_keluar.satuan_material')->get();

        // 3. RINCIAN KEMBALI (HANYA HARI INI - BESOK HILANG)
        $volumeKembali = MaterialKembali::whereDate('created_at', $today)->sum('jumlah_material');
        $kembaliMeter = MaterialKembali::whereDate('created_at', $today)->whereIn('satuan', ['meter', 'm', 'METER', 'M'])->sum('jumlah_material');
        $kembaliBuah = MaterialKembali::whereDate('created_at', $today)->whereIn('satuan', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah_material');

        $detailsKembali = MaterialKembali::join('materials', 'material_kembali.material_id', '=', 'materials.id')
                            ->whereDate('material_kembali.created_at', $today)
                            ->select('materials.nama_material', 'material_kembali.satuan', DB::raw('SUM(material_kembali.jumlah_material) as total'))
                            ->groupBy('materials.nama_material', 'material_kembali.satuan')->get();

        // 4. RINCIAN RETUR (HANYA HARI INI - BESOK HILANG)
        $returAndal = MaterialRetur::whereDate('created_at', $today)->whereIn('status', ['baik', 'bekas_andal'])->sum('jumlah');
        $returRusak = MaterialRetur::whereDate('created_at', $today)->where('status', 'rusak')->sum('jumlah');
        $totalRetur = $returAndal + $returRusak;
        
        $detailsRetur = MaterialRetur::join('materials', 'material_retur.material_id', '=', 'materials.id')
                            ->whereDate('material_retur.created_at', $today)
                            ->select('materials.nama_material', 'material_retur.satuan', DB::raw('SUM(material_retur.jumlah) as total'))
                            ->groupBy('materials.nama_material', 'material_retur.satuan')->get();


        // ============================================================
        // ⚡ GRUP 2: MONITORING OPERASIONAL SIAGA
        // ============================================================

        // 1. SIAGA READY (TETAP MUNCUL SETIAP HARI)
        $listSiagaReady = MaterialSiagaStandBy::where('status', 'Ready')->get();

        // 2. SIAGA KELUAR (HANYA HARI INI - BESOK HILANG)
        $listSiagaKeluar = SiagaKeluar::whereDate('created_at', $today)->get();

        // 3. SIAGA KEMBALI (HANYA HARI INI - BESOK HILANG)
        $listSiagaKembali = SiagaKembali::whereDate('created_at', $today)->get();
        
        // Statistik Operasional
        $siaga1P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%1P%')->count();
        $siaga3P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%3P%')->count();

        $siagaKeluar1P = SiagaKeluar::whereDate('created_at', $today)->where('nama_material_lengkap', 'LIKE', '%1P%')->count();
        $siagaKeluar3P = SiagaKeluar::whereDate('created_at', $today)->where('nama_material_lengkap', 'LIKE', '%3P%')->count();

        $siagaKembali1P = SiagaKembali::whereDate('created_at', $today)->where('nama_material_lengkap', 'LIKE', '%1P%')->count();
        $siagaKembali3P = SiagaKembali::whereDate('created_at', $today)->where('nama_material_lengkap', 'LIKE', '%3P%')->count();

        return view('dashboard', compact(
            'totalStandBy', 'volumeKeluar', 'keluarMeter', 'keluarBuah', 'volumeKembali', 'totalRetur',
            'returAndal', 'returRusak', 'totalMeter', 'totalBuah', 'detailsStandby', 
            'detailsKeluar', 'detailsKembali', 'detailsRetur', 'listSiagaReady', 
            'listSiagaKeluar', 'listSiagaKembali', 'siaga1P', 'siaga3P', 
            'siagaKeluar1P', 'siagaKeluar3P', 'siagaKembali1P', 'siagaKembali3P',
            'kembaliMeter', 'kembaliBuah'
        ));
    }
}