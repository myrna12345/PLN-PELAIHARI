<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialStandByExport; // <-- 1. Impor class Export BARU
use Maatwebsite\Excel\Facades\Excel; // <-- 2. Impor class Excel BARU

class MaterialStandByController extends Controller
{
    // ... (fungsi index, create, store, edit, update, destroy Anda sudah ada di sini) ...
    
    public function index()
    {
        $items = MaterialStandBy::with('material')->latest()->paginate(10); 
        return view('material_stand_by.index', compact('items'));
    }

    public function create()
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos', 'public');
        }
        MaterialStandBy::create($validated + ['foto_path' => $path]);
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    public function edit(MaterialStandBy $materialStandBy)
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.edit', [
            'item' => $materialStandBy,
            'materials' => $materials
        ]);
    }

    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        $path = $materialStandBy->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos', 'public');
        }
        $materialStandBy->update($validated + ['foto_path' => $path]);
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }

    public function destroy(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path) {
            Storage::disk('public')->delete($materialStandBy->foto_path);
        }
        $materialStandBy->delete();
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil dihapus.');
    }

    public function downloadFoto(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path && Storage::disk('public')->exists($materialStandBy->foto_path)) {
            return Storage::disk('public')->download($materialStandBy->foto_path);
        } else {
            return redirect()->route('material-stand-by.index')
                             ->with('error', 'File foto tidak ditemukan.');
        }
    }
    
    /**
     * --- 3. FUNGSI BARU INI MENGGANTIKAN downloadPDF() ---
     * Menangani download PDF DAN Excel.
     */
    public function downloadReport(Request $request)
    {
        // Validasi input tanggal
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;
        $filename = 'laporan_material_stand_by_' . $tanggalMulai . '_sd_' . $tanggalAkhir;

        // Cek tombol mana yang diklik (dari nama tombol)
        if ($request->has('submit_pdf')) {
            
            // --- LOGIKA PDF ---
            $items = MaterialStandBy::with('material')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                        ->orderBy('tanggal', 'asc')
                        ->get();
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];
            $pdf = PDF::loadView('material_stand_by.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');

        } 
        
        if ($request->has('submit_excel')) {
            
            // --- LOGIKA EXCEL BARU ---
            return Excel::download(new MaterialStandByExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        
        }
        
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}