<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRetur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Gunakan File Facade
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialReturExport;

class MaterialReturController extends Controller
{
    // Folder tujuan penyimpanan di Public
    private $uploadFolder = 'uploads/material_retur';

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialRetur::with('material');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', '%' . $search . '%')
                  ->orWhereHas('material', function($subQ) use ($search) {
                        $subQ->where('nama_material', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10); 
        return view('material_retur.index', compact('items'));
    }

    public function create()
    {
        $materials = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->get()->sortBy('nama_material', SORT_NATURAL);
        return view('material_retur.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Buah,Meter',
            'status' => 'required|in:bekas_andal,rusak,baik', 
            'keterangan' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        // Buat folder jika belum ada
        $path = public_path($this->uploadFolder);
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }

        // Upload Foto Material ke Public
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($path, $fotoName);

        // Upload Foto Petugas ke Public
        $fotoPetugasName = time() . '_ptg_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
        $request->file('foto_petugas')->move($path, $fotoPetugasName);

        MaterialRetur::create([
            'material_id' => $validated['material_id'],
            'nama_petugas' => $validated['nama_petugas'],
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'foto_path' => $fotoName,
            'foto_petugas' => $fotoPetugasName,
            'tanggal' => Carbon::now('Asia/Makassar')
        ]);

        return redirect()->route('material-retur.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $materials = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->get()->sortBy('nama_material', SORT_NATURAL);
        return view('material_retur.edit', compact('item', 'materials'));
    }

    public function update(Request $request, $id)
    {
        $materialRetur = MaterialRetur::findOrFail($id);
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Buah,Meter',
            'status' => 'required|in:bekas_andal,rusak,baik', 
            'keterangan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $data = [
            'material_id' => $validated['material_id'],
            'nama_petugas' => $validated['nama_petugas'],
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
        ];

        $destinationPath = public_path($this->uploadFolder);

        // Update Foto Material
        if ($request->hasFile('foto')) {
            // Hapus lama
            $oldFile = public_path($this->uploadFolder . '/' . $materialRetur->foto_path);
            if (File::exists($oldFile)) { File::delete($oldFile); }
            
            // Upload baru
            $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($destinationPath, $fotoName);
            $data['foto_path'] = $fotoName;
        }

        // Update Foto Petugas
        if ($request->hasFile('foto_petugas')) {
            // Hapus lama
            $oldFile = public_path($this->uploadFolder . '/' . $materialRetur->foto_petugas);
            if (File::exists($oldFile)) { File::delete($oldFile); }
            
            // Upload baru
            $fotoPetugasName = time() . '_ptg_' . uniqid() . '.' . $request->file('foto_petugas')->getClientOriginalExtension();
            $request->file('foto_petugas')->move($destinationPath, $fotoPetugasName);
            $data['foto_petugas'] = $fotoPetugasName;
        }

        $materialRetur->update($data); 
        return redirect()->route('material-retur.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = MaterialRetur::findOrFail($id);
        
        // Hapus fisik file di public
        if ($item->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $item->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }
        if ($item->foto_petugas) {
            $path = public_path($this->uploadFolder . '/' . $item->foto_petugas);
            if (File::exists($path)) { File::delete($path); }
        }
        
        $item->delete();
        return redirect()->route('material-retur.index')->with('success', 'Data berhasil dihapus.');
    }

    // --- FITUR DOWNLOAD REPORT ---
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = MaterialRetur::with('material')
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->orderBy('tanggal', 'asc')
                    ->get();

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_retur.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('laporan_material_retur.pdf');
        }
        
        if ($request->has('submit_excel')) {
            return Excel::download(
                new MaterialReturExport($tanggal_mulai, $tanggal_akhir), 
                'laporan_material_retur.xlsx'
            );
        }
    }

    public function downloadFoto($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        
        if (File::exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_petugas);
        
        if (File::exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    // Opsional: Untuk kompatibilitas route, tapi di view sebaiknya pakai asset()
    public function showFoto($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        if (File::exists($path)) {
            return response()->file($path);
        }
        return abort(404);
    }
}