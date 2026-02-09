<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialKembali;
use App\Models\MaterialStandBy;
use App\Exports\MaterialKembaliExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

// Import untuk Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MaterialKembaliController extends Controller
{
    private function getMaterialNameById($materialId)
    {
        $material = Material::find($materialId);
        return $material ? $material->nama_material : 'Material Tidak Ditemukan';
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialKembali::with('material');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_petugas', 'like', "%{$search}%")
                  ->orWhereHas('material', function ($m) use ($search) {
                      $m->where('nama_material', 'like', "%{$search}%");
                  });
            });
        }

        if ($tanggalMulai && $tanggalAkhir) {
            $query->whereBetween('tanggal', [
                $tanggalMulai . ' 00:00:00',
                $tanggalAkhir . ' 23:59:59'
            ]);
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10);

        $materialKembali->each(function ($item) {
            $materialStok = MaterialStandBy::where('material_id', $item->material_id)->first();
            $item->stok_saat_ini = $materialStok
                ? $materialStok->jumlah . ' ' . $materialStok->satuan
                : '0';
        });

        return view('material_kembali.index', compact('materialKembali'));
    }

    public function create()
    {
        $materialList = Material::where('kategori', '!=', 'siaga')
                                   ->orWhereNull('kategori')
                                   ->orderBy('nama_material')
                                   ->get();
        $satuanList = ['Buah', 'Meter'];

        return view('material_kembali.create', compact('materialList', 'satuanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter',
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        // Inisialisasi Image Manager v3
        $manager = new ImageManager(new Driver());

        $uploadPath = public_path('uploads/material_kembali');
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
        }

        // --- Simpan & Kompres Foto Material ---
        if ($request->hasFile('foto')) {
            $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
            $image = $manager->read($request->file('foto'));
            $image->scale(width: 800); // Resize lebar ke 800px (proporsional)
            $image->toJpeg(70)->save($uploadPath . '/' . $fileName); // Simpan kualitas 70%
            
            $validated['foto'] = 'uploads/material_kembali/' . $fileName;
        }

        // --- Simpan & Kompres Foto Petugas ---
        if ($request->hasFile('foto_petugas')) {
            $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
            $imagePetugas = $manager->read($request->file('foto_petugas'));
            $imagePetugas->scale(width: 800);
            $imagePetugas->toJpeg(70)->save($uploadPath . '/' . $fileNamePetugas);
            
            $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileNamePetugas;
        }

        $materialId = $validated['material_id'];
        $jumlahKembali = $validated['jumlah_material'];
        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();

        if ($materialStok) {
            $materialStok->increment('jumlah', $jumlahKembali);
        } else {
            return redirect()->back()->with('error', 'Gagal: Stok Material Stand By belum tercatat.')->withInput();
        }

        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan! Foto telah dikompres.');
    }

    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        $jumlahLama = $materialKembali->jumlah_material;
        $materialIdLama = $materialKembali->material_id;

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $manager = new ImageManager(new Driver());
        $uploadPath = public_path('uploads/material_kembali');

        // Logika penyesuaian stok (Tetap sama)
        $materialIdBaru = $validated['material_id'];
        $jumlahBaru = $validated['jumlah_material'];

        if ($materialIdBaru == $materialIdLama) {
            $stokSelisih = $jumlahBaru - $jumlahLama;
            if ($stokSelisih !== 0) {
                $materialStok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
                if ($materialStok) {
                    $stokSelisih > 0 ? $materialStok->increment('jumlah', $stokSelisih) : $materialStok->decrement('jumlah', abs($stokSelisih));
                }
            }
        } else {
            $materialStokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($materialStokLama) $materialStokLama->decrement('jumlah', $jumlahLama);

            $materialStokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($materialStokBaru) {
                $materialStokBaru->increment('jumlah', $jumlahBaru);
            }
        }

        // --- Update & Kompres Foto Material ---
        if ($request->hasFile('foto')) {
            if ($materialKembali->foto && File::exists(public_path($materialKembali->foto))) {
                File::delete(public_path($materialKembali->foto));
            }
            $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
            $image = $manager->read($request->file('foto'));
            $image->scale(width: 800);
            $image->toJpeg(70)->save($uploadPath . '/' . $fileName);
            $validated['foto'] = 'uploads/material_kembali/' . $fileName;
        }

        // --- Update & Kompres Foto Petugas ---
        if ($request->hasFile('foto_petugas')) {
            if ($materialKembali->foto_petugas && File::exists(public_path($materialKembali->foto_petugas))) {
                File::delete(public_path($materialKembali->foto_petugas));
            }
            $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
            $imagePetugas = $manager->read($request->file('foto_petugas'));
            $imagePetugas->scale(width: 800);
            $imagePetugas->toJpeg(70)->save($uploadPath . '/' . $fileNamePetugas);
            $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileNamePetugas;
        }

        $materialKembali->update($validated);
        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        $materialStok = MaterialStandBy::where('material_id', $data->material_id)->first();
        if ($materialStok) $materialStok->decrement('jumlah', $data->jumlah_material);

        if ($data->foto && File::exists(public_path($data->foto))) File::delete(public_path($data->foto));
        if ($data->foto_petugas && File::exists(public_path($data->foto_petugas))) File::delete(public_path($data->foto_petugas));

        $data->delete();
        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    // --- Fungsi Pendukung Lainnya (Sama Seperti Sebelumnya) ---

    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $item->nama_material = $this->getMaterialNameById($item->material_id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function showFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto);
        if (!$item->foto || !File::exists($filePath)) abort(404);
        return response()->file($filePath);
    }

    public function showFotoPetugas($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto_petugas);
        if (!$item->foto_petugas || !File::exists($filePath)) abort(404);
        return response()->file($filePath);
    }

    public function downloadFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto);
        if ($item->foto && File::exists($filePath)) return response()->download($filePath);
        return redirect()->back()->with('error', 'Foto tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto_petugas);
        if ($item->foto_petugas && File::exists($filePath)) return response()->download($filePath);
        return redirect()->back()->with('error', 'Foto petugas tidak ditemukan.');
    }

    public function downloadReport(Request $request)
{
    $request->validate([
        'tanggal_mulai' => 'required|date',
        'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
    ]);

    $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
    $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
    $filename = 'laporan_material_kembali_' . $tanggal_mulai->format('Ymd') . '_sd_' . $tanggal_akhir->format('Ymd');

    $items = MaterialKembali::with('material')
        ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
        ->orderBy('tanggal', 'asc')
        ->get();

    // --- TAMBAHKAN LOGIKA STOK DI SINI ---
    $items->each(function ($item) {
        $materialStok = \App\Models\MaterialStandBy::where('material_id', $item->material_id)->first();
        $item->stok_saat_ini = $materialStok 
            ? $materialStok->jumlah . ' ' . $materialStok->satuan 
            : '0';
    });
    // -------------------------------------

    if ($request->has('submit_pdf')) {
        $pdf = Pdf::loadView('material_kembali.laporan_pdf', [
            'items' => $items, 
            'tanggal_mulai' => $tanggal_mulai, 
            'tanggal_akhir' => $tanggal_akhir
        ])->setPaper('a4', 'portrait'); // Paksa portrait agar rapi
        
        return $pdf->download($filename . '.pdf');
    }

    if ($request->has('submit_excel')) {
        return Excel::download(new MaterialKembaliExport($tanggal_mulai, $tanggal_akhir), $filename . '.xlsx');
    }

    return redirect()->back()->with('error', 'Terjadi kesalahan.');
}
}