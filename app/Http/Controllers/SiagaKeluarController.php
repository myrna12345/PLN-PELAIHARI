<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// TODO: Jangan lupa import Model Anda nanti, contoh:
// use App\Models\SiagaKeluar;
// use App\Models\MaterialStandBy;

class SiagaKeluarController extends Controller
{
    /**
     * Menampilkan halaman tabel (index)
     * Ini untuk halaman "Lihat Siaga Keluar" (Gambar 3)
     */
    public function index()
    {
        // TODO: Ganti ini dengan logika untuk mengambil data dari database
        // $dataSiagaKeluar = SiagaKeluar::all(); 
        $dataSiagaKeluar = []; // Contoh data kosong

        return view('siaga-keluar.index', compact('dataSiagaKeluar'));
    }

    /**
     * Menampilkan halaman form tambah data
     * Ini untuk halaman "Tambah Siaga Keluar" (Gambar 2)
     */
    public function create()
    {
        // TODO: Ambil data material untuk dropdown
        // $materials = MaterialStandBy::all();
        $materials = []; // Contoh data kosong

        return view('siaga-keluar.create', compact('materials'));
    }

    /**
     * Menyimpan data baru dari form 'create'
     */
    public function store(Request $request)
    {
        // TODO: Tambahkan validasi data di sini
        $request->validate([
            'nama_material' => 'required',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_keluar' => 'required|numeric',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Opsional
        ]);

        // TODO: Tulis logika untuk menyimpan data ke database
        // ... (Logika simpan data) ...
        // ... (Logika upload foto jika ada) ...

        // Arahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data.
     */
    public function edit($id)
    {
        // TODO: Cari data berdasarkan $id
        // $siagaKeluar = SiagaKeluar::findOrFail($id);
        // $materials = MaterialStandBy::all();
        
        // return view('siaga-keluar.edit', compact('siagaKeluar', 'materials'));
        
        // Untuk sekarang, kita arahkan ke index
        return redirect()->route('siaga-keluar.index');
    }

    /**
     * Update data di database.
     */
    public function update(Request $request, $id)
    {
        // TODO: Tambahkan logika validasi dan update data
        
        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil diperbarui.');
    }

    /**
     * Hapus data dari database.
     */
    public function destroy($id)
    {
        // TODO: Tambahkan logika untuk hapus data
        // $siagaKeluar = SiagaKeluar::findOrFail($id);
        // $siagaKeluar->delete();
        
        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil dihapus.');
    }
}