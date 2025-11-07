<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRetur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialReturExport; // <-- 1. Impor class Export
use Maatwebsite\Excel\Facades\Excel; // <-- 2. Impor class Excel
use Carbon\Carbon; // <-- 3. Impor Carbon

class MaterialReturController extends Controller
{
    /**
     * READ: Menampilkan halaman daftar (laporan) dengan SEARCH.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialRetur::with('material');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', '%' . $search . '%')
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', '%' . $search . '%');
                  });
            });
        }
        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        $items = $query->latest('tanggal')->paginate(10); 
        return view('material_retur.index', compact('items'));
    }

    /**
     * CREATE (Form): Menampilkan form tambah data.
     */
    public function create()
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_retur.create', compact('materials'));
    }

    /**
     * CREATE (Action): Menyimpan data baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date', 
            'status' => 'required|in:baik,rusak',
            'keterangan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_retur', 'public');
        }

        MaterialRetur::create($validated + ['foto_path' => $path]);

        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil ditambahkan.');
    }

    /**
     * SHOW: Menampilkan detail data (Read-Only).
     */
    public function show(MaterialRetur $materialRetur)
    {
        return view('material_retur.show', [
            'item' => $materialRetur
        ]);
    }

    /**
     * UPDATE (Form): Menampilkan form untuk edit data.
     */
    public function edit(MaterialRetur $materialRetur)
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_retur.edit', [
            'item' => $materialRetur,
            'materials' => $materials
        ]);
    }

    /**
     * UPDATE (Action): Memperbarui data di database.
     */
    public function update(Request $request, MaterialRetur $materialRetur)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'status' => 'required|in:baik,rusak',
            'keterangan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        $path = $materialRetur->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_retur', 'public');
        }

        $materialRetur->update($validated + ['foto_path' => $path]);

        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil diperbarui.');
    }

    /**
     * DELETE: Menghapus data dari database.
     */
    public function destroy(MaterialRetur $materialRetur)
    {
        if ($materialRetur->foto_path) {
            Storage::disk('public')->delete($materialRetur->foto_path);
        }
        $materialRetur->delete();
        
        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil dihapus.');
    }

    /**
     * --- FUNGSI UNTUK DOWNLOAD FOTO ---
     */
    public function downloadFoto(MaterialRetur $materialRetur)
    {
        if ($materialRetur->foto_path && Storage::disk('public')->exists($materialRetur->foto_path)) {
            return Storage::disk('public')->download($materialRetur->foto_path);
        } else {
            return redirect()->route('material-retur.index')
                             ->with('error', 'File foto tidak ditemukan.');
        }
    }
    
    /**
     * --- FUNGSI BARU UNTUK DOWNLOAD PDF & EXCEL ---
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulaiString = $request->tanggal_mulai;
        $tanggalAkhirString = $request->tanggal_akhir;
        
        $tanggalMulai = Carbon::parse($tanggalMulaiString)->startOfDay(); 
        $tanggalAkhir = Carbon::parse($tanggalAkhirString)->endOfDay();   
        
        $filename = 'laporan_material_retur_' . $tanggalMulaiString . '_sd_' . $tanggalAkhirString;

        if ($request->has('submit_pdf')) {
            
            $items = MaterialRetur::with('material')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                        ->orderBy('tanggal', 'asc')
                        ->get();
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulaiString,
                'tanggal_akhir' => $tanggalAkhirString,
            ];
            $pdf = PDF::loadView('material_retur.laporan_pdf', $data); // Arahkan ke view PDF Retur
            return $pdf->download($filename . '.pdf');

        } 
        
        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialReturExport($tanggalMulai->toDateTimeString(), $tanggalAkhir->toDateTimeString()), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}