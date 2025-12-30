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
    /**
     * Pastikan Anda telah menambahkan relasi berikut di model MaterialKeluar:
     * * public function material()
     * {
     * return $this->belongsTo(Material::class, 'material_id');
     * }
     */

    // INDEX: Tampilkan data + fitur pencarian
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        // Menggunakan with('material') untuk eager loading data relasi Material
        $query = MaterialKeluar::with('material');

        // Filter pencarian:
        if ($search) {
            // Perbaikan: Cari berdasarkan nama_petugas di tabel material_keluar
            // ATAU cari nama_material di tabel 'materials' melalui relasi.
            $query->where('nama_petugas', 'like', "%{$search}%")
                  ->orWhereHas('material', function ($q) use ($search) {
                      $q->where('nama_material', 'like', "%{$search}%");
                  });
        }

        // Filter tanggal (Tambahan: jika Anda menggunakan filter tanggal)
        if ($tanggalMulai && $tanggalAkhir) {
             $query->whereBetween('tanggal', [
                Carbon::parse($tanggalMulai)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay()
            ]);
        }


        // Urutkan dari terbaru dan paginate
        $materialKeluar = $query->orderByDesc('tanggal')->paginate(10);

        // 🟢 PENAMBAHAN: Ambil Stok Material Stand By untuk setiap item 🟢
        $materialKeluar->each(function ($item) {
            // Ambil satu baris MaterialStandBy berdasarkan material_id yang terkait
            $materialStok = MaterialStandBy::where('material_id', $item->material_id)->first();
            
            // Tambahkan properti 'stok_saat_ini' pada objek $item
            if ($materialStok) {
                // Format jumlah dan satuan, misal: "10 Buah"
                $item->stok_saat_ini = $materialStok->jumlah . ' ' . $materialStok->satuan;
            } else {
                // Jika tidak ada stok tercatat (walaupun seharusnya tidak terjadi jika Store/Update berjalan normal)
                $item->stok_saat_ini = '0';
            }
        });

        return view('material_keluar.index', compact('materialKeluar'));
    }

    // CREATE
    public function create()
    {
        $materialList = Material::where('kategori', '!=', 'siaga')
                                ->orWhereNull('kategori')
                                ->get();

        // Daftar satuan, samakan dengan Material Kembali
        $satuanList = ['Buah', 'Meter'];

        return view('material_keluar.create', compact('materialList', 'satuanList'));
    }



    // 💾 STORE
    public function store(Request $request)
    {
            $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|in:Buah,Meter',
            'keterangan' => 'required|string|max:1000',
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // 1. Tambahkan waktu saat ini otomatis
        $validated['tanggal'] = now('Asia/Makassar');

        // 2. Upload foto material
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        // 3. Upload foto petugas
        if ($request->hasFile('foto_petugas')) {
            $validated['foto_petugas'] = $request->file('foto_petugas')->store('foto_petugas', 'public');
        }

        // 4. Mengambil material_id langsung dari $validated
        $materialId = $validated['material_id'];
        $jumlahKeluar = $validated['jumlah_material'];

        // Cek apakah material_id valid (Sudah divalidasi dengan 'exists', tapi untuk mengambil data)
        $materialSource = Material::find($materialId);

        // 🟢 3. LOGIKA PENGURANGAN STOK (INVENTORY LOGIC) 🟢
        if ($materialSource) {
            // materialId sudah tersedia
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                // Cek ketersediaan stok
                if ($materialStok->jumlah >= $jumlahKeluar) {
                    // Kurangi stok
                    $materialStok->decrement('jumlah', $jumlahKeluar);
                    
                    // 4. Buat record Material Keluar
                    // Array $validated sudah berisi 'material_id' dan 'satuan_material'
                    MaterialKeluar::create($validated);

                    return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan dan stok Stand By berhasil dikurangi!');
                } else {
                    // Stok tidak cukup
                    return redirect()->back()->with('error', 'Gagal: Jumlah material keluar melebihi stok yang tersedia di Material Stand By (Stok tersedia: ' . $materialStok->jumlah . ' ' . $materialStok->satuan . ')')->withInput();
                }
            } else {
                 // Tidak ada stok awal yang tercatat
                 return redirect()->back()->with('error', 'Gagal: Material ini tidak memiliki stok awal yang tercatat di Material Stand By.')->withInput();
            }
        }
        
        // Fallback (Sebenarnya tidak tercapai jika validasi exists:materials,id berhasil)
        return redirect()->back()->with('error', 'Gagal: Material ID tidak ditemukan dalam daftar Material utama.')->withInput();
    }
    
    // lihat show
    public function lihat($id)
    {
        // Menggunakan with('material') untuk memuat data material
        $item = MaterialKeluar::with('material')->findOrFail($id);
        return view('material_keluar.lihat', compact('item'));
    }

    // ✏️ EDIT
    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);

        $materialList = Material::where('kategori', '!=', 'siaga')
                                ->orWhereNull('kategori')
                                ->get();

        // Sama seperti create()
        $satuanList = ['Buah', 'Meter'];

        return view('material_keluar.edit', compact('data', 'materialList', 'satuanList'));
    }


    // 🔁 UPDATE
    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        // Simpan jumlah lama sebelum update
        $jumlahLama = $data->jumlah_material; 

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|in:Buah,Meter',
            'keterangan' => 'required|string|max:1000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        
        $jumlahBaru = $validated['jumlah_material'];
        // PERBAIKAN: Gunakan material_id dari input baru
        $materialIdBaru = $validated['material_id'];
        
        // Cek apakah material_id berubah
        $materialIdLama = $data->material_id;
        
        $stokSelisih = $jumlahBaru - $jumlahLama;

        // Logika kompleks karena ID material mungkin berubah
        
        // KASUS 1: ID Material Berubah (Perlu mengembalikan stok lama & mengurangi stok baru)
        if ($materialIdBaru != $materialIdLama) {
             // 1. Kembalikan stok lama (berdasarkan material_id lama)
             $materialStokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
             if ($materialStokLama) {
                // Tambahkan kembali jumlah yang lama
                $materialStokLama->increment('jumlah', $jumlahLama);
             }

             // 2. Kurangi stok baru (berdasarkan material_id baru)
             $materialStokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
             if ($materialStokBaru) {
                 if ($materialStokBaru->jumlah >= $jumlahBaru) {
                     $materialStokBaru->decrement('jumlah', $jumlahBaru);
                 } else {
                     // Gagal, kembalikan stok lama lagi
                     if ($materialStokLama) {
                         $materialStokLama->decrement('jumlah', $jumlahLama);
                     }
                     return redirect()->back()->with('error', 'Gagal update: Jumlah material baru melebihi stok yang tersedia (Tersedia: ' . $materialStokBaru->jumlah . ' ' . $materialStokBaru->satuan . ')')->withInput();
                 }
             } else {
                 // Gagal, kembalikan stok lama lagi
                 if ($materialStokLama) {
                     $materialStokLama->decrement('jumlah', $jumlahLama);
                 }
                 return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By baru tidak ditemukan.')->withInput();
             }

        // KASUS 2: ID Material SAMA & Jumlah Berubah (Perlu menyesuaikan selisih)
        } elseif ($stokSelisih !== 0) {
            $materialStok = MaterialStandBy::where('material_id', $materialIdBaru)->first(); // materialIdBaru = materialIdLama
            
            if ($materialStok) {
                if ($stokSelisih > 0) {
                    // Jika jumlah bertambah (pengeluaran lebih banyak), kurangi stok
                    if ($materialStok->jumlah >= $stokSelisih) {
                        $materialStok->decrement('jumlah', $stokSelisih);
                    } else {
                        return redirect()->back()->with('error', 'Gagal update: Penambahan pengeluaran melebihi stok yang tersedia (Tersedia: ' . $materialStok->jumlah . ' ' . $materialStok->satuan . ')')->withInput();
                    }
                } else {
                    // Jika jumlah berkurang (pengeluaran ditarik), tambahkan stok
                    $materialStok->increment('jumlah', abs($stokSelisih));
                }
            } else {
                 return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By tidak ditemukan.')->withInput();
            }
        }
        // KASUS 3: ID Material SAMA & Jumlah SAMA (Tidak ada penyesuaian stok)
        // Lanjutkan update data non-stok

        // LOGIKA UPLOAD FOTO (Tidak Berubah)
        if ($request->hasFile('foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }
        
        // LOGIKA UPLOAD FOTO PETUGAS
        if ($request->hasFile('foto_petugas')) {
            if ($data->foto_petugas) {
                Storage::disk('public')->delete($data->foto_petugas);
            }
            $validated['foto_petugas'] = $request->file('foto_petugas')->store('foto_petugas', 'public');
        }


        // Array $validated sudah berisi 'material_id', 'satuan_material', dll.
        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui dan stok Stand By disesuaikan!');
    }

    // 🗑 DELETE
    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        // 🟢 LOGIKA PENGEMBALIAN STOK SAAT DELETE 🟢
        // PERBAIKAN: Gunakan material_id dari data lama
        $materialId = $data->material_id;
        $jumlahKeluar = $data->jumlah_material;

        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
        if ($materialStok) {
            // Tambahkan kembali jumlah yang dikeluarkan
            $materialStok->increment('jumlah', $jumlahKeluar);
        }
        // END LOGIKA PENGEMBALIAN STOK

        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        if ($data->foto_petugas) {
            Storage::disk('public')->delete($data->foto_petugas);
        }

        $data->delete();

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus dan stok Stand By dikembalikan!');
    }
    
    // ... (Fungsi showFoto dan downloadReport tetap sama) ...
    public function showFoto(MaterialKeluar $materialKeluar)
    {
        if (!$materialKeluar->foto || !Storage::disk('public')->exists($materialKeluar->foto)) {
            abort(404);
        }

        return Storage::disk('public')->response($materialKeluar->foto);
    }


    // FUNGSI SHOW FOTO PETUGAS
    public function showFotoPetugas(MaterialKeluar $materialKeluar)
    {
        if (!$materialKeluar->foto_petugas || !Storage::disk('public')->exists($materialKeluar->foto_petugas)) {
            abort(404);
        }

        return Storage::disk('public')->response($materialKeluar->foto_petugas);
    }



    // 🟢 KODE PERBAIKAN: FUNGSI DOWNLOAD FOTO YANG HILANG 🟢
    public function downloadFoto(MaterialKeluar $materialKeluar)
    {
        if (!$materialKeluar->foto || !Storage::disk('public')->exists($materialKeluar->foto)) {
            abort(404);
        }

        return Storage::disk('public')->download($materialKeluar->foto);
    }


    public function downloadFotoPetugas(MaterialKeluar $materialKeluar)
    {
        if (!$materialKeluar->foto_petugas || !Storage::disk('public')->exists($materialKeluar->foto_petugas)) {
            abort(404);
        }

        return Storage::disk('public')->download($materialKeluar->foto_petugas);
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

        // PERBAIKAN: Menggunakan with('material') untuk memuat data material
        $items = MaterialKeluar::with('material') 
                               ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();
        // 🟢 PENAMBAHAN: Sisipkan data Stok Material Stand By saat ini untuk setiap item 🟢
        // Logika ini sama dengan yang digunakan di fungsi index() sebelumnya.
        $items->each(function ($item) {
            // Ambil satu baris MaterialStandBy (stok material saat ini)
            $materialStok = \App\Models\MaterialStandBy::where('material_id', $item->material_id)->first();
            
            // Tambahkan properti 'stok_saat_ini' pada objek $item
            $item->stok_saat_ini = $materialStok 
                                    ? $materialStok->jumlah . ' ' . $materialStok->satuan 
                                    : '0';
        });
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