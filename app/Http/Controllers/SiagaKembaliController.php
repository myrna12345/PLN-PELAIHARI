<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use App\Models\SiagaKeluar; 
use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKembaliExport; 
use Intervention\Image\Laravel\Facades\Image;

class SiagaKembaliController extends Controller
{
    private $uploadFolder = 'uploads/siaga_kembali';

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
            'material_id'   => 'required|exists:materials,id',
            'nomor_meter'   => 'required|string|max:255',
            'nama_petugas'  => 'required|string|max:255',
            'stand_meter'   => 'required|numeric|min:0', // Validasi angka agar bisa dibandingkan
            'keterangan'    => 'nullable|string',
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        /* ============================================================ */
        /* 🛡️ VALIDASI: CEK KEBERADAAN DI DATA SIAGA KELUAR */
        /* ============================================================ */
        $materialMaster = Material::findOrFail($validated['material_id']);
        
        $dataKeluar = SiagaKeluar::where('nomor_meter', $validated['nomor_meter'])
                                 ->where('nama_material_lengkap', $materialMaster->nama_material)
                                 ->first();

        if (!$dataKeluar) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal: Material "' . $materialMaster->nama_material . '" dengan Nomor Meter "' . $validated['nomor_meter'] . '" belum pernah dikeluarkan atau tidak terdata di Siaga Keluar.');
        }

        // LOGIKA PENAMBAHAN: Stand kembali tidak boleh lebih kecil dari stand saat keluar
        if ($validated['stand_meter'] < $dataKeluar->stand_meter) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal: Stand meter kembali (' . $validated['stand_meter'] . ') tidak boleh lebih kecil dari stand saat keluar (' . $dataKeluar->stand_meter . ').');
        }

        /* =================== SETUP FOLDER & KOMPRESI FOTO =================== */
        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        // 1. Foto Material
        $fotoName = time() . '_mat_' . uniqid() . '.jpg';
        $imageMat = Image::read($request->file('foto'));
        $imageMat->scale(width: 800);
        $imageMat->toJpeg(60)->save($path . '/' . $fotoName);

        // 2. Foto Petugas
        $fotoPetugasName = time() . '_pet_' . uniqid() . '.jpg';
        $imagePet = Image::read($request->file('foto_petugas'));
        $imagePet->scale(width: 800);
        $imagePet->toJpeg(60)->save($path . '/' . $fotoPetugasName);

        /* =================== SIMPAN DATA KEMBALI =================== */
        SiagaKembali::create([
            'material_id'           => $validated['material_id'],
            'nomor_meter'           => $validated['nomor_meter'],
            'nama_material_lengkap' => $materialMaster->nama_material,
            'nama_petugas'          => $validated['nama_petugas'],
            'stand_meter'           => $validated['stand_meter'],
            'status'                => 'Kembali', 
            'keterangan'            => $validated['keterangan'],
            'foto_path'             => $fotoName,
            'foto_petugas'          => $fotoPetugasName,
            'tanggal'               => Carbon::now('Asia/Makassar'),
        ]);

        /* =================== UPDATE DATA MASTER STANDBY =================== */
        // Memperbarui stand_meter master dengan angka terbaru yang diinput
        MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
            ->update([
                'stand_meter' => $validated['stand_meter'],
                'status'      => 'Ready'
            ]);

        return redirect()
            ->route('siaga-kembali.index')
            ->with('success', 'Material berhasil dikembalikan. Stand meter master telah diperbarui ke: ' . $validated['stand_meter']);
    }

    public function edit(SiagaKembali $siagaKembali)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.');
        }

        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|numeric|min:0',
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $destinationPath = public_path($this->uploadFolder);

        if ($request->hasFile('foto')) {
            $oldFile = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
            if ($siagaKembali->foto_path && File::exists($oldFile)) { File::delete($oldFile); }
            
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            Image::read($request->file('foto'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoName);
            $siagaKembali->foto_path = $fotoName;
        }

        if ($request->hasFile('foto_petugas')) {
            $oldFilePetugas = public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas);
            if ($siagaKembali->foto_petugas && File::exists($oldFilePetugas)) { File::delete($oldFilePetugas); }
            
            $fotoPetugasName = time() . '_pet_' . uniqid() . '.jpg';
            Image::read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoPetugasName);
            $siagaKembali->foto_petugas = $fotoPetugasName;
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $siagaKembali->fill([
            'material_id' => $validated['material_id'],
            'nomor_meter' => $validated['nomor_meter'],
            'nama_petugas' => $validated['nama_petugas'],
            'stand_meter' => $validated['stand_meter'],
            'status' => $validated['status'] ?? 'Kembali',
            'keterangan' => $validated['keterangan'],
            'nama_material_lengkap' => $material->nama_material,
        ])->save();

        // Update Master Standby jika stand_meter berubah di edit
        MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
            ->update(['stand_meter' => $validated['stand_meter']]);

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(SiagaKembali $siagaKembali)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.');
        }

        if ($siagaKembali->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }
        
        if ($siagaKembali->foto_petugas) {
            $pathPetugas = public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas);
            if (File::exists($pathPetugas)) { File::delete($pathPetugas); }
        }

        $siagaKembali->delete();
        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    // --- REKONSTRUKSI METODE DOWNLOAD AGAR TIDAK TERPOTONG ---
    
    public function downloadFoto($id)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = SiagaKembali::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        
        if (File::exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = SiagaKembali::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_petugas);
        
        if (File::exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        // Blokir akses Satpam
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = SiagaKembali::with('material')
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->orderBy('tanggal', 'asc')
                    ->get();

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('laporan_siaga_kembali.pdf');
        }
        
        if ($request->has('submit_excel')) {
            return Excel::download(
                new SiagaKembaliExport($tanggal_mulai, $tanggal_akhir), 
                'laporan_siaga_kembali.xlsx'
            );
        }
        
        return back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}