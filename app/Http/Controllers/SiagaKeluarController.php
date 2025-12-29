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
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = SiagaKeluar::with('material'); 
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', "%$search%")
                    ->orWhere('stand_meter', 'like', "%$search%")
                    ->orWhere('nomor_meter', 'like', "%$search%") 
                    ->orWhereHas('material', function($subQ) use ($search) {
                        $subQ->where('nama_material', 'like', "%$search%");
                    });
            });
        }
        
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $dataSiagaKeluar = $query->latest('tanggal')->paginate(10);
        
        return view('siaga-keluar.index', compact('dataSiagaKeluar'));
    }

    public function create()
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];

        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        
        return view('siaga-keluar.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500', 
            'status' => 'required|string|max:255', 
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // TAMBAHAN VALIDASI
        ]);

        // Upload Foto Material
        $path = $request->file('foto')->store('fotos_siaga_keluar', 'public');
        
        // TAMBAHAN: Upload Foto Petugas
        $pathPetugas = $request->file('foto_petugas')->store('fotos_siaga_keluar', 'public');
        
        $material = Material::findOrFail($validated['material_id']);

        $dataToSave = [
            'material_id' => $validated['material_id'],
            'nomor_meter' => $validated['nomor_meter'], 
            'nama_material_lengkap' => $material->nama_material, 
            'nama_petugas' => $validated['nama_petugas'],
            'stand_meter' => $validated['stand_meter'],
            'keterangan' => $validated['keterangan'], 
            'status' => $validated['status'],
            'foto_path' => $path,
            'foto_petugas' => $pathPetugas, // TAMBAHAN SIMPAN PATH
            'tanggal' => Carbon::now('Asia/Makassar'),
        ];
        
        SiagaKeluar::create($dataToSave);

        // Update status di tabel Standby
        \App\Models\MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
            ->update(['status' => 'Terpakai']);

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil disimpan!');
    }

    public function edit(SiagaKeluar $siagaKeluar)
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-keluar.edit', ['item' => $siagaKeluar, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKeluar $siagaKeluar)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500', 
            'status' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // TAMBAHAN VALIDASI
        ]);

        // Update Foto Material
        $path = $siagaKeluar->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_keluar', 'public');
        }

        // TAMBAHAN: Update Foto Petugas
        $pathPetugas = $siagaKeluar->foto_petugas;
        if ($request->hasFile('foto_petugas')) {
            if ($pathPetugas) { Storage::disk('public')->delete($pathPetugas); }
            $pathPetugas = $request->file('foto_petugas')->store('fotos_siaga_keluar', 'public');
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $dataToUpdate = $validated;
        $dataToUpdate['foto_path'] = $path;
        $dataToUpdate['foto_petugas'] = $pathPetugas; // TAMBAHAN UPDATE PATH
        $dataToUpdate['nama_material_lengkap'] = $material->nama_material; 
        $dataToUpdate['nomor_meter'] = $validated['nomor_meter']; 
        $dataToUpdate['keterangan'] = $validated['keterangan']; 
        
        unset($dataToUpdate['nomor_meter']); 

        $siagaKeluar->update($dataToUpdate);

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil diperbarui!');
    }

    // Menampilkan foto material
    public function showFoto(SiagaKeluar $siagaKeluar) 
    {
        if (!$siagaKeluar->foto_path || !Storage::disk('public')->exists($siagaKeluar->foto_path)) {
            return abort(404, 'File foto tidak ditemukan.');
        }
        return Storage::disk('public')->response($siagaKeluar->foto_path);
    }
    
    // Download foto material
    public function downloadFoto(SiagaKeluar $siagaKeluar)
    {
        if ($siagaKeluar->foto_path && Storage::disk('public')->exists($siagaKeluar->foto_path)) {
            return Storage::disk('public')->download($siagaKeluar->foto_path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    // TAMBAHAN: Download foto petugas
    public function downloadFotoPetugas(SiagaKeluar $siagaKeluar)
    {
        if ($siagaKeluar->foto_petugas && Storage::disk('public')->exists($siagaKeluar->foto_petugas)) {
            return Storage::disk('public')->download($siagaKeluar->foto_petugas);
        }
        return redirect()->back()->with('error', 'File foto petugas tidak ditemukan.');
    }
    
    public function destroy(SiagaKeluar $siagaKeluar) 
    {
        // Hapus Foto Material
        if ($siagaKeluar->foto_path) {
            Storage::disk('public')->delete($siagaKeluar->foto_path);
        }
        // TAMBAHAN: Hapus Foto Petugas
        if ($siagaKeluar->foto_petugas) {
            Storage::disk('public')->delete($siagaKeluar->foto_petugas);
        }

        $siagaKeluar->delete();

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil dihapus!');
    }
    
    public function downloadReport(Request $request)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        $isPDF = $request->has('submit_pdf');
        $isExcel = $request->has('submit_excel');

        if ($tanggalMulai) {
            $dateStart = Carbon::parse($tanggalMulai)->startOfDay();
        } else {
            $dateStart = Carbon::minValue();
        }

        if ($tanggalAkhir) {
            $dateEnd = Carbon::parse($tanggalAkhir)->endOfDay();
        } else {
            $dateEnd = Carbon::now()->endOfDay();
        }

        $query = SiagaKeluar::with('material')
            ->whereBetween('tanggal', [$dateStart, $dateEnd]) 
            ->orderBy('tanggal', 'asc');
        
        $dataSiagaKeluar = $query->get();

        if ($dataSiagaKeluar->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data ditemukan pada periode tersebut.');
        }

        $tanggal_mulai = $dateStart->format('d M Y');
        $tanggal_akhir = $dateEnd->format('d M Y');

        if ($isPDF) {
            $pdf = Pdf::loadView('siaga-keluar.laporan_pdf', compact('dataSiagaKeluar', 'tanggal_mulai', 'tanggal_akhir'));
            $filename = 'Laporan_Siaga_Keluar_PDF_' . Carbon::now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);

        } elseif ($isExcel) {
            $filename = 'Laporan_Siaga_Keluar_Excel_' . Carbon::now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new SiagaKeluarExport($dateStart, $dateEnd), $filename); 

        } else {
            return redirect()->back()->with('error', 'Pilih format unduhan (PDF atau Excel).');
        }
    }
}