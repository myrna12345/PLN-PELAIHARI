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
        // --- DATA TERBARU ---
        $recentStandby = MaterialStandBy::join('materials', 'material_stand_by.material_id', '=', 'materials.id')
                            ->select('material_stand_by.*', 'materials.nama_material')
                            ->latest('material_stand_by.created_at')->first();

        $recentSiaga = MaterialSiagaStandBy::latest()->first();

        // --- GRUP 1: MATERIAL GUDANG (GUDANG KECIL) ---
        $totalMeter = MaterialStandBy::whereIn('satuan', ['meter', 'm', 'METER', 'M'])->sum('jumlah');
        $totalBuah = MaterialStandBy::whereIn('satuan', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah');
        $totalStandBy = $totalMeter + $totalBuah;
        
        $detailsStandby = MaterialStandBy::join('materials', 'material_stand_by.material_id', '=', 'materials.id')
                            ->select('materials.nama_material', 'material_stand_by.satuan', DB::raw('SUM(material_stand_by.jumlah) as total'))
                            ->groupBy('materials.nama_material', 'material_stand_by.satuan')->get();

        $keluarMeter = MaterialKeluar::whereDate('created_at', Carbon::today())->whereIn('satuan_material', ['meter', 'm', 'METER', 'M'])->sum('jumlah_material');
        $keluarBuah = MaterialKeluar::whereDate('created_at', Carbon::today())->whereIn('satuan_material', ['buah', 'bh', 'BUAH', 'BH'])->sum('jumlah_material');
        $volumeKeluar = $keluarMeter + $keluarBuah;

        $detailsKeluar = MaterialKeluar::join('materials', 'material_keluar.material_id', '=', 'materials.id')
                            ->whereDate('material_keluar.created_at', Carbon::today())
                            ->select('materials.nama_material', 'material_keluar.satuan_material', DB::raw('SUM(material_keluar.jumlah_material) as total'))
                            ->groupBy('materials.nama_material', 'material_keluar.satuan_material')->get();

        $volumeKembali = MaterialKembali::sum('jumlah_material');
        $detailsKembali = MaterialKembali::join('materials', 'material_kembali.material_id', '=', 'materials.id')
                            ->select('materials.nama_material', 'material_kembali.satuan', DB::raw('SUM(material_kembali.jumlah_material) as total'))
                            ->groupBy('materials.nama_material', 'material_kembali.satuan')->get();

        $returAndal = MaterialRetur::whereIn('status', ['baik', 'bekas_andal'])->sum('jumlah');
        $returRusak = MaterialRetur::where('status', 'rusak')->sum('jumlah');
        $totalRetur = $returAndal + $returRusak;
        $detailsRetur = MaterialRetur::join('materials', 'material_retur.material_id', '=', 'materials.id')
                            ->select('materials.nama_material', 'material_retur.satuan', DB::raw('SUM(material_retur.jumlah) as total'))
                            ->groupBy('materials.nama_material', 'material_retur.satuan')->get();

        // --- GRUP 2: MONITORING SIAGA ---
        $listSiagaReady = MaterialSiagaStandBy::where('status', 'Ready')->get();
        $listSiagaKeluar = SiagaKeluar::all();
        $listSiagaKembali = SiagaKembali::all();
        
        // Statistik Ready
        $siaga1P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%1P%')->count();
        $siaga3P = MaterialSiagaStandBy::where('status', 'Ready')->where('nama_material', 'LIKE', '%3P%')->count();

        // Statistik Keluar (Berdasarkan nama_material_lengkap di tabel siaga_keluars)
        $siagaKeluar1P = SiagaKeluar::where('nama_material_lengkap', 'LIKE', '%1P%')->count();
        $siagaKeluar3P = SiagaKeluar::where('nama_material_lengkap', 'LIKE', '%3P%')->count();

        // Statistik Kembali (Berdasarkan nama_material_lengkap di tabel siaga_kembalis)
        $siagaKembali1P = SiagaKembali::where('nama_material_lengkap', 'LIKE', '%1P%')->count();
        $siagaKembali3P = SiagaKembali::where('nama_material_lengkap', 'LIKE', '%3P%')->count();

        return view('dashboard', compact(
            'totalStandBy', 'volumeKeluar', 'keluarMeter', 'keluarBuah', 'volumeKembali', 'totalRetur',
            'returAndal', 'returRusak', 'totalMeter', 'totalBuah', 'recentStandby', 'recentSiaga',
            'detailsStandby', 'detailsKeluar', 'detailsKembali', 'detailsRetur',
            'listSiagaReady', 'listSiagaKeluar', 'listSiagaKembali', 
            'siaga1P', 'siaga3P', 'siagaKeluar1P', 'siagaKeluar3P', 'siagaKembali1P', 'siagaKembali3P'
        ));
    }
}