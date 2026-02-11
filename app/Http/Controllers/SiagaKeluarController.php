<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKeluar;
use App\Models\MaterialSiagaStandBy; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKeluarExport; 
use Intervention\Image\Laravel\Facades\Image; // WAJIB: Library Kompresi

class SiagaKeluarController extends Controller
{
    private $uploadFolder = 'uploads/siaga_keluar';

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = SiagaKeluar::with('material'); 
        
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

        $dataSiagaKeluar = $query->latest('tanggal')->paginate(10);
        
        return view('siaga-keluar.index', compact('dataSiagaKeluar'));
    }

    public function create()
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
        
        return view('siaga-keluar.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'   => 'required|exists:materials,id',
            'nomor_meter'   => 'required|string|max:255', 
            'nama_petugas'  => 'required|string|max:255',
            'stand_meter'   => 'required|string|max:255',
            'keterangan'    => 'required|string|max:500', 
            'status'        => 'required|string|max:255', 
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
        ]);

        // AMBIL DATA MATERIAL DARI TABEL MASTER
        $materialMaster = Material::findOrFail($validated['material_id']);

        // CEK STOK DI SIAGA STANDBY
        $stokTersedia = MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
                        ->where('nama_material', $materialMaster->nama_material)
                        ->where('status', 'Ready')
                        ->first();

        if (!$stokTersedia) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal: Material "' . $materialMaster->nama_material . '" dengan Nomor Meter "' . $validated['nomor_meter'] . '" tidak ditemukan di Siaga Stand By.');
        }

        // PROSES UPLOAD & KOMPRESI
        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        // --- 1. KOMPRESI FOTO MATERIAL ---
        $fotoName = time() . '_mat_' . uniqid() . '.jpg'; // Paksa .jpg
        $imageMat = Image::read($request->file('foto'));
        $imageMat->scale(width: 800); // Resize lebar max 800px
        $encodedMat = $imageMat->toJpeg(60); // Convert JPG Quality 60
        $encodedMat->save($path . '/' . $fotoName);

        // --- 2. KOMPRESI FOTO PETUGAS ---
        $fotoPetugasName = time() . '_petugas_' . uniqid() . '.jpg'; // Paksa .jpg
        $imagePtg = Image::read($request->file('foto_petugas'));
        $imagePtg->scale(width: 800); // Resize lebar max 800px
        $encodedPtg = $imagePtg->toJpeg(60); // Convert JPG Quality 60
        $encodedPtg->save($path . '/' . $fotoPetugasName);

        // SIMPAN DATA
        SiagaKeluar::create([
            'material_id'            => $validated['material_id'],
            'nomor_meter'            => $validated['nomor_meter'], 
            'nama_material_lengkap'  => $materialMaster->nama_material, 
            'nama_petugas'           => $validated['nama_petugas'],
            'stand_meter'            => $validated['stand_meter'],
            'keterangan'             => $validated['keterangan'], 
            'status'                 => $validated['status'],
            'foto_path'              => $fotoName,
            'foto_petugas'           => $fotoPetugasName, 
            'tanggal'                => Carbon::now('Asia/Makassar'),
        ]);

        // UPDATE STATUS STOK
        $stokTersedia->update(['status' => 'Terpakai']);

        return redirect()->route('siaga-keluar.index')
            ->with('success', 'Data Siaga Keluar berhasil disimpan!');
    }

    public function edit(SiagaKeluar $siagaKeluar)
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('siaga-keluar.index')->with('error', 'Akses ditolak.');
        }

        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-keluar.edit', ['item' => $siagaKeluar, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKeluar $siagaKeluar)
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('siaga-keluar.index')->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500', 
            'status' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', 
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', 
        ]);

        $destinationPath = public_path($this->uploadFolder);
        // Buat folder jika belum ada (safety check)
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $dataToUpdate = [
            'material_id' => $validated['material_id'],
            'nama_material_lengkap' => $material->nama_material,
            'nama_petugas' => $validated['nama_petugas'],
            'stand_meter' => $validated['stand_meter'],
            'keterangan' => $validated['keterangan'],
            'status' => $validated['status'],
            'nomor_meter' => $validated['nomor_meter'], 
        ];

        // --- UPDATE FOTO MATERIAL (KOMPRESI) ---
        if ($request->hasFile('foto')) {
            $oldFile = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
            if ($siagaKeluar->foto_path && File::exists($oldFile)) { File::delete($oldFile); }
            
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            
            $imageMat = Image::read($request->file('foto'));
            $imageMat->scale(width: 800);
            $encodedMat = $imageMat->toJpeg(60);
            $encodedMat->save($destinationPath . '/' . $fotoName);
            
            $dataToUpdate['foto_path'] = $fotoName;
        }

        // --- UPDATE FOTO PETUGAS (KOMPRESI) ---
        if ($request->hasFile('foto_petugas')) {
            $oldFilePetugas = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
            if ($siagaKeluar->foto_petugas && File::exists($oldFilePetugas)) { File::delete($oldFilePetugas); }
            
            $fotoPetugasName = time() . '_petugas_' . uniqid() . '.jpg';
            
            $imagePtg = Image::read($request->file('foto_petugas'));
            $imagePtg->scale(width: 800);
            $encodedPtg = $imagePtg->toJpeg(60);
            $encodedPtg->save($destinationPath . '/' . $fotoPetugasName);
            
            $dataToUpdate['foto_petugas'] = $fotoPetugasName;
        }

        $siagaKeluar->update($dataToUpdate);

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil diperbarui!');
    }

    public function destroy(SiagaKeluar $siagaKeluar) 
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('siaga-keluar.index')->with('error', 'Akses ditolak.');
        }

        if ($siagaKeluar->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }

        if ($siagaKeluar->foto_petugas) {
            $pathPetugas = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
            if (File::exists($pathPetugas)) { File::delete($pathPetugas); }
        }
        
        $siagaKeluar->delete();

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil dihapus!');
    }
    
    public function downloadFoto(SiagaKeluar $siagaKeluar)
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
        if (File::exists($path)) { return response()->download($path); }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    public function downloadFotoPetugas(SiagaKeluar $siagaKeluar)
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
        if (File::exists($path)) { return response()->download($path); }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        // Proteksi Satpam
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();

        $dataSiagaKeluar = SiagaKeluar::with('material')
            ->whereBetween('tanggal', [$dateStart, $dateEnd]) 
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($dataSiagaKeluar->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data ditemukan pada periode tersebut.');
        }

        $tanggal_mulai = $dateStart->format('d M Y');
        $tanggal_akhir = $dateEnd->format('d M Y');

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('siaga-keluar.laporan_pdf', compact('dataSiagaKeluar', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('Laporan_Siaga_Keluar.pdf');
        } elseif ($request->has('submit_excel')) {
            return Excel::download(new SiagaKeluarExport($dateStart, $dateEnd), 'Laporan_Siaga_Keluar.xlsx'); 
        }
    }
}