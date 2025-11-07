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
     * READ: Menampilkan halaman daftar (laporan) dengan SEARCH.
     */
    public function index(Request $request)
    {
        // Ambil input dari URL
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        // Mulai query
        $query = MaterialStandBy::with('material');

        // Tambahkan filter NAMA (jika ada)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', '%' . $search . '%')
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', '%' . $search . '%');
                  });
            });
        }

        // Tambahkan filter TANGGAL MULAI (jika ada)
        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai); // whereDate mengabaikan jam
        }

        // Tambahkan filter TANGGAL AKHIR (jika ada)
        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir); // whereDate mengabaikan jam
        }

        // Ambil data, urutkan, dan paginasi
        $items = $query->latest('tanggal')->paginate(10); 

        // Kirim data ke view
        return view('material_stand_by.index', compact('items'));
    }

    /**
     * CREATE (Form): Menampilkan form tambah data.
     */
    public function create()
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.create', compact('materials'));
    }

    /**
     * CREATE (Action): Menyimpan data baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos', 'public');
        }
        MaterialStandBy::create($validated + ['foto_path' => $path]);
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    /**
     * SHOW: Menampilkan detail data (Read-Only).
     */
    public function show(MaterialStandBy $materialStandBy)
    {
        return view('material_stand_by.show', [
            'item' => $materialStandBy
        ]);
    }

    /**
     * UPDATE (Form): Menampilkan form untuk edit data.
     */
    public function edit(MaterialStandBy $materialStandBy)
    {
        $materials = Material::orderBy('nama_material')->get();
        return view('material_stand_by.edit', [
            'item' => $materialStandBy,
            'materials' => $materials
        ]);
    }

    /**
     * UPDATE (Action): Memperbarui data di database.
     */
    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        $path = $materialStandBy->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos', 'public');
        }
        $materialStandBy->update($validated + ['foto_path' => $path]);
        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }

    /**
     * DELETE: Menghapus data dari database.
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
     * --- FUNGSI UNTUK DOWNLOAD FOTO ---
     */
    public function downloadFoto(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path && Storage::disk('public')->exists($materialStandBy->foto_path)) {
            return Storage::disk('public')->download($materialStandBy->foto_path);
        } else {
            return redirect()->route('material-stand-by.index')
                             ->with('error', 'File foto tidak ditemukan.');
        }
    }
    
    /**
     * --- FUNGSI UNTUK DOWNLOAD PDF & EXCEL ---
     */
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;
        
        // --- Perbaikan Bug Tanggal Selesai ---
        $tanggalMulaiCarbon = Carbon::parse($tanggalMulai)->startOfDay();
        $tanggalAkhirCarbon = Carbon::parse($tanggalAkhir)->endOfDay();
        
        $filename = 'laporan_material_stand_by_' . $tanggalMulai . '_sd_' . $tanggalAkhir;

        if ($request->has('submit_pdf')) {
            $items = MaterialStandBy::with('material')
                        ->whereBetween('tanggal', [$tanggalMulaiCarbon, $tanggalAkhirCarbon])
                        ->orderBy('tanggal', 'asc')
                        ->get();
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];
            $pdf = PDF::loadView('material_stand_by.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        } 
        
        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialStandByExport($tanggalMulaiCarbon->toDateTimeString(), $tanggalAkhirCarbon->toDateTimeString()), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}