<?php

namespace App\Http\Controllers;
use App\Models\Material;
use App\Models\MaterialKeluar;
use App\Exports\MaterialKeluarExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKeluarController extends Controller
{
    // INDEX: Tampilkan data + fitur pencarian
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialKeluar::query();

        // Filter pencarian nama material / petugas
        if ($search) {
            // PERBAIKAN: Eager load material (asumsi relasi sudah ada)
            $query->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
        }


        // Urutkan dari terbaru dan paginate
        $materialKeluar = $query->orderByDesc('tanggal')->paginate(10);

        return view('material_keluar.index', compact('materialKeluar'));
    }

    // CREATE
    public function create()
    {
        // Ambil material yang kategorinya BUKAN 'siaga' (bisa 'teknik' atau null)
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->get();
                                 
        return view('material_keluar.create', compact('materialList'));
    }


    // 💾 STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        // Tambahkan waktu saat ini otomatis
        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        MaterialKeluar::create($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan!');
    }
    
    // lihat show
    public function lihat($id)
    {
        $item = MaterialKeluar::findOrFail($id);
        return view('material_keluar.lihat', compact('item'));
    }

    // ✏️ EDIT
    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        
        // Sama seperti create, filter agar material siaga tidak muncul
        $materialList = Material::where('kategori', '!=', 'siaga')
                                 ->orWhereNull('kategori')
                                 ->get();

        return view('material_keluar.edit', compact('data', 'materialList'));
    }

    // 🔁 UPDATE
    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            // PERBAIKAN: Menaikkan batas dari 2048 KB menjadi 5120 KB (5 MB)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_keluar', 'public');
        }

        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui!');
    }

    // 🗑 DELETE
    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }
        $data->delete();

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus!');
    }
    
    /**
     * FUNGSI PERMANEN: Melayani file foto secara langsung melalui Controller (Solusi Anti-Symlink).
     */
    public function showFoto($id)
    {
        $item = MaterialKeluar::findOrFail($id);
        
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
        $filename = 'laporan_material_keluar_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKeluar::whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                               ->orderBy('tanggal', 'asc')
                               ->get();

        if ($request->has('submit_pdf')) {
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];
            $pdf = Pdf::loadView('material_keluar.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialKeluarExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}