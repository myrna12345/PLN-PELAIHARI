<?php

namespace App\Http\Controllers;

use App\Models\BarangStandBy;
use Illuminate\Http\Request;

class BarangStandByController extends Controller
{
    /**
     * READ: Menampilkan halaman daftar (laporan) Barang Stand By.
     */
    public function index()
    {
        $items = BarangStandBy::latest()->paginate(10); // Ambil 10 data terbaru
        return view('barang_stand_by.index', compact('items'));
    }

    /**
     * CREATE (Form): Menampilkan form untuk menambah data baru.
     */
    public function create()
    {
        return view('barang_stand_by.create');
    }

    /**
     * CREATE (Action): Menyimpan data baru dari form ke database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_barang' => 'required|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'status' => 'required|string',
        ]);

        // Simpan data ke database
        BarangStandBy::create($request->all());

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('barang-stand-by.index')
                         ->with('success', 'Data Barang Stand By berhasil ditambahkan.');
    }

    /**
     * UPDATE (Form): Menampilkan form untuk mengedit data.
     */
    public function edit(BarangStandBy $barangStandBy)
    {
        return view('barang_stand_by.edit', ['item' => $barangStandBy]);
    }

    /**
     * UPDATE (Action): Memperbarui data di database.
     */
    public function update(Request $request, BarangStandBy $barangStandBy)
    {
        // Validasi input
        $request->validate([
            'kode_barang' => 'required|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'status' => 'required|string',
        ]);

        // Update data di database
        $barangStandBy->update($request->all());

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('barang-stand-by.index')
                         ->with('success', 'Data Barang Stand By berhasil diperbarui.');
    }

    /**
     * DELETE: Menghapus data dari database.
     */
    public function destroy(BarangStandBy $barangStandBy)
    {
        $barangStandBy->delete();

        return redirect()->route('barang-stand-by.index')
                         ->with('success', 'Data Barang Stand By berhasil dihapus.');
    }
}