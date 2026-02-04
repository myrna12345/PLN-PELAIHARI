<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKeluar;
use App\Models\MaterialSiagaStandBy; // Pastikan Model ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKeluarExport; 

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
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // ============================================================
        // VALIDASI STOK (Mencegah Input jika Barang Tidak Ada/Terpakai)
        // ============================================================
        
        // 1. Cari data di tabel Stand By berdasarkan Nomor Meter
        $stokTersedia = MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])->first();

        // 2. Jika Nomor Meter TIDAK DITEMUKAN
        if (!$stokTersedia) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nomor_meter' => 'Gagal! Nomor Meter "' . $validated['nomor_meter'] . '" tidak ditemukan di stok Siaga Stand By.']);
        }

        // 3. Jika Barang DITEMUKAN tapi Statusnya BUKAN READY (Misal: Terpakai/Rusak)
        if ($stokTersedia->status !== 'Ready') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nomor_meter' => 'Gagal! Material dengan nomor meter ini statusnya sedang "' . $stokTersedia->status . '", tidak bisa dikeluarkan.']);
        }

        // 4. Validasi Kesesuaian Jenis Material (Opsional tapi disarankan)
        if ($stokTersedia->material_id != $validated['material_id']) {
             return redirect()->back()
                ->withInput()
                ->withErrors(['material_id' => 'Jenis material yang dipilih tidak cocok dengan Nomor Meter tersebut di data stok.']);
        }
        // ============================================================


        // Buat folder jika belum ada
        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        // Upload Foto Material
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($path, $fotoName);

        // Upload Foto Petugas
        $fotoPetugasName = time() . '_petugas_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
        $request->file('foto_petugas')->move($path, $fotoPetugasName);

        $material = Material::findOrFail($validated['material_id']);

        // Simpan Data Siaga Keluar
        SiagaKeluar::create([
            'material_id'           => $validated['material_id'],
            'nomor_meter'           => $validated['nomor_meter'], 
            'nama_material_lengkap' => $material->nama_material, 
            'nama_petugas'          => $validated['nama_petugas'],
            'stand_meter'           => $validated['stand_meter'],
            'keterangan'            => $validated['keterangan'], 
            'status'                => $validated['status'],
            'foto_path'             => $fotoName,
            'foto_petugas'          => $fotoPetugasName, 
            'tanggal'               => Carbon::now('Asia/Makassar'),
        ]);

        // Update status di tabel Standby menjadi Terpakai
        // Kita gunakan object $stokTersedia yang sudah kita temukan di atas
        $stokTersedia->update(['status' => 'Terpakai']);

        return redirect()->route('siaga-keluar.index')
            ->with('success', 'Data Siaga Keluar berhasil disimpan dan stok diperbarui!');
    }


    public function edit(SiagaKeluar $siagaKeluar)
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-keluar.edit', ['item' => $siagaKeluar, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKeluar $siagaKeluar)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500', 
            'status' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $destinationPath = public_path($this->uploadFolder);
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

        if ($request->hasFile('foto')) {
            $oldFile = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
            if ($siagaKeluar->foto_path && File::exists($oldFile)) { File::delete($oldFile); }
            
            $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($destinationPath, $fotoName);
            $dataToUpdate['foto_path'] = $fotoName;
        }

        if ($request->hasFile('foto_petugas')) {
            $oldFilePetugas = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
            if ($siagaKeluar->foto_petugas && File::exists($oldFilePetugas)) { File::delete($oldFilePetugas); }
            
            $fotoPetugasName = time() . '_petugas_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
            $request->file('foto_petugas')->move($destinationPath, $fotoPetugasName);
            $dataToUpdate['foto_petugas'] = $fotoPetugasName;
        }

        $siagaKeluar->update($dataToUpdate);

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil diperbarui!');
    }

    public function destroy(SiagaKeluar $siagaKeluar) 
    {
        // Hapus Foto Material
        if ($siagaKeluar->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }

        // Hapus Foto Petugas
        if ($siagaKeluar->foto_petugas) {
            $pathPetugas = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
            if (File::exists($pathPetugas)) { File::delete($pathPetugas); }
        }
        
        $siagaKeluar->delete();

        return redirect()->route('siaga-keluar.index')->with('success', 'Data Siaga Keluar berhasil dihapus!');
    }
    
    public function downloadFoto(SiagaKeluar $siagaKeluar)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_path);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    public function downloadFotoPetugas(SiagaKeluar $siagaKeluar)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKeluar->foto_petugas);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
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