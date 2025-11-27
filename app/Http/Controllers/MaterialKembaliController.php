<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialKembali;
use App\Exports\MaterialKembaliExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKembaliController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = MaterialKembali::query();

        // PERBAIKAN: Eager load material jika Model MaterialKembali memiliki relasi material()
        // $query->with('material'); 

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
            });
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10);

        return view('material_kembali.index', compact('materialKembali'));
    }

    public function create()
    {
        // PERBAIKAN: Filter agar hanya mengambil material yang BUKAN 'siaga'
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->orderBy('nama_material')
                                 ->get();
                                 
        return view('material_kembali.create', compact('materialList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan!');
    }

    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function edit($id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        
        // PERBAIKAN: Filter agar hanya mengambil material yang BUKAN 'siaga'
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->orderBy('nama_material')
                                 ->get(); 

        return view('material_kembali.edit', compact('materialKembali', 'materialList'));
    }

    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                Storage::disk('public')->delete($materialKembali->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        
        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        $data->delete();

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus!');
    }
    
    /**
     * FUNGSI PERMANEN: Melayani file foto secara langsung melalui Controller (Solusi Anti-Symlink).
     */
    public function showFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);

        // Asumsi kolom foto bernama 'foto'
        if (!$item->foto || !Storage::disk('public')->exists($item->foto)) {
            return abort(404, 'File foto tidak ditemukan untuk ditampilkan.');
        }

        // PERBAIKAN UTAMA: Menggunakan Storage::response()
        return Storage::disk('public')->response($item->foto);
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_kembali_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKembali::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();

        // ✅ PDF
        if ($request->has('submit_pdf')) {
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];

            $pdf = Pdf::loadView('material_kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        // ✅ Excel
        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}