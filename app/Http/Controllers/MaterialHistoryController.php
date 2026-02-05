<?php

namespace App\Http\Controllers;

use App\Models\MaterialHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialHistoryExport;

class MaterialHistoryController extends Controller
{
    // 1. Tampilan Utama
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialHistory::query();

        if ($search) {
            $query->where('nama_material', 'like', "%$search%");
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal_input', '>=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tanggal_input', '<=', $tanggalAkhir);
        }

        $histories = $query->orderBy('tanggal_input', 'desc')->paginate(10);

        return view('material-history.index', compact('histories'));
    }

    // 2. Export PDF - PERBAIKAN ERROR $query
    public function exportPDF(Request $request)
    {
        // BARIS KUNCI: Kamu harus mendefinisikan $query di sini
        $query = MaterialHistory::query(); 

        if ($request->filled('search')) {
            $query->where('nama_material', 'like', "%{$request->search}%");
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_input', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_input', '<=', $request->tanggal_akhir);
        }

        // Ambil data berdasarkan filter di atas
        $data = $query->orderBy('tanggal_input', 'desc')->get();
        
        // Load view pdf_view dan kirim variabel $data
        $pdf = Pdf::loadView('material-history.pdf_view', compact('data'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Riwayat_Material_' . now()->format('d-m-Y') . '.pdf');
    }

    // 3. Export Excel
    public function exportExcel(Request $request)
{
    // Pastikan mengirim $request agar filter pencarian & tanggal terbawa ke Excel
    return Excel::download(new MaterialHistoryExport($request), 'Riwayat_Material_Standby.xlsx');
}
}