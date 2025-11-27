<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRetur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialReturExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MaterialReturController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialRetur::with('material');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', '%' . $search . '%')
                    ->orWhereHas('material', function($subQ) use ($search) {
                        $subQ->where('nama_material', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        $items = $query->latest('tanggal')->paginate(10); 
        return view('material_retur.index', compact('items'));
    }

    public function create()
    {
        // PERBAIKAN: Menggunakan SORT_NATURAL agar urutan angka benar (1P 1, 1P 2, ... 1P 10)
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_retur.create', compact('materials'));
    }

    public function store(Request $request)
    {
        // Validasi TANPA tanggal, karena tanggal diisi otomatis
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'status' => 'required|in:baik,rusak',
            'keterangan' => 'nullable|string',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            // Menggunakan folder yang spesifik (fotos_material_standby)
            $path = $request->file('foto')->store('fotos_material_retur', 'public');
        }

        // Isi tanggal otomatis dengan waktu sekarang (WITA)
        $dataToSave = array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar')
        ]);

        MaterialRetur::create($dataToSave);

        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil ditambahkan.');
    }

    public function edit(MaterialRetur $materialRetur)
    {
        // PERBAIKAN: Terapkan juga SORT_NATURAL di halaman edit
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_retur.edit', [
            'item' => $materialRetur,
            'materials' => $materials
        ]);
    }

    public function update(Request $request, MaterialRetur $materialRetur)
    {
        // Validasi TANPA tanggal, agar user tidak bisa mengubah tanggal lama
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'status' => 'required|in:baik,rusak',
            'keterangan' => 'nullable|string',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);
        
        // PERBAIKAN: Menggunakan variabel yang benar ($materialRetur)
        $path = $materialRetur->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            // Menggunakan folder yang spesifik (fotos_material_standby)
            $path = $request->file('foto')->store('fotos_material_standby', 'public');
        }

        // Update data TANPA mengubah kolom 'tanggal'
        $materialRetur->update(array_merge($validated, ['foto_path' => $path]));

        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil diperbarui.');
    }

    public function destroy(MaterialRetur $materialRetur)
    {
        if ($materialRetur->foto_path) {
            Storage::disk('public')->delete($materialRetur->foto_path);
        }
        $materialRetur->delete();
        
        return redirect()->route('material-retur.index')
                         ->with('success', 'Data Material Retur berhasil dihapus.');
    }

    /**
     * FUNGSI PERMANEN: Melayani file foto secara langsung melalui Controller.
     * Menggunakan Storage::response() adalah solusi terbaik untuk masalah Symlink.
     */
    public function showFoto(MaterialRetur $materialRetur)
    {
        if (!$materialRetur->foto_path || !Storage::disk('public')->exists($materialRetur->foto_path)) {
            // Jika path kosong atau file tidak ditemukan
            return redirect()->back()->with('error', 'File foto tidak ditemukan untuk ditampilkan.');
        }

        // PERBAIKAN UTAMA: Menggunakan Storage::response()
        return Storage::disk('public')->response($materialRetur->foto_path);
    }

    public function downloadFoto(MaterialRetur $materialRetur)
    {
        if ($materialRetur->foto_path && Storage::disk('public')->exists($materialRetur->foto_path)) {
            return Storage::disk('public')->download($materialRetur->foto_path);
        } else {
            return redirect()->back()->with('error', 'File foto tidak ditemukan.');
        }
    }
    
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_material_retur_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');

        if ($request->has('submit_pdf')) {
            $items = MaterialRetur::with('material')
                                 ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                 ->orderBy('tanggal', 'asc')
                                 ->get();

            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai->format('d M Y'),
                'tanggal_akhir' => $tanggalAkhir->format('d M Y'),
            ];
            
            $pdf = Pdf::loadView('material_retur.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        } 
        
        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialReturExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}