<?php

namespace App\Http\Controllers;

// WAJIB: Pastikan baris ini ada agar Request terbaca sebagai class Laravel
use Illuminate\Http\Request; 
use App\Models\MaterialHistory;
use Carbon\Carbon;

class MaterialHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggal_mulai = $request->query('tanggal_mulai');
        $tanggal_akhir = $request->query('tanggal_akhir');

        $query = MaterialHistory::query();

        if ($search) {
            $query->where('nama_material', 'LIKE', "%{$search}%");
        }

        // Pastikan filter menggunakan kolom 'tanggal_input'
        if ($tanggal_mulai && $tanggal_akhir) {
            $query->whereBetween('tanggal_input', [
                Carbon::parse($tanggal_mulai)->startOfDay(),
                Carbon::parse($tanggal_akhir)->endOfDay()
            ]);
        }

        $histories = $query->latest('tanggal_input')->paginate(10);

        return view('material-history.index', compact('histories'));
    }
}