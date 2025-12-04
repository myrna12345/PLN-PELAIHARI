<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialStandByExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MaterialStandByController extends Controller
{
    /**
     * Menampilkan daftar Material Stand By.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        // Menggunakan model MaterialStandBy
        $query = MaterialStandBy::with('material');

        if ($search) {
            // PERBAIKAN: Hanya mencari berdasarkan 'nama_material' dan kolom yang relevan
            $query->where(function($q) use ($search) {
                // Mencari di tabel Material yang berelasi
                $q->orWhereHas('material', function($subQ) use ($search) {
                    $subQ->where('nama_material', 'like', '%' . $search . '%');
                });
                // Mencari berdasarkan Satuan
                $q->orWhere('satuan', 'like', '%' . $search . '%');
                // Mencari berdasarkan Nama Petugas (jika ada data lama)
                $q->orWhere('nama_petugas', 'like', '%' . $search . '%');
            });
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }

        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        $items = $query->latest('tanggal')->paginate(10); 

        return view('material_stand_by.index', compact('items'));
    }

    /**
     * Menampilkan form untuk membuat Material Stand By baru.
     */
    public function create()
    {
        // Ambil dan urutkan material yang bukan kategori 'siaga'
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('material_stand_by.create', compact('materials'));
    }

    /**
     * Menyimpan Material Stand By baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            // HAPUS VALIDASI INI: 'nomor_unit' => 'required|string|max:255', 
            // HAPUS VALIDASI INI: 'stand_meter' => 'required|string|max:255', 
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|in:Buah,Meter',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' 
        ]);

        // Hapus 'foto' dari array validated
        $foto_file = $validated['foto'];
        unset($validated['foto']);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $foto_file->store('fotos_material_standby', 'public');
        }

        // Jika kolom `nama_petugas` di database adalah NOT NULL, Anda harus memberikan nilai default.
        $dataToSave = array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
            // Berikan nilai default untuk field yang dihapus dari form
            'nama_petugas' => 'System', 
            'nomor_unit' => null, 
            'stand_meter' => null, 
        ]);

        MaterialStandBy::create($dataToSave);

        return redirect()->route('material-stand-by.index')
                             ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit Material Stand By.
     */
    public function edit(MaterialStandBy $materialStandBy)
    {
        // Ambil dan urutkan material yang bukan kategori 'siaga'
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_stand_by.edit', [
            'item' => $materialStandBy,
            'materials' => $materials
        ]);
    }

    /**
     * Memperbarui Material Stand By di database.
     */
    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        // Validasi input: 'foto' dibuat nullable agar tidak wajib saat update
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            // HAPUS VALIDASI INI: 'nama_petugas' => 'required|string|max:255',
            // HAPUS VALIDASI INI: 'nomor_unit' => 'required|string|max:255', 
            // HAPUS VALIDASI INI: 'stand_meter' => 'required|string|max:255', 
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|in:Buah,Meter', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' 
        ]);

        $path = $materialStandBy->foto_path;
        
        // Logika penanganan file foto
        if ($request->hasFile('foto')) {
            if ($path) { 
                Storage::disk('public')->delete($path); 
            }
            $path = $request->file('foto')->store('fotos_material_standby', 'public');
        }

        // Hapus 'foto' dari array validated 
        unset($validated['foto']); 
        
        // Tambahkan field yang TIDAK ADA DI FORM EDIT (yaitu: nama_petugas, nomor_unit, stand_meter)
        // Nilai lama akan dipertahankan.
        $dataToUpdate = array_merge($validated, [
            'foto_path' => $path,
            'nama_petugas' => $materialStandBy->nama_petugas, // Pertahankan nilai lama
            'nomor_unit' => $materialStandBy->nomor_unit,       // Pertahankan nilai lama
            'stand_meter' => $materialStandBy->stand_meter,     // Pertahankan nilai lama
        ]);

        // Update data
        $materialStandBy->update($dataToUpdate);

        return redirect()->route('material-stand-by.index')
                             ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }
    
    /**
     * Menghapus Material Stand By dari database.
     */
    public function destroy(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path) {
            Storage::disk('public')->delete($materialStandBy->foto_path);
        }
        $materialStandBy->delete();
        return redirect()->route('material-stand-by.index')
                             ->with('success', 'Data Material Stand By berhasil dihapus.');
    }

    /**
     * Menampilkan foto.
     */
    public function showFoto(MaterialStandBy $materialStandBy)
    {
        if (!$materialStandBy->foto_path || !Storage::disk('public')->exists($materialStandBy->foto_path)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        return Storage::disk('public')->response($materialStandBy->foto_path);
    }

    /**
     * Mengunduh foto.
     */
    public function downloadFoto(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path && Storage::disk('public')->exists($materialStandBy->foto_path)) {
            return Storage::disk('public')->download($materialStandBy->foto_path);
        } else {
            return redirect()->back()->with('error', 'File foto tidak ditemukan.');
        }
    }
    
    /**
     * Mengunduh laporan dalam format PDF atau Excel.
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_material_stand_by_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');

        if ($request->has('submit_pdf')) {
            $items = MaterialStandBy::with('material')
                                       ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                       ->orderBy('tanggal', 'asc')
                                       ->get();

            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai->format('d M Y'),
                'tanggal_akhir' => $tanggalAkhir->format('d M Y'),
            ];
            
            $pdf = Pdf::loadView('material_stand_by.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        } 
        
        if ($request->has('submit_excel')) {
            return Excel::download(new \App\Exports\MaterialStandByExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}