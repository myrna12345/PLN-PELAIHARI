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
        // [SECURITY CHECK] Hanya Admin yang boleh akses
        if (strtolower(auth()->user()->role) !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak. Halaman ini hanya untuk Admin.');
        }

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

    // 2. Export PDF
    public function exportPDF(Request $request)
    {
        // [SECURITY CHECK]
        if (strtolower(auth()->user()->role) !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengunduh laporan ini.');
        }

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

        $data = $query->orderBy('tanggal_input', 'desc')->get();
        
        $pdf = Pdf::loadView('material-history.pdf_view', compact('data'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Riwayat_Material_' . now()->format('d-m-Y') . '.pdf');
    }

    // 3. Export Excel
    public function exportExcel(Request $request)
    {
        // [SECURITY CHECK]
        if (strtolower(auth()->user()->role) !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengunduh laporan ini.');
        }

        return Excel::download(new MaterialHistoryExport($request), 'Riwayat_Material_Standby.xlsx');
    }
}