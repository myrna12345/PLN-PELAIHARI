<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use App\Models\SiagaKeluar; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKembaliExport; 

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
                    ->orWhere('nomor_meter', 'like', "%$search%") // <-- PERBAIKAN: Menggunakan 'nomor_meter'
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
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        
        return view('siaga-kembali.create', compact('materials'));
    }

    /**
     * Store data
     */
    public function store(Request $request)
    {
        // VALIDASI: Menggunakan 'nomor_meter' yang sekarang match dengan kolom database
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'status' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }
        
        // Mapping data untuk disimpan ke SiagaKembali
        $dataToSave = [
            'material_id' => $validated['material_id'],
            'nomor_meter' => $validated['nomor_meter'], // <-- LANGSUNG MENGGUNAKAN 'nomor_meter'
            'nama_petugas' => $validated['nama_petugas'],
            'stand_meter' => $validated['stand_meter'],
            'status' => $validated['status'],
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
        ];
        
        SiagaKembali::create($dataToSave);

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil disimpan!');
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
                             
        // $siagaKembali sekarang secara otomatis memiliki properti 'nomor_meter'
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    /**
     * Update data
     */
    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        // VALIDASI: Menggunakan 'nomor_meter'
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', // <-- KOLOM DATABASE BARU
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'status' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // ... (Logika lama yang dihapus tidak berubah)

        $path = $siagaKembali->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }
        
        // Sekarang $validated sudah berisi kunci 'nomor_meter', yang match dengan kolom DB.
        $dataToUpdate = $validated;
        $dataToUpdate['foto_path'] = $path;
        
        $siagaKembali->update($dataToUpdate);

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
    }


    /**
     * Hapus data
     */
    public function destroy(SiagaKembali $siagaKembali)
    {
        // ... (Logika lama yang dihapus tidak berubah)

        if ($siagaKembali->foto_path) {
            Storage::disk('public')->delete($siagaKembali->foto_path);
        }

        $siagaKembali->delete();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus!');
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
        
        $filename = 'laporan_siaga_kembali_' . $tanggalMulai->format('Y-m-d') . 'sd' . $tanggalAkhir->format('Y-m-d');
        
        if ($request->has('submit_excel')) {
            return Excel::download(new SiagaKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        if ($request->has('submit_pdf')) {
            $items = SiagaKembali::with('material') 
                                 ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                 ->orderBy('tanggal', 'asc')
                                 ->get();

            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai, 
                'tanggal_akhir' => $tanggalAkhir, 
            ];

            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}