<?php

namespace App\Http\Controllers;

use App\Models\Material; // Model untuk dropdown
use App\Models\MaterialStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Untuk mengelola file

class MaterialStandByController extends Controller
{
    /**
     * READ: Menampilkan halaman daftar (laporan).
     */
    public function index()
    {
        // 'with('material')' mengambil data relasi agar lebih efisien
        $items = MaterialStandBy::with('material')->latest()->paginate(10); 
        return view('material_stand_by.index', compact('items'));
    }

    /**
     * CREATE (Form): Menampilkan form tambah data.
     */
    public function create()
    {
        // Ambil semua data material untuk mengisi <select> dropdown
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.create', compact('materials'));
    }

    /**
     * CREATE (Action): Menyimpan data baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi foto
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            // 2. Simpan foto ke folder public/storage/fotos
            // PENTING: Nanti jalankan "php artisan storage:link"
            $path = $request->file('foto')->store('fotos', 'public');
        }

        // 3. Simpan data ke database
        MaterialStandBy::create([
            'material_id' => $request->material_id,
            'nama_petugas' => $request->nama_petugas,
            'jumlah' => $request->jumlah,
            'tanggal' => $request->tanggal,
            'foto_path' => $path
        ]);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    /**
     * UPDATE (Form): Menampilkan form untuk edit data.
     */
    public function edit(MaterialStandBy $materialStandBy)
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.edit', [
            'item' => $materialStandBy,
            'materials' => $materials
        ]);
    }

    /**
     * UPDATE (Action): Memperbarui data di database.
     */
    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        $request->validate([
        'material_id' => 'required|exists:materials,id',
        'nama_petugas' => 'required|string|max:255',
        'jumlah' => 'required|integer|min:1',
        'tanggal' => 'required|date', // 'date' sudah bisa menangani format datetime-local
        'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

        $path = $materialStandBy->foto_path;
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            // Simpan foto baru
            $path = $request->file('foto')->store('fotos', 'public');
        }

        $$request->validate([
        'material_id' => 'required|exists:materials,id',
        'nama_petugas' => 'required|string|max:255',
        'jumlah' => 'required|integer|min:1',
        'tanggal' => 'required|date',
        'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }

    /**
     * DELETE: Menghapus data dari database.
     */
    public function destroy(MaterialStandBy $materialStandBy)
    {
        // Hapus foto dari storage
        if ($materialStandBy->foto_path) {
            Storage::disk('public')->delete($materialStandBy->foto_path);
        }
        
        // Hapus data dari database
        $materialStandBy->delete();

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil dihapus.');
    }
}