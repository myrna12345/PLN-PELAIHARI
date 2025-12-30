<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MaterialStandByController extends Controller
{
    /**
     * Menampilkan daftar Material Stand By dengan fitur pencarian dan filter tanggal.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        // Mengambil data dengan relasi material
        $query = MaterialStandBy::with('material');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->orWhereHas('material', function($subQ) use ($search) {
                    $subQ->where('nama_material', 'like', '%' . $search . '%');
                });
                $q->orWhere('satuan', 'like', '%' . $search . '%');
            });
        }

        if ($tanggalMulai) { 
            $query->whereDate('tanggal', '>=', $tanggalMulai); 
        }

        if ($tanggalAkhir) { 
            $query->whereDate('tanggal', '<=', $tanggalAkhir); 
        }

        // Urutkan berdasarkan tanggal terbaru
        $items = $query->latest('tanggal')->paginate(10); 

        return view('material_stand_by.index', compact('items'));
    }

    /**
     * Menampilkan form tambah data.
     */
    public function create()
    {
        // Mengambil material yang bukan kategori siaga untuk dropdown
        $materials = Material::where('kategori', '!=', 'siaga')
                            ->orWhereNull('kategori')
                            ->get()
                            ->sortBy('nama_material', SORT_NATURAL);
                            
        return view('material_stand_by.create', compact('materials'));
    }

    /**
     * Menyimpan data baru tanpa kolom keterangan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string|in:Buah,Meter',
            'foto'         => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' 
        ]);

        // Proses simpan file ke storage
        $pathMaterial = $request->file('foto')->store('fotos_material_standby', 'public');
        $pathPetugas = $request->file('foto_petugas')->store('foto_petugas', 'public');

        MaterialStandBy::create([
            'material_id'  => $validated['material_id'],
            'jumlah'       => $validated['jumlah'],
            'satuan'       => $validated['satuan'],
            'foto_path'    => $pathMaterial,
            'foto_petugas' => $pathPetugas,
            'tanggal'      => Carbon::now('Asia/Makassar'), // Waktu otomatis WITA
        ]);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit data.
     */
    public function edit($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        $materials = Material::where('kategori', '!=', 'siaga')
                            ->orWhereNull('kategori')
                            ->get()
                            ->sortBy('nama_material', SORT_NATURAL);

        return view('material_stand_by.edit', compact('item', 'materials'));
    }

    /**
     * Memperbarui data (Keterangan telah dihapus).
     */
    public function update(Request $request, $id)
    {
        $materialStandBy = MaterialStandBy::findOrFail($id);

        $validated = $request->validate([
            'material_id'  => 'required|exists:materials,id',
            'jumlah'       => 'required|integer|min:1',
            'satuan'       => 'required|string|in:Buah,Meter', 
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $data = [
            'material_id' => $validated['material_id'],
            'jumlah'      => $validated['jumlah'],
            'satuan'      => $validated['satuan'],
        ];
        
        // Update foto material jika ada file baru
        if ($request->hasFile('foto')) {
            if ($materialStandBy->foto_path) {
                Storage::disk('public')->delete($materialStandBy->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('fotos_material_standby', 'public');
        }

        // Update foto petugas jika ada file baru
        if ($request->hasFile('foto_petugas')) {
            if ($materialStandBy->foto_petugas) {
                Storage::disk('public')->delete($materialStandBy->foto_petugas);
            }
            $data['foto_petugas'] = $request->file('foto_petugas')->store('foto_petugas', 'public');
        }

        $materialStandBy->update($data);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }

    /**
     * Menghapus data beserta file fisiknya.
     */
    public function destroy($id)
    {
        $materialStandBy = MaterialStandBy::findOrFail($id);
        
        if ($materialStandBy->foto_path) {
            Storage::disk('public')->delete($materialStandBy->foto_path);
        }
        if ($materialStandBy->foto_petugas) {
            Storage::disk('public')->delete($materialStandBy->foto_petugas);
        }
        
        $materialStandBy->delete();
        
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil dihapus.');
    }

    /**
     * Menampilkan Foto Material.
     */
    public function showFoto($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        return Storage::disk('public')->response($item->foto_path);
    }

    /**
     * Mengunduh Foto Material.
     */
    public function downloadFoto($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        return Storage::disk('public')->download($item->foto_path);
    }

    /**
     * Mengunduh Foto Petugas.
     */
    public function downloadFotoPetugas($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        if ($item->foto_petugas && Storage::disk('public')->exists($item->foto_petugas)) {
            return Storage::disk('public')->download($item->foto_petugas);
        }
        return redirect()->back()->with('error', 'File foto petugas tidak ditemukan.');
    }

    /**
     * Generate Laporan PDF & Excel.
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = MaterialStandBy::with('material')
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                    ->orderBy('tanggal', 'asc')
                    ->get();

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_stand_by.laporan_pdf', [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ]);
            
            return $pdf->download('laporan_material_stand_by.pdf');
        }
        
        if ($request->has('submit_excel')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\MaterialStandByExport($tanggalMulai, $tanggalAkhir), 
                'laporan_material_stand_by.xlsx'
            );
        }
    }
}