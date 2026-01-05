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

        // Simpan foto ke public/uploads/material_kembali
        $uploadPath = public_path('uploads/material_kembali');
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
        }

        if ($request->hasFile('foto')) {
            $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move($uploadPath, $fileName);
            $validated['foto'] = 'uploads/material_kembali/' . $fileName;
        }

        if ($request->hasFile('foto_petugas')) {
            $fileName = time() . '_' . $request->file('foto_petugas')->getClientOriginalName();
            $request->file('foto_petugas')->move($uploadPath, $fileName);
            $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileName;
        }

        $materialId = $validated['material_id'];
        $jumlahKembali = $validated['jumlah_material'];

        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();

        if ($materialStok) {
            $materialStok->increment('jumlah', $jumlahKembali);
        } else {
            return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item ini belum tercatat.')->withInput();
        }

        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan! Stok Stand By bertambah.');
    }

    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $item->nama_material = $this->getMaterialNameById($item->material_id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function edit($id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        $materialList = Material::where('kategori', '!=', 'siaga')
                                   ->orWhereNull('kategori')
                                   ->orderBy('nama_material')
                                   ->get();
        $satuanList = ['Buah', 'Meter'];

        return view('material_kembali.edit', compact('materialKembali', 'materialList', 'satuanList'));
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
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $materialIdBaru = $validated['material_id'];
        $jumlahBaru = $validated['jumlah_material'];

        // Logika penyesuaian stok
        if ($materialIdBaru == $materialIdLama) {
            $stokSelisih = $jumlahBaru - $jumlahLama;
            if ($stokSelisih !== 0) {
                $materialStok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
                if ($materialStok) {
                    if ($stokSelisih > 0) {
                        $materialStok->increment('jumlah', $stokSelisih);
                    } else {
                        $materialStok->decrement('jumlah', abs($stokSelisih));
                    }
                } else {
                    return redirect()->back()->with('error', 'Gagal: Stok Material Stand By belum tercatat.')->withInput();
                }
            }
        } else {
            $materialStokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($materialStokLama) {
                $materialStokLama->decrement('jumlah', $jumlahLama);
            }

            $materialStokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($materialStokBaru) {
                $materialStokBaru->increment('jumlah', $jumlahBaru);
            } else {
                if ($materialStokLama) {
                    $materialStokLama->increment('jumlah', $jumlahLama);
                }
                return redirect()->back()->with('error', 'Gagal: Stok Material Stand By untuk item baru belum tercatat.')->withInput();
            }
        }

        // Update foto
        $uploadPath = public_path('uploads/material_kembali');
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
        }

        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                $oldPath = public_path($materialKembali->foto);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move($uploadPath, $fileName);
            $validated['foto'] = 'uploads/material_kembali/' . $fileName;
        }

        if ($request->hasFile('foto_petugas')) {
            if ($materialKembali->foto_petugas) {
                $oldPath = public_path($materialKembali->foto_petugas);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $fileName = time() . '_' . $request->file('foto_petugas')->getClientOriginalName();
            $request->file('foto_petugas')->move($uploadPath, $fileName);
            $validated['foto_petugas'] = 'uploads/material_kembali/' . $fileName;
        }

        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui! Stok Stand By disesuaikan.');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);

        $materialId = $data->material_id;
        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();

        if ($materialStok) {
            $materialStok->decrement('jumlah', $data->jumlah_material);
        }

        if ($data->foto) {
            $path = public_path($data->foto);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        if ($data->foto_petugas) {
            $path = public_path($data->foto_petugas);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $data->delete();

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus! Stok Stand By dikurangi kembali.');
    }

    // Fungsi tampilkan foto langsung dari public/
    public function showFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto);

        if (!$item->foto || !File::exists($filePath)) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath);
        return response()->file($filePath, ['Content-Type' => $mimeType]);
    }

    public function showFotoPetugas($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto_petugas);

        if (!$item->foto_petugas || !File::exists($filePath)) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath);
        return response()->file($filePath, ['Content-Type' => $mimeType]);
    }

    public function downloadFoto($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto);

        if ($item->foto && File::exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->back()->with('error', 'Foto tidak ditemukan.');
    }

    public function downloadFotoPetugas($id)
    {
        $item = MaterialKembali::findOrFail($id);
        $filePath = public_path($item->foto_petugas);

        if ($item->foto_petugas && File::exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->back()->with('error', 'Foto petugas tidak ditemukan.');
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_kembali_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKembali::with('material')
                                ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                ->orderBy('tanggal', 'asc')
                                ->get();

        $items->each(function ($item) {
            $materialStok = MaterialStandBy::where('material_id', $item->material_id)->first();
            $item->stok_saat_ini = $materialStok
                ? $materialStok->jumlah . ' ' . $materialStok->satuan
                : '0';
        });

        if ($request->has('submit_pdf')) {
            $data = [
                'items' => $items,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ];

            $pdf = Pdf::loadView('material_kembali.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialKembaliExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}