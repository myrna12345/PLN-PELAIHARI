<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKembaliExport; 

class SiagaKembaliController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = SiagaKembali::with('material'); 
        
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

        $items = $query->latest('tanggal')->paginate(10);
        
        return view('siaga-kembali.index', compact('items'));
    }

    public function create()
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];

        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        
        return view('siaga-kembali.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // TAMBAHAN VALIDASI
        ]);

        // Upload Foto Material
        $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');

        // TAMBAHAN: Upload Foto Petugas
        $pathPetugas = $request->file('foto_petugas')->store('fotos_siaga_kembali', 'public');
        
        $material = Material::findOrFail($validated['material_id']);

        $dataToSave = [
            'material_id' => $validated['material_id'],
            'nomor_meter' => $validated['nomor_meter'], 
            'nama_material_lengkap' => $material->nama_material,
            'nama_petugas' => $validated['nama_petugas'],
            'stand_meter' => $validated['stand_meter'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
            'foto_path' => $path,
            'foto_petugas' => $pathPetugas, // TAMBAHAN SIMPAN PATH
            'tanggal' => Carbon::now('Asia/Makassar'),
        ];
        
        SiagaKembali::create($dataToSave);

        // Update status di tabel Standby kembali menjadi Ready
        \App\Models\MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
            ->update(['status' => 'Ready']);

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil disimpan dan status Standby kini Ready!');
    }

    public function edit(SiagaKembali $siagaKembali)
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // TAMBAHAN VALIDASI
        ]);

        // Update Foto Material
        $path = $siagaKembali->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }

        // TAMBAHAN: Update Foto Petugas
        $pathPetugas = $siagaKembali->foto_petugas;
        if ($request->hasFile('foto_petugas')) {
            if ($pathPetugas) { Storage::disk('public')->delete($pathPetugas); }
            $pathPetugas = $request->file('foto_petugas')->store('fotos_siaga_kembali', 'public');
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $dataToUpdate = $validated;
        $dataToUpdate['foto_path'] = $path;
        $dataToUpdate['foto_petugas'] = $pathPetugas; // TAMBAHAN UPDATE PATH
        $dataToUpdate['nama_material_lengkap'] = $material->nama_material;
        
        $siagaKembali->update($dataToUpdate);

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(SiagaKembali $siagaKembali)
    {
        // Hapus Foto Material
        if ($siagaKembali->foto_path) {
            Storage::disk('public')->delete($siagaKembali->foto_path);
        }
        // TAMBAHAN: Hapus Foto Petugas
        if ($siagaKembali->foto_petugas) {
            Storage::disk('public')->delete($siagaKembali->foto_petugas);
        }

        $siagaKembali->delete();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    // --- FOTO MATERIAL ---
    public function showFoto(SiagaKembali $siagaKembali) 
    {
        if (!$siagaKembali->foto_path || !Storage::disk('public')->exists($siagaKembali->foto_path)) {
            return abort(404, 'File foto tidak ditemukan.');
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

    // --- TAMBAHAN: FOTO PETUGAS ---
    public function downloadFotoPetugas(SiagaKembali $siagaKembali)
    {
        if ($siagaKembali->foto_petugas && Storage::disk('public')->exists($siagaKembali->foto_petugas)) {
            return Storage::disk('public')->download($siagaKembali->foto_petugas);
        }
        return redirect()->back()->with('error', 'File foto petugas tidak ditemukan.');
    }
    
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