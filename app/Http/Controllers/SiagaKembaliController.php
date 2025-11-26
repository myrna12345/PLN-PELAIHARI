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
                  ->orWhereHas('material', function($subQ) use ($search) {
                      $subQ->where('nama_material', 'like', "%$search%");
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

        return view('siaga-kembali.index', compact('items'));
    }

    public function create()
    {
        // PERBAIKAN: Menggunakan SORT_NATURAL agar urutan angka benar (1P 1, 1P 2, ... 1P 10)
        // Kita ambil dulu semua data ->get(), baru diurutkan ->sortBy()
        $materials = Material::where('kategori', 'siaga')
                        ->get()
                        ->sortBy('nama_material', SORT_NATURAL);

        return view('siaga-kembali.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }

        SiagaKembali::create(array_merge($validated, [
            'foto_path' => $path,
            'tanggal' => Carbon::now('Asia/Makassar'),
            'status' => 'Kembali', 
            'keterangan' => '-' 
        ]));

        return redirect()->route('siaga-kembali.index')
                         ->with('success', 'Data Siaga Kembali berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = SiagaKembali::findOrFail($id);
        
        // PERBAIKAN: Terapkan juga SORT_NATURAL di halaman edit
        $materials = Material::where('kategori', 'siaga')
                        ->get()
                        ->sortBy('nama_material', SORT_NATURAL);

        return view('siaga-kembali.edit', compact('item', 'materials'));
    }

    public function update(Request $request, $id)
    {
        $siagaKembali = SiagaKembali::findOrFail($id);

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_kembali' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $siagaKembali->foto_path;
        if ($request->hasFile('foto')) {
            if ($path) { Storage::disk('public')->delete($path); }
            $path = $request->file('foto')->store('fotos_siaga_kembali', 'public');
        }

        $siagaKembali->update(array_merge($validated, ['foto_path' => $path]));

        return redirect()->route('siaga-kembali.index')
                         ->with('success', 'Data Siaga Kembali berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SiagaKembali::findOrFail($id);
        if ($item->foto_path) {
            Storage::disk('public')->delete($item->foto_path);
        }
        $item->delete();
        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus.');
    }

    public function downloadFoto($id)
    {
        $item = SiagaKembali::findOrFail($id);
        if ($item->foto_path && Storage::disk('public')->exists($item->foto_path)) {
            return Storage::disk('public')->download($item->foto_path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
         $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_siaga_kembali_' . $tanggalMulai->format('Y-m-d') . '_sd_' . $tanggalAkhir->format('Y-m-d');

        if ($request->has('submit_pdf')) {
            $items = SiagaKembali::with('material')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                        ->orderBy('tanggal', 'asc')
                        ->get();

            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai->format('d M Y'),
                'tanggal_akhir' => $tanggalAkhir->format('d M Y'),
            ];
            
            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        } 
        
        if ($request->has('submit_excel')) {
            return Excel::download(new SiagaKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan.');
    }
}