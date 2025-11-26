<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; // Menggunakan Model yang benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
// Asumsi ada Export untuk Siaga Kembali
// use App\Exports\SiagaKembaliExport; 

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
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', "%$search%");
                  });
            });
        }
        
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10);
        // PERBAIKAN UTAMA: Mengubah panggilan view dari underscore menjadi tanda hubung
        return view('siaga-kembali.index', compact('items'));
    }

    /**
     * Halaman create (Metode ini perlu diimplementasikan sesuai kebutuhan Anda)
     */
    public function create()
    {
        $materials = Material::where('kategori', 'siaga')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        // PERBAIKAN: Mengubah panggilan view dari underscore menjadi tanda hubung
        return view('siaga-kembali.create', compact('materials'));
    }

    /**
     * Store data (Metode ini perlu diimplementasikan sesuai kebutuhan Anda)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'status' => 'nullable|string',
            // PERBAIKAN: Menaikkan batas ukuran file dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }

        SiagaKembali::create(array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
        ]));

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Halaman edit (Metode ini perlu diimplementasikan sesuai kebutuhan Anda)
     */
    public function edit(SiagaKembali $siagaKembali)
    {
        $materials = Material::where('kategori', 'siaga')->get()->sortBy('nama_material', SORT_NATURAL);
        // PERBAIKAN: Mengubah panggilan view dari underscore menjadi tanda hubung
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    /**
     * Update data (Metode ini perlu diimplementasikan sesuai kebutuhan Anda)
     */
    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'status' => 'nullable|string',
            // PERBAIKAN: Menaikkan batas ukuran file dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $path = $siagaKembali->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }

        $siagaKembali->update(array_merge($validated, ['foto_path' => $path]));

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
    }


    /**
     * Hapus data
     */
    public function destroy(SiagaKembali $siagaKembali)
    {
        if ($siagaKembali->foto_path) {
            Storage::disk('public')->delete($siagaKembali->foto_path);
        }

        $siagaKembali->delete();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    /**
     * FUNGSI BARU: Melayani file foto secara langsung melalui Controller (Solusi Anti-Symlink).
     * Menggunakan Route Model Binding.
     */
    public function showFoto(SiagaKembali $siagaKembali) 
    {
        // Asumsi kolom path foto bernama 'foto_path'
        if (!$siagaKembali->foto_path || !Storage::disk('public')->exists($siagaKembali->foto_path)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        // PERBAIKAN UTAMA: Menggunakan Storage::response() untuk keamanan dan keandalan.
        return Storage::disk('public')->response($siagaKembali->foto_path);
    }
    
    /**
     * FUNGSI DOWNLOAD FOTO (Menggunakan Route Model Binding).
     */
    public function downloadFoto(SiagaKembali $siagaKembali)
    {
        // Asumsi kolom path foto bernama 'foto_path'
        if ($siagaKembali->foto_path && Storage::disk('public')->exists($siagaKembali->foto_path)) {
            return Storage::disk('public')->download($siagaKembali->foto_path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    /**
     * FUNGSI DOWNLOAD REPORT (Metode ini perlu diimplementasikan sesuai kebutuhan Anda)
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        // Logika query dan download PDF/Excel Anda di sini
        $filename = 'laporan_siaga_kembali_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');
        
        // Anda perlu menyesuaikan logika download sesuai kebutuhan (contoh di bawah)
        if ($request->has('submit_pdf')) {
            $items = SiagaKembali::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])->get();
            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', compact('items', 'tanggalMulai', 'tanggalAkhir'));
            return $pdf->download($filename . '.pdf');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}