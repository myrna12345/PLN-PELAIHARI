<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy; 
use App\Models\MaterialHistory; // PERBAIKAN: Import Model yang benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\MaterialStandByExport;

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

    // --- 1. INDEX ---
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = MaterialStandBy::with('material'); 
        
        if ($search) {
            $query->whereHas('material', function($q) use ($search) {
                $q->where('nama_material', 'like', "%$search%");
            });
        }
        
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10);
        
        return view('material_stand_by.index', compact('items'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        $materials = Material::all()->sortBy('nama_material', SORT_NATURAL);
        return view('material_stand_by.create', compact('materials'));
    }

    // --- 3. STORE (FIX ERROR MATERIAL_ID) ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string', 
            'foto'        => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $destinationPath = public_path($this->uploadFolder);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Upload Foto Baru
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($destinationPath, $fotoName);

        $material = Material::findOrFail($validated['material_id']);

        // A. UPDATE/CREATE STOK (Tabel Utama)
        $existingItem = MaterialStandBy::where('material_id', $validated['material_id'])
                                       ->where('satuan', $validated['satuan'])
                                       ->first();

        if ($existingItem) {
            $oldPath = public_path($this->uploadFolder . '/' . $existingItem->foto_path);
            if ($existingItem->foto_path && File::exists($oldPath)) { File::delete($oldPath); }

            $existingItem->jumlah += $validated['jumlah']; // Logika Penjumlahan (100 + 50 = 150)
            $existingItem->foto_path = $fotoName;
            $existingItem->tanggal = Carbon::now('Asia/Makassar'); 
            $existingItem->save();
        } else {
            MaterialStandBy::create([
                'material_id'           => $validated['material_id'],
                'nama_material_lengkap' => $material->nama_material,
                'satuan'                => $validated['satuan'], 
                'jumlah'                => $validated['jumlah'],
                'foto_path'             => $fotoName,
                'tanggal'               => Carbon::now('Asia/Makassar'),
            ]);
        }

        // B. CATAT RIWAYAT: Sekarang menyertakan material_id agar tidak error 1364
        MaterialHistory::create([
            'material_id'   => $validated['material_id'], // Diambil dari migrasi Anda
            'nama_material' => $material->nama_material,
            'jumlah'        => $validated['jumlah'], 
            'satuan'        => $validated['satuan'],
            'foto_path'     => $fotoName,
            'tanggal_input' => Carbon::now('Asia/Makassar'),
        ]);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data berhasil diperbarui dan dicatat di riwayat!');
    }

    // --- SISA FUNGSI TETAP SAMA ---
    public function edit(MaterialStandBy $materialStandBy) { /* ... */ }
    public function update(Request $request, MaterialStandBy $materialStandBy) { /* ... */ }
    public function destroy(MaterialStandBy $materialStandBy) { /* ... */ }
    public function downloadFoto($id) { /* ... */ }
    public function downloadReport(Request $request) { /* ... */ }
}