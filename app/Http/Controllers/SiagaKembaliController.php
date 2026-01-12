<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKembaliExport; 

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
        'stand_meter'   => 'required|string|max:255',
        'keterangan'    => 'nullable|string',
        'foto'          => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
    ]);

    /* =================== SETUP FOLDER =================== */
    $path = public_path($this->uploadFolder);
    if (!File::isDirectory($path)) {
        File::makeDirectory($path, 0777, true, true);
    }

    /* =================== UPLOAD FOTO =================== */
    $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
    $request->file('foto')->move($path, $fotoName);

    $fotoPetugasName = time() . '_pet_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
    $request->file('foto_petugas')->move($path, $fotoPetugasName);

    /* =================== DATA MATERIAL =================== */
    $material = Material::findOrFail($validated['material_id']);

    /* =================== SIMPAN DATA KEMBALI =================== */
    SiagaKembali::create([
        'material_id'            => $validated['material_id'],
        'nomor_meter'            => $validated['nomor_meter'],
        'nama_material_lengkap'  => $material->nama_material,
        'nama_petugas'           => $validated['nama_petugas'],
        'stand_meter'            => $validated['stand_meter'],
        'status'                 => 'Kembali', // ⬅️ STATUS DIKUNCI SISTEM
        'keterangan'             => $validated['keterangan'],
        'foto_path'              => $fotoName,
        'foto_petugas'           => $fotoPetugasName,
        'tanggal'                => Carbon::now('Asia/Makassar'),
    ]);

    /* =================== UPDATE STATUS STANDBY =================== */
    \App\Models\MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
        ->where('stand_meter', $validated['stand_meter'])
        ->update([
            'status' => 'Ready'
        ]);

    return redirect()
        ->route('siaga-kembali.index')
        ->with('success', 'Material berhasil dikembalikan & status Stand By diperbarui');
}


    public function edit(SiagaKembali $siagaKembali)
    {
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P'];
        $materials = Material::where('kategori', 'siaga')
                             ->whereIn('nama_material', $allowedMaterials)
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]);
    }

    public function update(Request $request, SiagaKembali $siagaKembali)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $destinationPath = public_path($this->uploadFolder);

        if ($request->hasFile('foto')) {
            $oldFile = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
            if ($siagaKembali->foto_path && File::exists($oldFile)) { File::delete($oldFile); }
            
            $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($destinationPath, $fotoName);
            $siagaKembali->foto_path = $fotoName;
        }

        if ($request->hasFile('foto_petugas')) {
            $oldFilePetugas = public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas);
            if ($siagaKembali->foto_petugas && File::exists($oldFilePetugas)) { File::delete($oldFilePetugas); }
            
            $fotoPetugasName = time() . '_pet_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
            $request->file('foto_petugas')->move($destinationPath, $fotoPetugasName);
            $siagaKembali->foto_petugas = $fotoPetugasName;
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $siagaKembali->material_id = $validated['material_id'];
        $siagaKembali->nomor_meter = $validated['nomor_meter'];
        $siagaKembali->nama_petugas = $validated['nama_petugas'];
        $siagaKembali->stand_meter = $validated['stand_meter'];
        $siagaKembali->status = $validated['status'];
        $siagaKembali->keterangan = $validated['keterangan'];
        $siagaKembali->nama_material_lengkap = $material->nama_material;
        
        $siagaKembali->save();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(SiagaKembali $siagaKembali)
    {
        // Hapus Foto Material
        if ($siagaKembali->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }
        
        // Hapus Foto Petugas
        if ($siagaKembali->foto_petugas) {
            $pathPetugas = public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas);
            if (File::exists($pathPetugas)) { File::delete($pathPetugas); }
        }

        $siagaKembali->delete();

        return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    public function downloadFoto(SiagaKembali $siagaKembali)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }
    
    public function downloadFotoPetugas(SiagaKembali $siagaKembali)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File foto petugas tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $filename = 'laporan_siaga_kembali_' . $tanggalMulai->format('Y-m-d') . 'sd' . $tanggalAkhir->format('Y-m-d');
        
        if ($request->has('submit_excel')) {
            return Excel::download(new SiagaKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }
        
        if ($request->has('submit_pdf')) {
            $items = SiagaKembali::with('material') 
                                 ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                 ->orderBy('tanggal', 'asc')
                                 ->get();

            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai, 
                'tanggal_akhir' => $tanggalAkhir, 
            ];

            $pdf = Pdf::loadView('siaga-kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }
        
        return redirect()->back()->with('error', 'Pilih jenis laporan yang ingin diunduh.');
    }
}