<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKeluar;
use App\Models\MaterialSiagaStandBy; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Log;
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
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360', 
            'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360', 
        ]);

        // AMBIL DATA MATERIAL DARI TABEL MASTER
        $materialMaster = Material::findOrFail($validated['material_id']);

        // --- PERBAIKAN LOGIKA PENCARIAN (TRIM & CASE INSENSITIVE) ---
        $nomorMeterInput = trim($validated['nomor_meter']);
        $namaMaterialMaster = trim($materialMaster->nama_material);

        $stokTersedia = MaterialSiagaStandBy::where('nomor_meter', $nomorMeterInput)
                        ->where('nama_material', $namaMaterialMaster)
                        ->where(function($q) {
                            $q->where('status', 'Ready')
                              ->orWhere('status', 'ready')
                              ->orWhere('status', 'READY');
                        })
                        ->first();

        if (!$stokTersedia) {
            // Debugging tambahan untuk Log jika masih gagal
            Log::warning("Siaga Keluar Gagal: Mencari No Meter [$nomorMeterInput] dengan Material [$namaMaterialMaster]");
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal: Material "' . $namaMaterialMaster . '" dengan Nomor Meter "' . $nomorMeterInput . '" tidak ditemukan atau statusnya sudah "Terpakai" di Siaga Stand By.');
        }

        // VALIDASI KESAMAAN STAND METER
        if (trim($stokTersedia->stand_meter) !== trim($validated['stand_meter'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal: Stand Meter tidak sesuai. Data Standby mencatat Stand Meter: ' . $stokTersedia->stand_meter);
        }

        // PROSES UPLOAD & KOMPRESI
        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        try {
            // KOMPRESI FOTO MATERIAL
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            $imageMat = Image::read($request->file('foto'));
            $imageMat->scale(width: 800);
            $encodedMat = $imageMat->toJpeg(60);
            $encodedMat->save($path . '/' . $fotoName);

            // KOMPRESI FOTO PETUGAS
            $fotoPetugasName = time() . '_petugas_' . uniqid() . '.jpg';
            $imagePtg = Image::read($request->file('foto_petugas'));
            $imagePtg->scale(width: 800);
            $encodedPtg = $imagePtg->toJpeg(60);
            $encodedPtg->save($path . '/' . $fotoPetugasName);

            // SIMPAN DATA
            SiagaKeluar::create([
                'material_id'            => $validated['material_id'],
                'nomor_meter'            => $nomorMeterInput, 
                'nama_material_lengkap'  => $namaMaterialMaster, 
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

            if (strtolower(auth()->user()->role) === 'satpam') {
                return redirect()->route('dashboard')->with('success', 'Data Siaga Keluar berhasil disimpan!');
            }

            return redirect()->route('siaga-keluar.index')
                ->with('success', 'Data Siaga Keluar berhasil disimpan!');

        } catch (\Exception $e) {
            Log::error("Store Siaga Keluar Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses foto.')->withInput();
        }
    }

    public function edit(SiagaKeluar $siagaKeluar)
    {
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360', 
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360', 
        ]);

        $destinationPath = public_path($this->uploadFolder);
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
            'nomor_meter' => trim($validated['nomor_meter']), 
        ];

        try {
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

        } catch (\Exception $e) {
            Log::error("Update Siaga Keluar Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses foto baru.');
        }
    }

    public function destroy(SiagaKeluar $siagaKeluar) 
    {
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
        if (strtolower(auth()->user()->role) === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
        return File::exists($path) ? response()->download($path) : redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    public function downloadFotoPetugas(SiagaKeluar $siagaKeluar)
    {
        if (strtolower(auth()->user()->role) === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
        return File::exists($path) ? response()->download($path) : redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        if (strtolower(auth()->user()->role) === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $request->validate(['tanggal_mulai' => 'required|date', 'tanggal_akhir' => 'required|date']);
        $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $dataSiagaKeluar = SiagaKeluar::with('material')->whereBetween('tanggal', [$dateStart, $dateEnd])->orderBy('tanggal', 'asc')->get();

        if ($dataSiagaKeluar->isEmpty()) { return redirect()->back()->with('error', 'Tidak ada data ditemukan.'); }

        $tanggal_mulai = $dateStart->format('d M Y');
        $tanggal_akhir = $dateEnd->format('d M Y');

        if ($request->has('submit_pdf')) {
            return Pdf::loadView('siaga-keluar.laporan_pdf', compact('dataSiagaKeluar', 'tanggal_mulai', 'tanggal_akhir'))->download('Laporan_Siaga_Keluar.pdf');
        }
        return Excel::download(new SiagaKeluarExport($dateStart, $dateEnd), 'Laporan_Siaga_Keluar.xlsx'); 
    }
}