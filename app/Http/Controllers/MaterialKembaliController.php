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
    /**
     * Helper: Mendapatkan Nama Material berdasarkan ID
     */
    private function getMaterialNameById($materialId)
    {
        $material = Material::find($materialId);
        return $material ? $material->nama_material : 'Material Tidak Ditemukan';
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = MaterialKembali::query();

        // 📝 Perbaikan: Filter sekarang mencari berdasarkan ID (jika input adalah angka) atau nama petugas
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Asumsi: jika search adalah angka, cari di material_id, jika string, cari di nama_petugas
                if (is_numeric($search)) {
                    $q->where('material_id', $search);
                } else {
                    $q->where('nama_petugas', 'like', "%{$search}%");
                }
            });
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10);

        // 💡 Catatan: Di sini, Anda mungkin perlu memuat (eager load) data Material atau
        // memetakan (map) data untuk menampilkan nama material di view index.

        return view('material_kembali.index', compact('materialKembali'));
    }

    public function create()
    {
        // Filter agar hanya mengambil material yang BUKAN 'siaga'
        $materialList = Material::where('kategori', '!=', 'siaga')
                                   ->orWhereNull('kategori')
                                   ->orderBy('nama_material')
                                   ->get();
        // 🟢 PERBAIKAN: Tambahkan daftar satuan yang tersedia untuk dikirim ke view
        $satuanList = ['Buah', 'Meter']; 

        // 📝 PERUBAHAN: Kirim $satuanList ke view
        return view('material_kembali.create', compact('materialList', 'satuanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id', // 📝 Perbaikan: Menggunakan material_id
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter', // 🟢 PERBAIKAN: Tambahkan validasi Satuan
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        $materialId = $validated['material_id'];
        $jumlahKembali = $validated['jumlah_material'];
        
        // 🟢 1. LOGIKA PENAMBAHAN STOK (INCREMENT) 🟢
        // Cari record Material Stand By yang relevan
        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
        
        if ($materialStok) {
            // Tambahkan stok
            $materialStok->increment('jumlah', $jumlahKembali);
        } else {
            return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item ini belum tercatat.')->withInput();
        }

        // 2. Simpan record Material Kembali
        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan! Stok Stand By bertambah.');
    }

    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        
        // Ambil nama material untuk ditampilkan
        $item->nama_material = $this->getMaterialNameById($item->material_id); 
        
        return view('material_kembali.lihat', compact('item'));
    }

    public function edit($id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        $materialList = Material::where('kategori', '!=', 'siaga')
                                   ->orWhereNull('kategori')
                                   ->orderBy('nama_material')
                                   ->get(); 
        // 🟢 PERBAIKAN: Tambahkan daftar satuan yang tersedia untuk dikirim ke view
        $satuanList = ['Buah', 'Meter']; 

        // 📝 PERUBAHAN: Kirim $satuanList ke view
        return view('material_kembali.edit', compact('materialKembali', 'materialList', 'satuanList'));
    }

    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        // Simpan jumlah lama sebelum update
        $jumlahLama = $materialKembali->jumlah_material;
        // Simpan ID material lama
        $materialIdLama = $materialKembali->material_id; 

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id', // 📝 Perbaikan: Menggunakan material_id
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter', // 🟢 PERBAIKAN: Tambahkan validasi Satuan
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $materialIdBaru = $validated['material_id'];
        $jumlahBaru = $validated['jumlah_material'];
        
        // 🟢 LOGIKA PENYESUAIAN STOK (UPDATE LOGIC) 🟢
        
        // Kasus 1: Material ID tidak berubah
        if ($materialIdBaru == $materialIdLama) {
            $stokSelisih = $jumlahBaru - $jumlahLama;

            if ($stokSelisih !== 0) {
                $materialStok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
                
                if ($materialStok) {
                    if ($stokSelisih > 0) {
                        // Jika jumlah bertambah, tambahkan stok
                        $materialStok->increment('jumlah', $stokSelisih);
                    } else {
                        // Jika jumlah berkurang, kurangi stok
                        $materialStok->decrement('jumlah', abs($stokSelisih));
                    }
                } else {
                    return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item baru belum tercatat.')->withInput();
                }
            }
        } 
        // Kasus 2: Material ID berubah (Mengembalikan stok lama dan menambah stok baru)
        else {
            // 1. Kurangi stok material lama
            $materialStokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($materialStokLama) {
                $materialStokLama->decrement('jumlah', $jumlahLama);
            }

            // 2. Tambahkan stok material baru
            $materialStokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($materialStokBaru) {
                $materialStokBaru->increment('jumlah', $jumlahBaru);
            } else {
                 // Jika material baru tidak ada di stok standby, kembalikan stok lama (opsional: untuk menjaga konsistensi)
                 if($materialStokLama) $materialStokLama->increment('jumlah', $jumlahLama);
                 return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item baru belum tercatat.')->withInput();
            }
        }

        // --- Logika Update Foto ---
        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                Storage::disk('public')->delete($materialKembali->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }
        
        // 📝 PERUBAHAN: $validated sekarang mencakup 'satuan'
        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui! Stok Stand By disesuaikan.');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        
        // 🟢 LOGIKA PENGEMBALIAN STOK SAAT DELETE 🟢
        $materialId = $data->material_id; // 📝 Perbaikan: Menggunakan material_id
        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
        
        if ($materialStok) {
            // Kurangi stok (undo increment)
            $materialStok->decrement('jumlah', $data->jumlah_material);
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
        $filename = 'laporan_material_kembali_' . $tanggalMulai->format('Ymd') . 'sd' . $tanggalAkhir->format('Ymd');

        $items = MaterialKembali::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();

        // 💡 Catatan: Di sini, Anda mungkin perlu memuat (eager load) data Material atau
        // memetakan (map) data untuk menampilkan nama material di laporan.

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