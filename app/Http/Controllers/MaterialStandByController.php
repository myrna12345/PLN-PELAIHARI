<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
// Kita tidak perlu 'use Carbon' lagi di sini

class MaterialStandByController extends Controller
{
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

    /**
     * ==============================================
     * === FUNGSI 'store' DIPERBAIKI (DISEDERHANAKAN) ===
     * ==============================================
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date', // 'tanggal' sekarang adalah string UTC dari form
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos', 'public');
        }

        // 2. Simpan data langsung. 
        //    'tanggal' sudah dalam format UTC yang benar dari JavaScript.
        MaterialStandBy::create([
            'material_id' => $validated['material_id'],
            'nama_petugas' => $validated['nama_petugas'],
            'jumlah' => $validated['jumlah'],
            'tanggal' => $validated['tanggal'], // <-- LANGSUNG SIMPAN
            'foto_path' => $path
        ]);

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

    /**
     * ==============================================
     * === FUNGSI 'update' DIPERBAIKI (DISEDERHANAKAN) ===
     * ==============================================
     */
    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date', // 'tanggal' sekarang adalah string UTC
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $materialStandBy->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos', 'public');
        }

        $materialStandBy->update([
            'material_id' => $validated['material_id'],
            'nama_petugas' => $validated['nama_petugas'],
            'jumlah' => $validated['jumlah'],
            'tanggal' => $validated['tanggal'], // <-- LANGSUNG SIMPAN
            'foto_path' => $path
        ]);

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
    
    public function downloadPDF(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;

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
        $filename = 'laporan_material_stand_by_' . $tanggalMulai . '_sd_' . $tanggalAkhir . '.pdf';
        return $pdf->download($filename);
    }
}