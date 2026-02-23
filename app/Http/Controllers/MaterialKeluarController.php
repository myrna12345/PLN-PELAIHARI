<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialKeluar;
use App\Models\MaterialStandBy;
use App\Exports\MaterialKeluarExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

// Import Standar Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MaterialKeluarController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialKeluar::with('material');

        if ($search) {
            $query->where('nama_petugas', 'like', "%{$search}%")
                  ->orWhereHas('material', function ($q) use ($search) {
                      $q->where('nama_material', 'like', "%{$search}%");
                  });
        }

        if ($tanggalMulai && $tanggalAkhir) {
            $query->whereBetween('tanggal', [
                Carbon::parse($tanggalMulai)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay()
            ]);
        }

        $materialKeluar = $query->orderByDesc('tanggal')->paginate(10);

        $materialKeluar->each(function ($item) {
            $materialStok = MaterialStandBy::where('material_id', $item->material_id)->first();
            $item->stok_saat_ini = $materialStok 
                ? $materialStok->jumlah . ' ' . $materialStok->satuan 
                : '0';
        });

        return view('material_keluar.index', compact('materialKeluar'));
    }

    public function create()
    {
        $materialList = Material::where(function ($query) {
                                    $query->where('kategori', '!=', 'siaga')
                                         ->orWhereNull('kategori');
                                })
                                ->orderBy('nama_material', 'asc')
                                ->get();

        $satuanList = ['Buah', 'Meter'];

        return view('material_keluar.create', compact('materialList', 'satuanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|in:Buah,Meter',
            'keterangan' => 'required|string|max:1000',
            // Batas upload diubah menjadi 15MB (15360 KB)
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif|max:15360',
            'foto_petugas' => 'required|image|mimes:jpg,jpeg,png|max:15360',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');
        
        $manager = new ImageManager(new Driver());
        $path = public_path('uploads/material_keluar');

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        if ($request->hasFile('foto')) {
            $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
            $manager->read($request->file('foto'))->scale(width: 800)->toJpeg(70)->save($path . '/' . $fileName);
            $validated['foto'] = 'uploads/material_keluar/' . $fileName;
        }

        if ($request->hasFile('foto_petugas')) {
            $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
            $manager->read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(70)->save($path . '/' . $fileNamePetugas);
            $validated['foto_petugas'] = 'uploads/material_keluar/' . $fileNamePetugas;
        }

        $materialId = $validated['material_id'];
        $jumlahKeluar = $validated['jumlah_material'];
        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();

        if ($materialStok && $materialStok->jumlah >= $jumlahKeluar) {
            $materialStok->decrement('jumlah', $jumlahKeluar);
            MaterialKeluar::create($validated);

            // LOGIKA PENGALIHAN BARU UNTUK SATPAM
            if (strtolower(auth()->user()->role) === 'satpam') {
                return redirect()->route('dashboard')->with('success', 'Data berhasil disimpan!');
            }

            return redirect()->route('material_keluar.index')->with('success', 'Data berhasil disimpan!');
        }

        return redirect()->back()->with('error', 'Gagal: Stok tidak mencukupi.')->withInput();
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material_keluar.index')->with('error', 'Akses ditolak.');
        }

        $data = MaterialKeluar::findOrFail($id);
        $jumlahLama = $data->jumlah_material;
        $materialIdLama = $data->material_id;

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|in:Buah,Meter',
            'keterangan' => 'required|string|max:1000',
            // Batas upload diubah menjadi 15MB (15360 KB)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:15360',
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png|max:15360',
        ]);

        $manager = new ImageManager(new Driver());
        $path = public_path('uploads/material_keluar');

        $jumlahBaru = $validated['jumlah_material'];
        $materialIdBaru = $validated['material_id'];
        
        if ($materialIdBaru != $materialIdLama) {
            $stokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($stokLama) $stokLama->increment('jumlah', $jumlahLama);
            
            $stokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($stokBaru && $stokBaru->jumlah >= $jumlahBaru) {
                $stokBaru->decrement('jumlah', $jumlahBaru);
            } else {
                return redirect()->back()->with('error', 'Stok tidak mencukupi.');
            }
        } else {
            $selisih = $jumlahBaru - $jumlahLama;
            $stok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($stok && ($selisih < 0 || $stok->jumlah >= $selisih)) {
                $selisih > 0 ? $stok->decrement('jumlah', $selisih) : $stok->increment('jumlah', abs($selisih));
            }
        }

        if ($request->hasFile('foto')) {
            if ($data->foto && File::exists(public_path($data->foto))) File::delete(public_path($data->foto));
            $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
            $manager->read($request->file('foto'))->scale(width: 800)->toJpeg(70)->save($path.'/'.$fileName);
            $validated['foto'] = 'uploads/material_keluar/' . $fileName;
        }

        if ($request->hasFile('foto_petugas')) {
            if ($data->foto_petugas && File::exists(public_path($data->foto_petugas))) File::delete(public_path($data->foto_petugas));
            $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
            $manager->read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(70)->save($path.'/'.$fileNamePetugas);
            $validated['foto_petugas'] = 'uploads/material_keluar/' . $fileNamePetugas;
        }

        $data->update($validated);
        return redirect()->route('material_keluar.index')->with('success', 'Data diperbarui!');
    }

    public function destroy($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material_keluar.index')->with('error', 'Akses ditolak.');
        }

        $data = MaterialKeluar::findOrFail($id);
        $stok = MaterialStandBy::where('material_id', $data->material_id)->first();
        if ($stok) $stok->increment('jumlah', $data->jumlah_material);

        if ($data->foto && File::exists(public_path($data->foto))) File::delete(public_path($data->foto));
        if ($data->foto_petugas && File::exists(public_path($data->foto_petugas))) File::delete(public_path($data->foto_petugas));

        $data->delete();
        return redirect()->route('material_keluar.index')->with('success', 'Data dihapus!');
    }

    public function lihat($id) {
        $item = MaterialKeluar::with('material')->findOrFail($id);
        return view('material_keluar.lihat', compact('item'));
    }

    public function edit($id) {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material_keluar.index')->with('error', 'Akses ditolak.');
        }

        $data = MaterialKeluar::findOrFail($id);
        $materialList = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->orderBy('nama_material', 'asc')->get();
        $satuanList = ['Buah', 'Meter'];
        return view('material_keluar.edit', compact('data', 'materialList', 'satuanList'));
    }

    public function showFoto($id) {
        $item = MaterialKeluar::findOrFail($id);
        $path = public_path($item->foto);
        return File::exists($path) ? response()->file($path) : abort(404);
    }

    public function showFotoPetugas($id) {
        $item = MaterialKeluar::findOrFail($id);
        $path = public_path($item->foto_petugas);
        return File::exists($path) ? response()->file($path) : abort(404);
    }

    public function downloadFoto($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = MaterialKeluar::findOrFail($id);
        $filePath = public_path($item->foto);
        if ($item->foto && File::exists($filePath)) {
            return response()->download($filePath);
        }
        return redirect()->back()->with('error', 'Foto tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = MaterialKeluar::findOrFail($id);
        $filePath = public_path($item->foto_petugas);
        if ($item->foto_petugas && File::exists($filePath)) {
            return response()->download($filePath);
        }
        return redirect()->back()->with('error', 'Foto petugas tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();

        $items = MaterialKeluar::with('material')
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->get();

        $items->each(function ($item) {
            $materialStok = \App\Models\MaterialStandBy::where('material_id', $item->material_id)->first();
            $item->stok_saat_ini = $materialStok 
                ? $materialStok->jumlah . ' ' . $materialStok->satuan 
                : '0';
        });

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_keluar.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'))
                      ->setPaper('a4', 'portrait'); 
            
            return $pdf->download('laporan_keluar.pdf');
        }

        return Excel::download(new MaterialKeluarExport($tanggal_mulai, $tanggal_akhir), 'laporan_keluar.xlsx');
    }
}