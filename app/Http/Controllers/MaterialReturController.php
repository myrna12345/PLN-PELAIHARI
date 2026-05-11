<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRetur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialReturExport;
use Intervention\Image\Laravel\Facades\Image;

class MaterialReturController extends Controller
{
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
            // Mendukung format modern heic dan webp
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360', 
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360' 
        ]);

        try {
            $path = public_path($this->uploadFolder);
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0755, true, true);
            }

            // --- KOMPRESI FOTO MATERIAL ---
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            $imageMat = Image::read($request->file('foto'));
            $imageMat->scale(width: 800);
            $encodedMat = $imageMat->toJpeg(60);
            $encodedMat->save($path . '/' . $fotoName);

            // --- KOMPRESI FOTO PETUGAS ---
            $fotoPetugasName = time() . '_ptg_' . uniqid() . '.jpg';
            $imagePtg = Image::read($request->file('foto_petugas'));
            $imagePtg->scale(width: 800);
            $encodedPtg = $imagePtg->toJpeg(60);
            $encodedPtg->save($path . '/' . $fotoPetugasName);

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

            if (strtolower(auth()->user()->role) === 'satpam') {
                return redirect()->route('dashboard')->with('success', 'Data berhasil disimpan!');
            }

            return redirect()->route('material-retur.index')->with('success', 'Data berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error("Store Material Retur Error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.')->withInput();
        }
    }

    public function edit($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-retur.index')->with('error', 'Akses ditolak.');
        }

        $item = MaterialRetur::findOrFail($id);
        $materials = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->get()->sortBy('nama_material', SORT_NATURAL);
        return view('material_retur.edit', compact('item', 'materials'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-retur.index')->with('error', 'Akses ditolak.');
        }

        $materialRetur = MaterialRetur::findOrFail($id);
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Buah,Meter',
            'status' => 'required|in:bekas_andal,rusak,baik', 
            'keterangan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360'
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
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        try {
            if ($request->hasFile('foto')) {
                $oldFile = public_path($this->uploadFolder . '/' . $materialRetur->foto_path);
                if ($materialRetur->foto_path && File::exists($oldFile)) { File::delete($oldFile); }
                
                $fotoName = time() . '_mat_' . uniqid() . '.jpg';
                $imageMat = Image::read($request->file('foto'));
                $imageMat->scale(width: 800);
                $encodedMat = $imageMat->toJpeg(60);
                $encodedMat->save($destinationPath . '/' . $fotoName);
                $data['foto_path'] = $fotoName;
            }

            if ($request->hasFile('foto_petugas')) {
                $oldFile = public_path($this->uploadFolder . '/' . $materialRetur->foto_petugas);
                if ($materialRetur->foto_petugas && File::exists($oldFile)) { File::delete($oldFile); }
                
                $fotoPetugasName = time() . '_ptg_' . uniqid() . '.jpg';
                $imagePtg = Image::read($request->file('foto_petugas'));
                $imagePtg->scale(width: 800);
                $encodedPtg = $imagePtg->toJpeg(60);
                $encodedPtg->save($destinationPath . '/' . $fotoPetugasName);
                $data['foto_petugas'] = $fotoPetugasName;
            }

            $materialRetur->update($data); 
            return redirect()->route('material-retur.index')->with('success', 'Data berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error("Update Material Retur Error: " . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui foto baru.');
        }
    }

    public function destroy($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-retur.index')->with('error', 'Akses ditolak.');
        }

        $item = MaterialRetur::findOrFail($id);
        
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

    public function downloadReport(Request $request)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $request->validate(['tanggal_mulai' => 'required|date', 'tanggal_akhir' => 'required|date']);
        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = MaterialRetur::with('material')->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])->orderBy('tanggal', 'asc')->get();

        if ($request->has('submit_pdf')) {
            foreach ($items as $item) {
                if ($item->foto_path && File::exists(public_path($this->uploadFolder . '/' . $item->foto_path))) {
                    $path = public_path($this->uploadFolder . '/' . $item->foto_path);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($path);
                    $item->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                }
                if ($item->foto_petugas && File::exists(public_path($this->uploadFolder . '/' . $item->foto_petugas))) {
                    $path = public_path($this->uploadFolder . '/' . $item->foto_petugas);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($path);
                    $item->foto_petugas_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                }
            }
            $pdf = Pdf::loadView('material_retur.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('laporan_material_retur.pdf');
        }
        
        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialReturExport($tanggal_mulai, $tanggal_akhir), 'laporan_material_retur.xlsx');
        }
    }

    public function downloadFoto($id)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        return File::exists($path) ? response()->download($path) : back()->with('error', 'File tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses ditolak.'); }
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_petugas);
        return File::exists($path) ? response()->download($path) : back()->with('error', 'File tidak ditemukan.');
    }

    public function showFoto($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        return File::exists($path) ? response()->file($path) : abort(404);
    }
}