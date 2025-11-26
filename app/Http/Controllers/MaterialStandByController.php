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
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialStandBy::with('material');

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

        return view('material_stand_by.index', compact('items'));
    }

    public function create()
    {
        // PERBAIKAN: Menggunakan SORT_NATURAL agar urutan angka benar (1P 1, 1P 2, ... 1P 10)
        // Kita ambil dulu semua data ->get(), baru diurutkan ->sortBy()
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('material_stand_by.create', compact('materials'));
    }

    public function store(Request $request)
    {
        // Validasi input tanpa 'tanggal'
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' 
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            // Menggunakan folder yang spesifik (fotos_material_standby)
            $path = $request->file('foto')->store('fotos_material_standby', 'public');
        }

        // Gabungkan data validasi dengan tanggal otomatis
        $dataToSave = array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar')
        ]);

        MaterialStandBy::create($dataToSave);

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil ditambahkan.');
    }

    public function edit(MaterialStandBy $materialStandBy)
    {
        // PERBAIKAN: Terapkan juga SORT_NATURAL di halaman edit
        $materials = Material::where('kategori', '!=', 'siaga')
                             ->orWhereNull('kategori')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_stand_by.edit', [
            'item' => $materialStandBy,
            'materials' => $materials
        ]);
    }

    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        // Validasi input tanpa 'tanggal'
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' 
        ]);

        $path = $materialStandBy->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            // Menggunakan folder yang spesifik (fotos_material_standby)
            $path = $request->file('foto')->store('fotos_material_standby', 'public');
        }

        // Update data tanpa mengubah tanggal lama
        $materialStandBy->update(array_merge($validated, ['foto_path' => $path]));

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data Material Stand By berhasil diperbarui.');
    }

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
     * FUNGSI BARU: Melayani file foto secara langsung melalui Controller (SOLUSI ANTI-SYMLINK).
     * Dipanggil oleh route('material-stand-by.show-foto', $item->id)
     */
    public function showFoto(MaterialStandBy $materialStandBy)
    {
        if (!$materialStandBy->foto_path || !Storage::disk('public')->exists($materialStandBy->foto_path)) {
            // Jika path kosong atau file tidak ditemukan
            return redirect()->back()->with('error', 'File foto tidak ditemukan untuk ditampilkan.');
        }

        // Menggunakan Storage::response() untuk memaksa Laravel melayani file.
        // Ini adalah cara yang paling solid, mengabaikan masalah symlink yang sering terjadi di Windows/Copy-Paste.
        return Storage::disk('public')->response($materialStandBy->foto_path);
    }

    public function downloadFoto(MaterialStandBy $materialStandBy)
    {
        if ($materialStandBy->foto_path && Storage::disk('public')->exists($materialStandBy->foto_path)) {
            return Storage::disk('public')->download($materialStandBy->foto_path);
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
            return Excel::download(new MaterialStandByExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}