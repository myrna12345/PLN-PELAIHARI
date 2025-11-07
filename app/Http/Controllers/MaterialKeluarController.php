<?php

namespace App\Http\Controllers;

use App\Models\MaterialKeluar;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialKeluarController extends Controller
{
    // 🧭 Tampilkan semua data material keluar
    public function index()
    {
        $materialKeluar = MaterialKeluar::all();
        return view('material_keluar.index', compact('materialKeluar'));
    }

    // 🧱 Form tambah data material keluar
    public function create()
    {
        $materialList = Material::all(); // untuk dropdown nama material
        return view('material_keluar.create', compact('materialList'));
    }

    // 💾 Simpan data baru ke database
    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Makassar'); // atau 'Asia/Jakarta' tergantung lokasi

        $validated['tanggal'] = now(); // otomatis waktu saat ini

        // Validasi input
        $validated = $request->validate([
            'nama_material' => 'required',
            'nama_petugas' => 'required',
            'jumlah_material' => 'required|numeric',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        // Format tanggal dari HTML ke format database
            $validated['tanggal'] = date('Y-m-d H:i:s', strtotime($request->tanggal));
            
        // Simpan foto jika ada
        if ($request->hasFile('foto')) {
        $fotoPath = $request->file('foto')->store('material_keluar', 'public');
        $validated['foto'] = $fotoPath;
    }


        // Simpan ke database
        MaterialKeluar::create($validated);

        // Tambahkan pesan sukses
        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan!');
    }

    // ✏️ Tampilkan form edit data
    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        $materialList = Material::all();
        return view('material_keluar.edit', compact('data', 'materialList'));
    }

    // 🔄 Update data
    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);

        $validated = $request->validate([
            'nama_material' => 'required',
            'nama_petugas' => 'required',
            'jumlah_material' => 'required|numeric',
            'tanggal' => 'required',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Ganti foto lama jika ada foto baru
        if ($request->hasFile('foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui!');
    }

    // 🗑️ Hapus data
    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);

        // Hapus foto dari storage jika ada
        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        $data->delete();

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus!');
    }
}
