<?php

namespace App\Http\Controllers;
use App\Models\Material;
use App\Models\MaterialKeluar;
use App\Models\MaterialStandBy; // 🟢 IMPORT MODEL STOK
use App\Exports\MaterialKeluarExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKeluarController extends Controller
{
    // INDEX: Tampilkan data + fitur pencarian
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

    // CREATE
    public function create()
    {
        // Ambil material yang kategorinya BUKAN 'siaga' (bisa 'teknik' atau null)
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->get();
                                     
        return view('material_keluar.create', compact('materialList'));
    }


    // 💾 STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        // 1. Tambahkan waktu saat ini otomatis
        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }
        
        // 2. TENTUKAN KUNCI MATERIAL ID DARI NAMA MATERIAL (Bridging Relasi)
        $materialSource = Material::where('nama_material', $validated['nama_material'])->first();

        // 🟢 3. LOGIKA PENGURANGAN STOK (INVENTORY LOGIC) 🟢
        if ($materialSource) {
            $materialId = $materialSource->id;
            $jumlahKeluar = $validated['jumlah_material'];
            
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                // Cek ketersediaan stok
                if ($materialStok->jumlah >= $jumlahKeluar) {
                    // Kurangi stok
                    $materialStok->decrement('jumlah', $jumlahKeluar);
                    
                    // 4. Buat record Material Keluar
                    MaterialKeluar::create($validated);

                    return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan dan stok Stand By berhasil dikurangi!');
                } else {
                    // Stok tidak cukup
                    return redirect()->back()->with('error', 'Gagal: Jumlah material keluar melebihi stok yang tersedia di Material Stand By (Stok tersedia: ' . $materialStok->jumlah . ')')->withInput();
                }
            } else {
                 // Tidak ada stok awal yang tercatat
                 return redirect()->back()->with('error', 'Gagal: Material ini tidak memiliki stok awal yang tercatat di Material Stand By.')->withInput();
            }
        }
        
        // Fallback jika nama material tidak ditemukan
        return redirect()->back()->with('error', 'Gagal: Nama Material tidak ditemukan dalam daftar Material utama.')->withInput();
    }
    
    // lihat show
    public function lihat($id)
    {
        $item = MaterialKeluar::findOrFail($id);
        return view('material_keluar.lihat', compact('item'));
    }

    // ✏️ EDIT
    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->get();

        return view('material_keluar.edit', compact('data', 'materialList'));
    }

    // 🔁 UPDATE
    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        // Simpan jumlah lama sebelum update
        $jumlahLama = $data->jumlah_material; 

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);
        
        $jumlahBaru = $validated['jumlah_material'];
        $materialSource = Material::where('nama_material', $validated['nama_material'])->first();
        
        // Tentukan selisih stok yang harus diubah
        $stokSelisih = $jumlahBaru - $jumlahLama;
        
        if ($materialSource && $stokSelisih !== 0) {
            $materialId = $materialSource->id;
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                if ($stokSelisih > 0) {
                    // Jika jumlah bertambah (pengeluaran lebih banyak), kurangi stok
                    if ($materialStok->jumlah >= $stokSelisih) {
                        $materialStok->decrement('jumlah', $stokSelisih);
                    } else {
                        return redirect()->back()->with('error', 'Gagal update: Penambahan pengeluaran melebihi stok yang tersedia (Tersedia: ' . $materialStok->jumlah . ')')->withInput();
                    }
                } else {
                    // Jika jumlah berkurang (pengeluaran ditarik), tambahkan stok
                    $materialStok->increment('jumlah', abs($stokSelisih));
                }
            } else {
                 return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By tidak ditemukan.')->withInput();
            }
        }
        
        if ($request->hasFile('foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui dan stok Stand By disesuaikan!');
    }

    // 🗑 DELETE
    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        // 🟢 LOGIKA PENGEMBALIAN STOK SAAT DELETE 🟢
        $materialSource = Material::where('nama_material', $data->nama_material)->first();
        if ($materialSource) {
            $materialStok = MaterialStandBy::where('material_id', $materialSource->id)->first();
            if ($materialStok) {
                // Tambahkan kembali jumlah yang dikeluarkan
                $materialStok->increment('jumlah', $data->jumlah_material);
            }
        }
        // END LOGIKA PENGEMBALIAN STOK

        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }
        $data->delete();

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus dan stok Stand By dikembalikan!');
    }
    
    // ... (Fungsi showFoto dan downloadReport tetap sama) ...
    public function showFoto($id)
    {
        $item = MaterialKeluar::findOrFail($id);
        
        if (!$item->foto || !Storage::disk('public')->exists($item->foto)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        return Storage::disk('public')->response($item->foto);
    }

    // 🟢 KODE PERBAIKAN: FUNGSI DOWNLOAD FOTO YANG HILANG 🟢
    public function downloadFoto($id)
    {
        $item = MaterialKeluar::findOrFail($id);

        if ($item->foto && Storage::disk('public')->exists($item->foto)) {
            return Storage::disk('public')->download($item->foto);
        }

        return redirect()->back()->with('error', 'File foto tidak ditemukan untuk diunduh.');
    }
    
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_keluar_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKeluar::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();

        if ($request->has('submit_pdf')) {
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];
            $pdf = Pdf::loadView('material_keluar.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        if ($request->has('submit_excel')) {
            // Asumsi MaterialKeluarExport ada
            return Excel::download(new \App\Exports\MaterialKeluarExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}