<?php

namespace App\Http\Controllers;

use App\Models\MaterialKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKeluarController extends Controller
{
    // 🔍 INDEX: Tampilkan data + fitur pencarian
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialKeluar::query();

        // Filter pencarian nama material / petugas
        if ($search) {
            $query->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
        }


        // Urutkan dari terbaru dan paginate
        $materialKeluar = $query->orderByDesc('tanggal')->paginate(10);

        return view('material_keluar.index', compact('materialKeluar'));
    }

    // ➕ CREATE
    public function create()
    {
        return view('material_keluar.create');
    }

    // 💾 STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'tanggal' => 'nullable|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Tambahkan waktu saat ini otomatis
        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        MaterialKeluar::create($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan!');
    }
     //  lihat show  
    public function lihat($id)
        {
            $item = MaterialKeluar::findOrFail($id);
            return view('material_keluar.lihat', compact('item'));
        }

    // ✏️ EDIT
    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        return view('material_keluar.edit', compact('data'));
    }

    // 🔁 UPDATE
    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui!');
    }

    // 🗑 DELETE
    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }
        $data->delete();

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus!');
    }
}