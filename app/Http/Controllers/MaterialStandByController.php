<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use App\Models\MaterialHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialStandByExport;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Laravel\Facades\Image; 

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

    // ===============================
    // 1. INDEX (SEMUA BISA AKSES)
    // ===============================
    public function index(Request $request)
    {
        $search = $request->search;
        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;

        $query = MaterialStandBy::with('material');

        if ($search) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%");
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

    // ===============================
    // 2. CREATE (SEMUA BISA AKSES)
    // ===============================
    public function create()
    {
        // PERBAIKAN: Menggunakan sortBy dengan SORT_NATURAL agar urutan angka (Ampere) benar
        $materials = Material::where('kategori', 'teknik')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('material_stand_by.create', compact('materials'));
    }

    // ===============================
    // 3. STORE (SEMUA BISA AKSES - Redirect ke Index)
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string',
            // Batas upload diubah menjadi 15MB (15360 KB)
            'foto'        => 'required|image|mimes:jpg,jpeg,png|max:15360',
        ]);

        $path = public_path($this->uploadFolder);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // --- KOMPRESI FOTO ---
        $fotoName = time() . '_' . uniqid() . '.jpg';
        
        $image = Image::read($request->file('foto'));
        $image->scale(width: 800);
        $encoded = $image->toJpeg(60);
        $encoded->save($path . '/' . $fotoName);
        // ----------------------------------

        $material = Material::findOrFail($validated['material_id']);

        // Cek apakah material sudah ada (Merge Data)
        $item = MaterialStandBy::where('material_id', $validated['material_id'])
            ->where('satuan', $validated['satuan'])
            ->first();

        if ($item) {
            $item->jumlah += $validated['jumlah'];
            $item->foto_path = $fotoName;
            $item->tanggal = now('Asia/Makassar');
            $item->save();
        } else {
            MaterialStandBy::create([
                'material_id' => $validated['material_id'],
                'nama_material_lengkap' => $material->nama_material,
                'jumlah' => $validated['jumlah'],
                'satuan' => $validated['satuan'],
                'foto_path' => $fotoName,
                'tanggal' => now('Asia/Makassar'),
            ]);
        }

        // Simpan History
        MaterialHistory::create([
            'nama_material' => $material->nama_material,
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'foto_path' => $fotoName,
            'tanggal_input' => now('Asia/Makassar'),
        ]);

        return redirect()->route('material-stand-by.index')
            ->with('success', 'Data berhasil disimpan');
    }

    // ===============================
    // 4. EDIT (DILARANG UNTUK SATPAM)
    // ===============================
    public function edit($id)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')
                ->with('error', 'Akses Ditolak. Satpam tidak boleh mengedit data.');
        }

        $item = MaterialStandBy::findOrFail($id);
        
        // PERBAIKAN: Menggunakan sortBy dengan SORT_NATURAL agar urutan angka (Ampere) benar
        $materials = Material::where('kategori', 'teknik')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_stand_by.edit', compact('item', 'materials'));
    }

    // ===============================
    // 5. UPDATE (DILARANG UNTUK SATPAM)
    // ===============================
    public function update(Request $request, $id)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')
                ->with('error', 'Akses Ditolak. Satpam tidak boleh mengubah data.');
        }

        $item = MaterialStandBy::findOrFail($id);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:0',
            // Batas upload diubah menjadi 15MB (15360 KB)
            'foto'   => 'nullable|image|mimes:jpg,jpeg,png|max:15360',
        ]);

        if ($request->hasFile('foto')) {
            $path = public_path($this->uploadFolder);

            // Hapus foto lama
            if ($item->foto_path && File::exists($path . '/' . $item->foto_path)) {
                File::delete($path . '/' . $item->foto_path);
            }

            // --- KOMPRESI FOTO ---
            $fotoName = time() . '_' . uniqid() . '.jpg';
            
            $image = Image::read($request->file('foto'));
            $image->scale(width: 800);
            $encoded = $image->toJpeg(60);
            $encoded->save($path . '/' . $fotoName);
            // ---------------------

            $item->foto_path = $fotoName;
        }

        $item->jumlah = $validated['jumlah'];
        $item->tanggal = now('Asia/Makassar'); 
        $item->save();

        return redirect()->route('material-stand-by.index')
            ->with('success', 'Data berhasil diperbarui');
    }

    // ===============================
    // 6. DELETE (DILARANG UNTUK SATPAM)
    // ===============================
    public function destroy($id)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')
                ->with('error', 'Akses Ditolak. Satpam tidak boleh menghapus data.');
        }

        // Ambil data stand by beserta relasi materialnya
        $item = MaterialStandBy::with('material')->findOrFail($id);

        // Ambil nama material untuk kunci penghapusan riwayat
        $namaMaterial = $item->nama_material_lengkap ?? ($item->material->nama_material ?? null);

        // --- LOGIKA PENGHAPUSAN HISTORY TOTAL ---
        if ($namaMaterial) {
            MaterialHistory::where('nama_material', $namaMaterial)
                ->where('satuan', $item->satuan)
                ->delete();
        }
        // ------------------------------------------

        // Hapus file fisik foto terakhir
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        if ($item->foto_path && File::exists($path)) {
            File::delete($path);
        }

        // Hapus data utama di tabel stand by
        $item->delete();

        return redirect()->route('material-stand-by.index')
            ->with('success', 'Data Laporan dan seluruh Riwayat terkait material tersebut berhasil dihapus.');
    }

    // ===============================
    // 7. DOWNLOAD PDF (DILARANG UNTUK SATPAM)
    // ===============================
    public function downloadPdf(Request $request)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()
                ->with('error', 'Akses Ditolak. Satpam tidak memiliki izin download PDF.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $start = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $end   = Carbon::parse($request->tanggal_akhir)->endOfDay();

        $items = MaterialStandBy::with('material')
            ->whereBetween('tanggal', [$start, $end])
            ->latest('tanggal')
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan pada periode tersebut.');
        }

        $tanggal_mulai = $start->format('d M Y');
        $tanggal_akhir = $end->format('d M Y');

        $pdf = Pdf::loadView(
            'material_stand_by.laporan_pdf',
            compact('items', 'tanggal_mulai', 'tanggal_akhir')
        );

        return $pdf->download('Laporan_Material_Standby.pdf');
    }

    // ===============================
    // 8. DOWNLOAD EXCEL (DILARANG UNTUK SATPAM)
    // ===============================
    public function downloadExcel(Request $request)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()
                ->with('error', 'Akses Ditolak. Satpam tidak memiliki izin download Excel.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        return Excel::download(
            new MaterialStandByExport(
                $request->tanggal_mulai,
                $request->tanggal_akhir
            ),
            'Laporan_Material_Standby.xlsx'
        );
    }

    public function showFoto($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        return view('material_stand_by.show_foto', compact('item'));
    }

    public function downloadFoto($id)
    {
        // [SECURITY CHECK]
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()
                ->with('error', 'Akses Ditolak. Satpam tidak boleh mendownload file asli.');
        }

        $item = MaterialStandBy::findOrFail($id);

        if (!$item->foto_path) {
            abort(404, 'Foto tidak ditemukan');
        }

        $path = public_path('uploads/material_stand_by/' . $item->foto_path);

        if (!file_exists($path)) {
            abort(404, 'File foto tidak ada di server');
        }

        return response()->download($path);
    }
}