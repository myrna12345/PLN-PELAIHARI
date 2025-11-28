<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use App\Models\SiagaKeluar; // 🟢 IMPORT MODEL STOK LAWAN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
// use App\Exports\SiagaKembaliExport; 
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini diimpor
use App\Exports\SiagaKembaliExport; // Pastikan ini diimpor

class SiagaKembaliController extends Controller
{
    /**
     * Halaman index
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        // Eager load relasi 'material'
        $query = SiagaKembali::with('material'); 
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', "%$search%")
                  ->orWhere('stand_meter', 'like', "%$search%")
                  ->orWhere('nomor_unit', 'like', "%$search%")
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', "%$search%");
                  });
            });
        }
        
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10);
        
        return view('siaga-kembali.index', compact('items'));
    }

    /**
     * Halaman create
     */
    public function create()
    {
        // Filter untuk hanya menampilkan 2 material KWH Siaga.
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];

        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials) // Filter berdasarkan nama material
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        
        return view('siaga-kembali.create', compact('materials'));
    }

    /**
     * Store data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_unit' => 'required|integer|min:1|max:50',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'status' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }
        
        $jumlahKembali = $validated['jumlah_siaga_kembali'];

        // 🟢 LOGIKA PENAMBAHAN JUMLAH MASUK PADA SIAGA KELUAR 🟢
        $dataSiagaKeluar = SiagaKeluar::where('material_id', $validated['material_id'])
                                      ->where('nomor_unit', $validated['nomor_unit'])
                                      ->latest('tanggal')
                                      ->first();

        if ($dataSiagaKeluar) {
            // Tambahkan jumlah_siaga_masuk di record Siaga Keluar
            $dataSiagaKeluar->increment('jumlah_siaga_masuk', $jumlahKembali);
        } else {
             // Opsional: Berikan pesan error jika tidak ada transaksi Siaga Keluar yang cocok
             return redirect()->back()->with('error', 'Gagal: Tidak ada transaksi Siaga Keluar yang cocok untuk material ini.')->withInput();
        }
        // END LOGIKA PENAMBAHAN JUMLAH MASUK

        SiagaKembali::create(array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
        ]));

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil disimpan! Jumlah masuk pada Siaga Keluar disesuaikan.');
    }

    /**
     * Halaman edit
     */
    public function edit(SiagaKembali $siagaKembali)
    {
        // Terapkan filter yang sama pada method edit
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    /**
     * Update data
     */
    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        $jumlahLama = $siagaKembali->jumlah_siaga_kembali;

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_unit' => 'required|integer|min:1|max:50',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'status' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $jumlahBaru = $validated['jumlah_siaga_kembali'];
        $stokSelisih = $jumlahBaru - $jumlahLama;

        // 🟢 LOGIKA PENYESUAIAN JUMLAH MASUK PADA SIAGA KELUAR 🟢
        if ($stokSelisih !== 0) {
            $dataSiagaKeluar = SiagaKeluar::where('material_id', $validated['material_id'])
                                          ->where('nomor_unit', $validated['nomor_unit'])
                                          ->latest('tanggal')
                                          ->first();
            if ($dataSiagaKeluar) {
                if ($stokSelisih > 0) {
                    // Jika jumlah bertambah, tambahkan jumlah_siaga_masuk di Siaga Keluar
                    $dataSiagaKeluar->increment('jumlah_siaga_masuk', $stokSelisih);
                } else {
                    // Jika jumlah berkurang, kurangi jumlah_siaga_masuk di Siaga Keluar
                    $dataSiagaKeluar->decrement('jumlah_siaga_masuk', abs($stokSelisih));
                }
            }
        }
        // END LOGIKA PENYESUAIAN

        $path = $siagaKembali->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }
        
        $siagaKembali->update(array_merge($validated, ['foto_path' => $path]));

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui! Jumlah masuk pada Siaga Keluar disesuaikan.');
    }


    /**
     * Hapus data
     */
    public function destroy(SiagaKembali $siagaKembali)
    {
        // 🟢 LOGIKA PENGEMBALIAN JUMLAH MASUK PADA SIAGA KELUAR SAAT DELETE 🟢
        $jumlahDikembalikan = $siagaKembali->jumlah_siaga_kembali;

        $dataSiagaKeluar = SiagaKeluar::where('material_id', $siagaKembali->material_id)
                                      ->where('nomor_unit', $siagaKembali->nomor_unit)
                                      ->latest('tanggal')
                                      ->first();
        if ($dataSiagaKeluar) {
            // Kurangi kembali jumlah_siaga_masuk karena record Siaga Kembali dihapus (undo increment)
            $dataSiagaKeluar->decrement('jumlah_siaga_masuk', $jumlahDikembalikan);
        }
        // END LOGIKA PENGEMBALIAN

        if ($siagaKembali->foto_path) {
            Storage::disk('public')->delete($siagaKembali->foto_path);
        }

        $siagaKembali->delete();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus! Jumlah masuk pada Siaga Keluar dikurangi kembali.');
    }

    public function showFoto(SiagaKembali $siagaKembali) 
    {
        if (!$siagaKembali->foto_path || !Storage::disk('public')->exists($siagaKembali->foto_path)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        return Storage::disk('public')->response($siagaKembali->foto_path);
    }
    
    public function downloadFoto(SiagaKembali $siagaKembali)
    {
        if ($siagaKembali->foto_path && Storage::disk('public')->exists($siagaKembali->foto_path)) {
            return Storage::disk('public')->download($siagaKembali->foto_path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    /**
     * FUNGSI DOWNLOAD REPORT
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_siaga_kembali_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');
        
        // 🟢 LOGIKA DOWNLOAD EXCEL 🟢
        if ($request->has('submit_excel')) {
            // Menggunakan class SiagaKembaliExport yang Anda sediakan
            return Excel::download(new SiagaKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        // 2. LOGIKA DOWNLOAD PDF
        if ($request->has('submit_pdf')) {
            $items = SiagaKembali::with('material') // Pastikan relasi material dimuat
                                 ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                 ->get();

            // Mengirim variabel dalam bentuk array dengan kunci snake_case (agar sesuai dengan Blade PDF)
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai, 
                'tanggal_akhir' => $tanggalAkhir, 
            ];

            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }
        
        // Fallback error
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}