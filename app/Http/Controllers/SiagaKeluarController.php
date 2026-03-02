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
use Intervention\Image\Laravel\Facades\Image;

class SiagaKeluarController extends Controller
{
    private $uploadFolder = 'uploads/siaga_keluar';

    public function index(Request $request)
    {
        // [PROTEKSI HARMET] 
        if (strtolower(auth()->user()->role) === 'harmet') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak diperbolehkan melihat laporan.');
        }

        // [PROTEKSI SATPAM] Jika Satpam coba akses laporan, lempar ke halaman input (create)
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('siaga-keluar.create');
        }

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
        
        if ($tanggalMulai) { 
            $query->whereDate('tanggal', '>=', $tanggalMulai); 
        }
        if ($tanggalAkhir) { 
            $query->whereDate('tanggal', '<=', $tanggalAkhir); 
        }

        $dataSiagaKeluar = $query->latest('tanggal')->paginate(10);
        
        return view('siaga-keluar.index', compact('dataSiagaKeluar'));
    }

    public function create()
    {
        // Izinkan Satpam akses Create
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

        $materialMaster = Material::findOrFail($validated['material_id']);
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
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data tidak ditemukan di Siaga Stand By.');
        }

        if (trim($stokTersedia->stand_meter) !== trim($validated['stand_meter'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Stand Meter tidak sesuai.');
        }

        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        try {
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            Image::read($request->file('foto'))->scale(width: 800)->toJpeg(60)->save($path . '/' . $fotoName);

            $fotoPetugasName = time() . '_petugas_' . uniqid() . '.jpg';
            Image::read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(60)->save($path . '/' . $fotoPetugasName);

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

            $stokTersedia->update(['status' => 'Terpakai']);

            // [PROTEKSI HARMET & SATPAM] Redirect ke Dashboard
            if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
                return redirect()->route('dashboard')->with('success', 'Data Siaga Keluar berhasil disimpan!');
            }

            return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil disimpan!');

        } catch (\Exception $e) {
            Log::error("Store Siaga Keluar Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses foto.')->withInput();
        }
    }

    public function edit(SiagaKeluar $siagaKeluar)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
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
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
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
                if ($siagaKeluar->foto_path && File::exists(public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path))) {
                    File::delete(public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path));
                }
                $fotoName = time() . '_mat_' . uniqid() . '.jpg';
                Image::read($request->file('foto'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoName);
                $dataToUpdate['foto_path'] = $fotoName;
            }

            if ($request->hasFile('foto_petugas')) {
                if ($siagaKeluar->foto_petugas && File::exists(public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas))) {
                    File::delete(public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas));
                }
                $fotoPetugasName = time() . '_petugas_' . uniqid() . '.jpg';
                Image::read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoPetugasName);
                $dataToUpdate['foto_petugas'] = $fotoPetugasName;
            }

            $siagaKeluar->update($dataToUpdate);
            return redirect()->route('siaga-keluar.index')->with('success', 'Data berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses foto baru.');
        }
    }

    public function destroy(SiagaKeluar $siagaKeluar) 
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
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
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    public function downloadFotoPetugas(SiagaKeluar $siagaKeluar)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $dataSiagaKeluar = SiagaKeluar::with('material')->whereBetween('tanggal', [$dateStart, $dateEnd])->orderBy('tanggal', 'asc')->get();

        if ($dataSiagaKeluar->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data ditemukan.');
        }

        $tanggal_mulai = $dateStart->format('d M Y');
        $tanggal_akhir = $dateEnd->format('d M Y');

        if ($request->has('submit_pdf')) {
            return Pdf::loadView('siaga-keluar.laporan_pdf', compact('dataSiagaKeluar', 'tanggal_mulai', 'tanggal_akhir'))->download('Laporan_Siaga_Keluar.pdf');
        }
        return Excel::download(new SiagaKeluarExport($dateStart, $dateEnd), 'Laporan_Siaga_Keluar.xlsx'); 
    }

    public function showFoto(SiagaKeluar $siagaKeluar)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
        if (File::exists($path)) {
            return response()->file($path);
        }
        abort(404);
    }
}