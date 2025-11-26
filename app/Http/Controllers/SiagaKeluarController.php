<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiagaKeluarExport; 

class SiagaKeluarController extends Controller
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
        $query = SiagaKeluar::with('material');

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', "%$search%")
                  ->orWhere('stand_meter', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', "%$search%");
                  });
            });
        }

        // Filter Tanggal
        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        // Mengambil data terbaru dan melakukan pagination
        $dataSiagaKeluar = $query->latest('tanggal')->paginate(10); // Variabel disesuaikan

        return view('siaga-keluar.index', compact('dataSiagaKeluar')); // Variabel disesuaikan
    }

    /**
     * Halaman create
     */
    public function create()
    {
        // KHUSUS SIAGA: Ambil material yang kategorinya 'siaga'
        $materials = Material::where('kategori', 'siaga')
                        ->get()
                        ->sortBy('nama_material', SORT_NATURAL);

        return view('siaga-keluar.create', compact('materials'));
    }

    /**
     * Store data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_keluar' => 'required|integer|min:1',
            // TAMBAHAN: Validasi untuk jumlah siaga masuk
            'jumlah_siaga_masuk' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_siaga_keluar', 'public');
        }

        $dataToSave = array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
            'status' => $request->status ?? 'Keluar',
            // Pastikan jika null diisi 0 agar tidak error di tampilan
            'jumlah_siaga_masuk' => $request->jumlah_siaga_masuk ?? 0 
        ]);
        
        unset($dataToSave['foto']); 

        SiagaKeluar::create($dataToSave);

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil ditambahkan.');
    }

    /**
     * Halaman edit
     */
    public function edit($id)
    {
        // Variabel disesuaikan menjadi $item agar cocok dengan View
        $item = SiagaKeluar::findOrFail($id);
        
        // KHUSUS SIAGA: Filter material siaga
        $materials = Material::where('kategori', 'siaga')
                        ->get()
                        ->sortBy('nama_material', SORT_NATURAL);

        return view('siaga-keluar.edit', compact('item', 'materials'));
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $siagaKeluar = SiagaKeluar::findOrFail($id);

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_keluar' => 'required|integer|min:1',
            // TAMBAHAN: Validasi untuk update jumlah siaga masuk
            'jumlah_siaga_masuk' => 'nullable|integer|min:0',
            // PERBAIKAN: Status menjadi nullable
            'status' => 'nullable|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $siagaKeluar->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_keluar', 'public');
        }

        $dataToUpdate = array_merge($validated, [
            'foto_path' => $path,
            'jumlah_siaga_masuk' => $request->jumlah_siaga_masuk ?? 0,
            // Jika status tidak ada di request (karena readonly), gunakan status lama dari model
            'status' => $siagaKeluar->status 
        ]);
        unset($dataToUpdate['foto']);

        $siagaKeluar->update($dataToUpdate);

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $siagaKeluar = SiagaKeluar::findOrFail($id);

        if ($siagaKeluar->foto_path) {
            Storage::disk('public')->delete($siagaKeluar->foto_path);
        }

        $siagaKeluar->delete();

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil dihapus.');
    }
    
    // --- FUNGSI DOWNLOAD FOTO ---
    public function downloadFoto($id)
    {
        $item = SiagaKeluar::findOrFail($id);
        if ($item->foto_path && Storage::disk('public')->exists($item->foto_path)) {
            return Storage::disk('public')->download($item->foto_path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    // --- FUNGSI DOWNLOAD REPORT (PDF & EXCEL) ---
    public function downloadReport(Request $request)
    {
         $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_siaga_keluar_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');

        // === LOGIKA DOWNLOAD PDF ===
        if ($request->has('submit_pdf')) {
            $dataSiagaKeluar = SiagaKeluar::with('material')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                        ->orderBy('tanggal', 'asc')
                        ->get();

            $data = [
                'dataSiagaKeluar' => $dataSiagaKeluar,
                'tanggal_mulai' => $tanggalMulai->format('d M Y'),
                'tanggal_akhir' => $tanggalAkhir->format('d M Y'),
            ];
            
            $pdf = Pdf::loadView('siaga-keluar.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        } 
        
        // === LOGIKA DOWNLOAD EXCEL ===
        if ($request->has('submit_excel')) {
            return Excel::download(new SiagaKeluarExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan.');
    }
}