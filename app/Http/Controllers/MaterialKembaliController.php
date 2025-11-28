<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialKembali;
use App\Models\MaterialStandBy; // 🟢 IMPORT MODEL STOK
use App\Exports\MaterialKembaliExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKembaliController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = MaterialKembali::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
            });
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10);

        return view('material_kembali.index', compact('materialKembali'));
    }

    public function create()
    {
        // Filter agar hanya mengambil material yang BUKAN 'siaga'
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->orderBy('nama_material')
                                 ->get();
        return view('material_kembali.create', compact('materialList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        // 1. Tentukan ID Material dari Nama Material (Bridge Relasi)
        $materialSource = Material::where('nama_material', $validated['nama_material'])->first();
        $jumlahKembali = $validated['jumlah_material'];
        
        if ($materialSource) {
            $materialId = $materialSource->id;

            // 🟢 2. LOGIKA PENAMBAHAN STOK (INCREMENT) 🟢
            // Cari record Material Stand By yang relevan
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                // Tambahkan stok
                $materialStok->increment('jumlah', $jumlahKembali);
            } else {
                return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item ini belum tercatat.')->withInput();
            }
        }

        // 3. Simpan record Material Kembali
        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan! Stok Stand By bertambah.');
    }

    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function edit($id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->orderBy('nama_material')
                                 ->get(); 

        return view('material_kembali.edit', compact('materialKembali', 'materialList'));
    }

    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        // Simpan jumlah lama sebelum update
        $jumlahLama = $materialKembali->jumlah_material;

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $jumlahBaru = $validated['jumlah_material'];
        $materialSource = Material::where('nama_material', $validated['nama_material'])->first();
        
        // Tentukan selisih stok yang harus diubah (positif jika bertambah, negatif jika berkurang)
        $stokSelisih = $jumlahBaru - $jumlahLama;

        // 🟢 LOGIKA PENYESUAIAN STOK (UPDATE LOGIC) 🟢
        if ($materialSource && $stokSelisih !== 0) {
            $materialId = $materialSource->id;
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                if ($stokSelisih > 0) {
                    // Jika jumlah bertambah (Material Kembali lebih banyak), tambahkan stok
                    $materialStok->increment('jumlah', $stokSelisih);
                } else {
                    // Jika jumlah berkurang (Material Kembali ditarik), kurangi stok
                    $materialStok->decrement('jumlah', abs($stokSelisih));
                }
            }
        }
        
        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                Storage::disk('public')->delete($materialKembali->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui! Stok Stand By disesuaikan.');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        
        // 🟢 LOGIKA PENGEMBALIAN STOK SAAT DELETE 🟢
        $materialSource = Material::where('nama_material', $data->nama_material)->first();
        if ($materialSource) {
            $materialStok = MaterialStandBy::where('material_id', $materialSource->id)->first();
            if ($materialStok) {
                // Kurangi stok (undo increment)
                $materialStok->decrement('jumlah', $data->jumlah_material);
            }
        }
        // END LOGIKA PENGEMBALIAN STOK

        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        $data->delete();

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus! Stok Stand By dikurangi kembali.');
    }
    
    // ... (Fungsi showFoto dan downloadReport tetap sama)
    public function showFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);

        if (!$item->foto || !Storage::disk('public')->exists($item->foto)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        return Storage::disk('public')->response($item->foto);
    }

    // 🟢 KODE PERBAIKAN: FUNGSI DOWNLOAD FOTO YANG HILANG 🟢
    public function downloadFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);

        if ($item->foto && Storage::disk('public')->exists($item->foto)) {
            return Storage::disk('public')->download($item->foto);
        }
        
        // Pastikan Anda menangani kasus ID yang tidak valid atau foto yang tidak ada.
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_kembali_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKembali::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();

        // PDF
        if ($request->has('submit_pdf')) {
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];

            $pdf = Pdf::loadView('material_kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        // Excel
        if ($request->has('submit_excel')) {
            return Excel::download(new \App\Exports\MaterialKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}