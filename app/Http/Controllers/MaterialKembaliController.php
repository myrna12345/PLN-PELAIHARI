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
use Illuminate\Support\Facades\Log;
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
                                   ->get()
                                   ->sortBy('nama_material', SORT_NATURAL);

        $satuanList = ['Buah', 'Meter'];

        return view('material_kembali.create', compact('materialList', 'satuanList'));
    }

    public function edit($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material_kembali.index')->with('error', 'Akses ditolak.');
        }

        $materialKembali = MaterialKembali::findOrFail($id);
        $materialList = Material::where('kategori', '!=', 'siaga')
                                ->orWhereNull('kategori')
                                ->get()
                                ->sortBy('nama_material', SORT_NATURAL);

        $satuanList = ['Buah', 'Meter'];

        return view('material_kembali.edit', compact('materialKembali', 'materialList', 'satuanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter',
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif,heic,webp|max:15360',
            'foto_petugas' => 'required|image|mimes:jpg,jpeg,png,gif,heic,webp|max:15360',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');
        $manager = new ImageManager(new Driver());
        $uploadPath = public_path('uploads/material_kembali');

        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
        }

        try {
            if ($request->hasFile('foto')) {
                $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
                $manager->read($request->file('foto'))->scale(width: 800)->toJpeg(70)->save($uploadPath . '/' . $fileName); 
                $validated['foto'] = 'uploads/material_kembali/' . $fileName;
            }

            if ($request->hasFile('foto_petugas')) {
                $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
                $manager->read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(70)->save($uploadPath . '/' . $fileNamePetugas);
                $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileNamePetugas;
            }

            $materialId = $validated['material_id'];
            $jumlahKembali = $validated['jumlah_material'];
            $totalKeluar = \App\Models\MaterialKeluar::where('material_id', $materialId)->sum('jumlah_material');
            $totalSudahKembali = MaterialKembali::where('material_id', $materialId)->sum('jumlah_material');
            $maksimalKembali = $totalKeluar - $totalSudahKembali;

            if ($totalKeluar <= 0) {
                return redirect()->back()->with('error', 'Gagal: Material ini belum pernah tercatat pada material keluar.')->withInput();
            }

            if ($jumlahKembali > $maksimalKembali) {
                return redirect()->back()->with('error', 'Gagal: Jumlah kembali melebihi sisa lapangan. Maksimal: ' . $maksimalKembali)->withInput();
            }

            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
            if ($materialStok) {
                $materialStok->increment('jumlah', $jumlahKembali);
            } else {
                return redirect()->back()->with('error', 'Stok Material Stand By tidak ditemukan.')->withInput();
            }

            MaterialKembali::create($validated);

            if (strtolower(auth()->user()->role) === 'satpam') {
                return redirect()->route('dashboard')->with('success', 'Data berhasil disimpan');
            }
            return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan');

        } catch (\Exception $e) {
            Log::error("Store Material Kembali Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses foto.')->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material_kembali.index')->with('error', 'Akses ditolak.');
        }

        $materialKembali = MaterialKembali::findOrFail($id);
        $jumlahLama = $materialKembali->jumlah_material;
        $materialIdLama = $materialKembali->material_id;

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan' => 'required|string|in:Buah,Meter',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif,heic,webp|max:15360',
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png,gif,heic,webp|max:15360',
        ]);

        $manager = new ImageManager(new Driver());
        $uploadPath = public_path('uploads/material_kembali');

        $materialIdBaru = $validated['material_id'];
        $jumlahBaru = $validated['jumlah_material'];

        // Logika Sinkronisasi Stok
        if ($materialIdBaru != $materialIdLama) {
            $stokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($stokLama) {
                if ($stokLama->jumlah < $jumlahLama) return redirect()->back()->with('error', 'Stok tidak cukup untuk membatalkan data lama.')->withInput();
                $stokLama->decrement('jumlah', $jumlahLama);
            }
            $stokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($stokBaru) $stokBaru->increment('jumlah', $jumlahBaru);
        } else {
            $selisih = $jumlahBaru - $jumlahLama;
            $stok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($stok) {
                if ($selisih < 0 && $stok->jumlah < abs($selisih)) return redirect()->back()->with('error', 'Stok tidak mencukupi.')->withInput();
                $selisih > 0 ? $stok->increment('jumlah', $selisih) : $stok->decrement('jumlah', abs($selisih));
            }
        }

        try {
            if ($request->hasFile('foto')) {
                if ($materialKembali->foto && File::exists(public_path($materialKembali->foto))) File::delete(public_path($materialKembali->foto));
                $fileName = time() . '_foto_' . $request->file('foto')->getClientOriginalName();
                $manager->read($request->file('foto'))->scale(width: 800)->toJpeg(70)->save($uploadPath . '/' . $fileName);
                $validated['foto'] = 'uploads/material_kembali/' . $fileName;
            }

            if ($request->hasFile('foto_petugas')) {
                if ($materialKembali->foto_petugas && File::exists(public_path($materialKembali->foto_petugas))) File::delete(public_path($materialKembali->foto_petugas));
                $fileNamePetugas = time() . '_petugas_' . $request->file('foto_petugas')->getClientOriginalName();
                $manager->read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(70)->save($uploadPath . '/' . $fileNamePetugas);
                $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileNamePetugas;
            }

            $materialKembali->update($validated);
            return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error("Update Material Kembali Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses foto baru.');
        }
    }

    public function destroy($id)
    {
        if (auth()->user()->role === 'satpam') return redirect()->route('material_kembali.index')->with('error', 'Akses ditolak.');
        $data = MaterialKembali::findOrFail($id);
        $stok = MaterialStandBy::where('material_id', $data->material_id)->first();
        if ($stok) $stok->decrement('jumlah', $data->jumlah_material);
        if ($data->foto && File::exists(public_path($data->foto))) File::delete(public_path($data->foto));
        if ($data->foto_petugas && File::exists(public_path($data->foto_petugas))) File::delete(public_path($data->foto_petugas));
        $data->delete();
        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    public function lihat($id) {
        $item = MaterialKembali::findOrFail($id);
        $item->nama_material = $this->getMaterialNameById($item->material_id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function showFoto($id) {
        $item = MaterialKembali::findOrFail($id);
        $path = public_path($item->foto);
        return File::exists($path) ? response()->file($path) : abort(404);
    }

    public function showFotoPetugas($id) {
        $item = MaterialKembali::findOrFail($id);
        $path = public_path($item->foto_petugas);
        return File::exists($path) ? response()->file($path) : abort(404);
    }

    public function downloadFoto($id) {
        if (auth()->user()->role === 'satpam') return redirect()->back()->with('error', 'Akses ditolak.');
        $item = MaterialKembali::findOrFail($id);
        $path = public_path($item->foto);
        return File::exists($path) ? response()->download($path) : redirect()->back()->with('error', 'Foto tidak ditemukan.');
    }

    public function downloadFotoPetugas($id) {
        if (auth()->user()->role === 'satpam') return redirect()->back()->with('error', 'Akses ditolak.');
        $item = MaterialKembali::findOrFail($id);
        $path = public_path($item->foto_petugas);
        return File::exists($path) ? response()->download($path) : redirect()->back()->with('error', 'Foto petugas tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        if (auth()->user()->role === 'satpam') return redirect()->back()->with('error', 'Akses ditolak.');
        $request->validate(['tanggal_mulai' => 'required|date', 'tanggal_akhir' => 'required|date']);
        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $items = MaterialKembali::with('material')->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])->get();
        if ($request->has('submit_pdf')) {
            return Pdf::loadView('material_kembali.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'))->setPaper('a4', 'portrait')->download('laporan_kembali.pdf');
        }
        return Excel::download(new MaterialKembaliExport($tanggal_mulai, $tanggal_akhir), 'laporan_kembali.xlsx');
    }
}