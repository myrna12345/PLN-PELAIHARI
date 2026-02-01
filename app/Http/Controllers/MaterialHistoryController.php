<?php

namespace App\Http\Controllers;

use App\Models\MaterialHistory;

class MaterialHistoryController extends Controller
{
    public function index()
    {
        $histories = MaterialHistory::orderBy('tanggal_input', 'desc')->get();
        return view('material-history.index', compact('histories'));
    }
    public function downloadHistory(Request $request)
{
    $request->validate([
        'tanggal_mulai' => 'nullable|date',
        'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
    ]);

    $query = MaterialHistory::orderBy('tanggal_input', 'asc');

    if ($request->tanggal_mulai && $request->tanggal_akhir) {
        $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $query->whereBetween('tanggal_input', [$dateStart, $dateEnd]);
    }

    $items = $query->get();

    if ($items->isEmpty()) {
        return redirect()->back()->with('error', 'Tidak ada data untuk diunduh.');
    }

    if ($request->has('submit_pdf')) {
        $pdf = Pdf::loadView('material_history.pdf', compact('items'));
        return $pdf->download('Riwayat_Penambahan_Material.pdf');
    } 

    if ($request->has('submit_excel')) {
        // Pastikan Anda telah membuat class Export untuk History
        return Excel::download(new \App\Exports\MaterialHistoryExport($items), 'Riwayat_Penambahan_Material.xlsx');
    }

    return redirect()->back();
}
}
