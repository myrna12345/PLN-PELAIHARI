<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use App\Models\MaterialHistory;
// use App\Models\MaterialRetur; // Tidak lagi digunakan untuk laporan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialStandByExport;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Laravel\Facades\Image; // WAJIB: Library Kompresi

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

    // ===============================
    // 1. INDEX
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
    // 2. CREATE
    // ===============================
    public function create()
    {
        // FILTER: Hanya ambil material kategori 'teknik' (Bukan Siaga)
        $materials = Material::where('kategori', 'teknik')
                             ->orderBy('nama_material')
                             ->get();
                             
        return view('material_stand_by.create', compact('materials'));
    }

    // ===============================
    // 3. STORE
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string',
            'foto'        => 'required|image|mimes:jpg,jpeg,png|max:10240', // Max 10MB sebelum dikompres
        ]);

        $path = public_path($this->uploadFolder);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // --- LOGIKA BARU: KOMPRESI FOTO ---
        // 1. Paksa nama file berakhiran .jpg
        $fotoName = time() . '_' . uniqid() . '.jpg';
        
        // 2. Baca file -> Resize -> Convert JPG Quality 60 -> Simpan
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
            // Update jumlah dan foto terbaru
            $item->jumlah += $validated['jumlah'];
            $item->foto_path = $fotoName;
            $item->tanggal = now('Asia/Makassar');
            $item->save();
        } else {
            // Buat data baru
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
    // 4. EDIT
    // ===============================
    public function edit($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        
        // FILTER: Hanya ambil material kategori 'teknik' (Bukan Siaga)
        $materials = Material::where('kategori', 'teknik')
                             ->orderBy('nama_material')
                             ->get();

        return view('material_stand_by.edit', compact('item', 'materials'));
    }

    // ===============================
    // 5. UPDATE
    // ===============================
    public function update(Request $request, $id)
    {
        $item = MaterialStandBy::findOrFail($id);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:0',
            'foto'   => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('foto')) {
            $path = public_path($this->uploadFolder);

            // Hapus foto lama
            if ($item->foto_path && File::exists($path . '/' . $item->foto_path)) {
                File::delete($path . '/' . $item->foto_path);
            }

            // --- LOGIKA BARU: KOMPRESI FOTO SAAT UPDATE ---
            $fotoName = time() . '_' . uniqid() . '.jpg';
            
            $image = Image::read($request->file('foto'));
            $image->scale(width: 800);
            $encoded = $image->toJpeg(60);
            $encoded->save($path . '/' . $fotoName);
            // ----------------------------------------------

            $item->foto_path = $fotoName;
        }

        $item->jumlah = $validated['jumlah'];
        $item->tanggal = now('Asia/Makassar'); // Update tanggal ke waktu edit
        $item->save();

        return redirect()->route('material-stand-by.index')
            ->with('success', 'Data berhasil diperbarui');
    }

    // ===============================
    // 6. DELETE
    // ===============================
    public function destroy($id)
    {
        $item = MaterialStandBy::findOrFail($id);

        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        if (File::exists($path)) {
            File::delete($path);
        }

        $item->delete();

        return redirect()->route('material-stand-by.index')
            ->with('success', 'Data berhasil dihapus');
    }

    // ===============================
    // 7. DOWNLOAD PDF (MURNI MATERIAL STAND BY)
    // ===============================
    public function downloadPdf(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $start = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $end   = Carbon::parse($request->tanggal_akhir)->endOfDay();

        // REVISI: Hanya mengambil data dari tabel MaterialStandBy
        // Tidak lagi digabung dengan MaterialRetur sesuai permintaan
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
    // 8. DOWNLOAD EXCEL
    // ===============================
    public function downloadExcel(Request $request)
    {
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