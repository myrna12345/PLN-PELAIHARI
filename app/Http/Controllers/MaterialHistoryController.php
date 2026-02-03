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
        // Ambil data dari input form (GET)
        $search = $request->query('search');
        $tanggal_mulai = $request->query('tanggal_mulai');
        $tanggal_akhir = $request->query('tanggal_akhir');

        $query = MaterialHistory::query();

        // Filter: Nama Material
        if ($search) {
            $query->where('nama_material', 'LIKE', "%{$search}%");
        }

        // Filter: Tanggal (Jika pilih hari ini di kedua kolom, maka hanya muncul hari ini)
        if ($tanggal_mulai && $tanggal_akhir) {
            $query->whereBetween('tanggal_input', [
                Carbon::parse($tanggal_mulai)->startOfDay(),
                Carbon::parse($tanggal_akhir)->endOfDay()
            ]);
        } elseif ($tanggal_mulai) {
            $query->whereDate('tanggal_input', '>=', $tanggal_mulai);
        } elseif ($tanggal_akhir) {
            $query->whereDate('tanggal_input', '<=', $tanggal_akhir);
        }

        // Ambil data dengan pagination agar link halaman muncul
        $histories = $query->latest('tanggal_input')->paginate(10);

        return view('material-history.index', compact('histories'));
    }
}