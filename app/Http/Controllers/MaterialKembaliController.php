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
    // INDEX: Tampilkan data + fitur pencarian dan filter tanggal (TIDAK BERUBAH)
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = MaterialKembali::query();

        // 1. Filter Pencarian Teks
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
            });
        }
        
        // 🟢 2. Filter Berdasarkan Rentang Tanggal 🟢
        if ($tanggalMulai) {
            $query->where('tanggal', '>=', Carbon::parse($tanggalMulai)->startOfDay());
        }

        if ($tanggalAkhir) {
            $query->where('tanggal', '<=', Carbon::parse($tanggalAkhir)->endOfDay());
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();

        return view('material_kembali.index', compact('materialKembali', 'tanggalMulai', 'tanggalAkhir'));
    }

    public function create()
    {
        // Filter agar hanya mengambil material yang BUKAN 'siaga'
        $materialList = Material::where('kategori', '!=', 'siaga')
                                   ->orWhereNull('kategori')
                                   ->orderBy('nama_material')
                                   ->get();
        // Ambil daftar satuan unik dari Material Stand By untuk pilihan
        $satuanList = MaterialStandBy::select('satuan')->distinct()->pluck('satuan');
        if ($satuanList->isEmpty()) {
            $satuanList = Material::select('satuan')->distinct()->pluck('satuan');
        }
                                   
        return view('material_kembali.create', compact('materialList', 'satuanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            // 🟢 TAMBAH VALIDASI SATUAN
            'satuan_material' => 'required|string|max:50', 
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        $materialSource = Material::where('nama_material', $validated['nama_material'])->first();
        $jumlahKembali = $validated['jumlah_material'];
        $satuanKembali = $validated['satuan_material'];
        
        if ($materialSource) {
            $materialId = $materialSource->id;

            // 🟢 LOGIKA PENAMBAHAN STOK 🟢
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            
            if ($materialStok) {
                // ⚠️ PENTING: Cek konsistensi satuan
                if ($materialStok->satuan !== $satuanKembali) {
                     return redirect()->back()->with('error', 'Gagal: Satuan material yang dikembalikan (' . $satuanKembali . ') tidak cocok dengan satuan stok (' . $materialStok->satuan . ') di Material Stand By.')->withInput();
                }
                
                // Tambahkan stok
                $materialStok->increment('jumlah', $jumlahKembali);
            } else {
                return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item ini belum tercatat.')->withInput();
            }
        } else {
            return redirect()->back()->with('error', 'Gagal: Material tidak ditemukan dalam daftar master.')->withInput();
        }

        // Simpan record Material Kembali (sudah termasuk satuan_material)
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
                                   
        // Ambil daftar satuan unik
        $satuanList = MaterialStandBy::select('satuan')->distinct()->pluck('satuan');
        if ($satuanList->isEmpty()) {
            $satuanList = Material::select('satuan')->distinct()->pluck('satuan');
        }

        return view('material_kembali.edit', compact('materialKembali', 'materialList', 'satuanList'));
    }

    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        // Simpan data lama sebelum update
        $jumlahLama = $materialKembali->jumlah_material;
        $satuanLama = $materialKembali->satuan_material; 
        $namaMaterialLama = $materialKembali->nama_material;

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|max:50', 
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $jumlahBaru = $validated['jumlah_material'];
        $satuanBaru = $validated['satuan_material'];
        $namaMaterialBaru = $validated['nama_material'];
        
        // --- 🟢 LOGIKA PEMBARUAN STOK YANG KOMPLEKS 🟢 ---

        // Cek apakah terjadi perubahan pada Nama Material ATAU Satuan Material
        $materialBerubah = ($namaMaterialLama != $namaMaterialBaru || $satuanLama != $satuanBaru);
        
        if ($materialBerubah) {
            // A. Undo Stok Lama (Mengurangi Stok Lama)
            $materialSourceLama = Material::where('nama_material', $namaMaterialLama)->first();
            if ($materialSourceLama) {
                $materialStokLama = MaterialStandBy::where('material_id', $materialSourceLama->id)->first();
                if ($materialStokLama) {
                    // Cek konsistensi satuan lama sebelum dikurangi
                    if ($materialStokLama->satuan !== $satuanLama) {
                        return redirect()->back()->with('error', 'Gagal update: Satuan lama material (' . $satuanLama . ') tidak cocok dengan satuan stok lama (' . $materialStokLama->satuan . ').')->withInput();
                    }

                    // Pastikan stok lama cukup sebelum ditarik
                    if ($materialStokLama->jumlah >= $jumlahLama) {
                         $materialStokLama->decrement('jumlah', $jumlahLama);
                    } else {
                         return redirect()->back()->with('error', 'Gagal update: Pengurangan stok lama (' . $namaMaterialLama . ') melebihi stok yang tersedia.')->withInput();
                    }
                }
            } else {
                 return redirect()->back()->with('error', 'Gagal update: Material lama tidak ditemukan di daftar master.')->withInput();
            }

            // B. Apply Stok Baru (Menambahkan Stok Baru)
            $materialSourceBaru = Material::where('nama_material', $namaMaterialBaru)->first();
            if ($materialSourceBaru) {
                $materialStokBaru = MaterialStandBy::where('material_id', $materialSourceBaru->id)->first();
                if ($materialStokBaru) {
                    // Cek konsistensi satuan baru sebelum ditambah
                    if ($materialStokBaru->satuan !== $satuanBaru) {
                        return redirect()->back()->with('error', 'Gagal update: Satuan baru material (' . $satuanBaru . ') tidak cocok dengan satuan stok baru (' . $materialStokBaru->satuan . ').')->withInput();
                    }
                    
                    // Tambahkan stok baru
                    $materialStokBaru->increment('jumlah', $jumlahBaru);
                } else {
                    return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By untuk item baru belum tercatat.')->withInput();
                }
            } else {
                 return redirect()->back()->with('error', 'Gagal update: Material baru tidak ditemukan di daftar master.')->withInput();
            }
        
        } else {
            // C. Hanya Penyesuaian Jumlah (Nama Material dan Satuan Sama)
            $materialSource = Material::where('nama_material', $namaMaterialBaru)->first();
            $stokSelisih = $jumlahBaru - $jumlahLama;

            if ($materialSource && $stokSelisih !== 0) {
                $materialId = $materialSource->id;
                $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
                
                if ($materialStok) {
                    // Cek konsistensi satuan (sekali lagi)
                    if ($materialStok->satuan !== $satuanBaru) {
                        return redirect()->back()->with('error', 'Gagal update: Satuan material yang dikembalikan (' . $satuanBaru . ') tidak cocok dengan satuan stok (' . $materialStok->satuan . ').')->withInput();
                    }

                    if ($stokSelisih > 0) {
                        // Jika jumlah bertambah, tambahkan stok
                        $materialStok->increment('jumlah', $stokSelisih);
                    } else {
                        // Jika jumlah berkurang, kurangi stok (pastikan stok cukup)
                        if ($materialStok->jumlah >= abs($stokSelisih)) {
                            $materialStok->decrement('jumlah', abs($stokSelisih));
                        } else {
                            return redirect()->back()->with('error', 'Gagal update: Pengurangan jumlah kembali melebihi stok yang tersedia di Material Stand By (Tersedia: ' . $materialStok->jumlah . ').')->withInput();
                        }
                    }
                } else {
                    return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By tidak ditemukan.')->withInput();
                }
            }
        }
        
        // --- END LOGIKA STOK ---
        
        // Pengecekan Foto (TIDAK BERUBAH)
        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                Storage::disk('public')->delete($materialKembali->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        // Simpan perubahan ke record Material Kembali
        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui! Stok Stand By disesuaikan.');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        
        // 🟢 LOGIKA PENGEMBALIAN STOK SAAT DELETE (TIDAK BERUBAH) 🟢
        $materialSource = Material::where('nama_material', $data->nama_material)->first();
        if ($materialSource) {
            $materialStok = MaterialStandBy::where('material_id', $materialSource->id)->first();
            if ($materialStok) {
                 // Cek konsistensi satuan sebelum mengurangi stok
                 if ($materialStok->satuan !== $data->satuan_material) {
                     return redirect()->back()->with('error', 'Gagal hapus: Satuan material yang dihapus tidak cocok dengan satuan stok.')->withInput();
                 }

                // Kurangi stok (undo increment), pastikan stok cukup
                if ($materialStok->jumlah >= $data->jumlah_material) {
                     $materialStok->decrement('jumlah', $data->jumlah_material);
                } else {
                     return redirect()->back()->with('error', 'Gagal hapus: Jumlah material yang akan ditarik kembali (karena penghapusan) melebihi stok yang tersedia.')->withInput();
                }
                
            }
        }
        // END LOGIKA PENGEMBALIAN STOK

        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        $data->delete();

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus! Stok Stand By dikurangi kembali.');
    }
    
    // ... (Fungsi showFoto dan downloadFoto tetap sama) ...
    public function showFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);

        if (!$item->foto || !Storage::disk('public')->exists($item->foto)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        return Storage::disk('public')->response($item->foto);
    }

    public function downloadFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);

        if ($item->foto && Storage::disk('public')->exists($item->foto)) {
            return Storage::disk('public')->download($item->foto);
        }
        
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
            // Memanggil export dengan rentang tanggal
            return Excel::download(new \App\Exports\MaterialKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}