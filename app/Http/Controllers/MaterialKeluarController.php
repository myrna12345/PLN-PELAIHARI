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

class MaterialKeluarController extends Controller
{
    /**
     * Pastikan Anda telah menambahkan relasi berikut di model MaterialKeluar:
     * public function material()
     * {
     *     return $this->belongsTo(Material::class, 'material_id');
     * }
     */

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
        $materialList = Material::where('kategori', '!=', 'siaga')
                                ->orWhereNull('kategori')
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
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        // Upload foto material ke folder public/uploads/material_keluar/
        if ($request->hasFile('foto')) {
            $path = public_path('uploads/material_keluar');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move($path, $fileName);
            $validated['foto'] = 'uploads/material_keluar/' . $fileName;
        }

        // Upload foto petugas
        if ($request->hasFile('foto_petugas')) {
            $path = public_path('uploads/material_keluar');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $fileName = time() . '_' . $request->file('foto_petugas')->getClientOriginalName();
            $request->file('foto_petugas')->move($path, $fileName);
            $validated['foto_petugas'] = 'uploads/material_keluar/' . $fileName;
        }

        $materialId = $validated['material_id'];
        $jumlahKeluar = $validated['jumlah_material'];
        $materialSource = Material::find($materialId);

        if ($materialSource) {
            $materialStok = MaterialStandBy::where('material_id', $materialId)->first();

            if ($materialStok) {
                if ($materialStok->jumlah >= $jumlahKeluar) {
                    $materialStok->decrement('jumlah', $jumlahKeluar);
                    MaterialKeluar::create($validated);
                    return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil disimpan dan stok Stand By berhasil dikurangi!');
                } else {
                    return redirect()->back()->with('error', 'Gagal: Jumlah material keluar melebihi stok yang tersedia di Material Stand By (Stok tersedia: ' . $materialStok->jumlah . ' ' . $materialStok->satuan . ')')->withInput();
                }
            } else {
                return redirect()->back()->with('error', 'Gagal: Material ini tidak memiliki stok awal yang tercatat di Material Stand By.')->withInput();
            }
        }

        return redirect()->back()->with('error', 'Gagal: Material ID tidak ditemukan dalam daftar Material utama.')->withInput();
    }

    public function lihat($id)
    {
        $item = MaterialKeluar::with('material')->findOrFail($id);
        return view('material_keluar.lihat', compact('item'));
    }

    public function edit($id)
    {
        $data = MaterialKeluar::findOrFail($id);
        $materialList = Material::where('kategori', '!=', 'siaga')
                                ->orWhereNull('kategori')
                                ->get();
        $satuanList = ['Buah', 'Meter'];
        return view('material_keluar.edit', compact('data', 'materialList', 'satuanList'));
    }

    public function update(Request $request, $id)
    {
        $data = MaterialKeluar::findOrFail($id);
        $jumlahLama = $data->jumlah_material;

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'satuan_material' => 'required|string|in:Buah,Meter',
            'keterangan' => 'required|string|max:1000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $jumlahBaru = $validated['jumlah_material'];
        $materialIdBaru = $validated['material_id'];
        $materialIdLama = $data->material_id;
        $stokSelisih = $jumlahBaru - $jumlahLama;

        if ($materialIdBaru != $materialIdLama) {
            $materialStokLama = MaterialStandBy::where('material_id', $materialIdLama)->first();
            if ($materialStokLama) {
                $materialStokLama->increment('jumlah', $jumlahLama);
            }

            $materialStokBaru = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($materialStokBaru) {
                if ($materialStokBaru->jumlah >= $jumlahBaru) {
                    $materialStokBaru->decrement('jumlah', $jumlahBaru);
                } else {
                    if ($materialStokLama) {
                        $materialStokLama->decrement('jumlah', $jumlahLama);
                    }
                    return redirect()->back()->with('error', 'Gagal update: Jumlah material baru melebihi stok yang tersedia (Tersedia: ' . $materialStokBaru->jumlah . ' ' . $materialStokBaru->satuan . ')')->withInput();
                }
            } else {
                if ($materialStokLama) {
                    $materialStokLama->decrement('jumlah', $jumlahLama);
                }
                return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By baru tidak ditemukan.')->withInput();
            }
        } elseif ($stokSelisih !== 0) {
            $materialStok = MaterialStandBy::where('material_id', $materialIdBaru)->first();
            if ($materialStok) {
                if ($stokSelisih > 0) {
                    if ($materialStok->jumlah >= $stokSelisih) {
                        $materialStok->decrement('jumlah', $stokSelisih);
                    } else {
                        return redirect()->back()->with('error', 'Gagal update: Penambahan pengeluaran melebihi stok yang tersedia (Tersedia: ' . $materialStok->jumlah . ' ' . $materialStok->satuan . ')')->withInput();
                    }
                } else {
                    $materialStok->increment('jumlah', abs($stokSelisih));
                }
            } else {
                return redirect()->back()->with('error', 'Gagal update: Stok Material Stand By tidak ditemukan.')->withInput();
            }
        }

        // Upload ulang foto jika diunggah
        if ($request->hasFile('foto')) {
            if ($data->foto) {
                $oldPath = public_path($data->foto);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $path = public_path('uploads/material_keluar');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move($path, $fileName);
            $validated['foto'] = 'uploads/material_keluar/' . $fileName;
        }

        if ($request->hasFile('foto_petugas')) {
            if ($data->foto_petugas) {
                $oldPath = public_path($data->foto_petugas);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $path = public_path('uploads/material_keluar');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $fileName = time() . '_' . $request->file('foto_petugas')->getClientOriginalName();
            $request->file('foto_petugas')->move($path, $fileName);
            $validated['foto_petugas'] = 'uploads/material_keluar/' . $fileName;
        }

        $data->update($validated);

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil diperbarui dan stok Stand By disesuaikan!');
    }

    public function destroy($id)
    {
        $data = MaterialKeluar::findOrFail($id);

        $materialId = $data->material_id;
        $jumlahKeluar = $data->jumlah_material;

        $materialStok = MaterialStandBy::where('material_id', $materialId)->first();
        if ($materialStok) {
            $materialStok->increment('jumlah', $jumlahKeluar);
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

        return redirect()->route('material_keluar.index')->with('success', 'Data Material Keluar berhasil dihapus dan stok Stand By dikembalikan!');
    }

    // ⚠️ Fungsi showFoto & downloadFoto HARUS DIUBAH — karena sekarang file ada di public/
    public function showFoto(MaterialKeluar $materialKeluar)
    {
        $filePath = public_path($materialKeluar->foto);
        if (!$materialKeluar->foto || !File::exists($filePath)) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath);
        $response = response()->file($filePath, ['Content-Type' => $mimeType]);
        return $response;
    }

    public function showFotoPetugas(MaterialKeluar $materialKeluar)
    {
        $filePath = public_path($materialKeluar->foto_petugas);
        if (!$materialKeluar->foto_petugas || !File::exists($filePath)) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath);
        $response = response()->file($filePath, ['Content-Type' => $mimeType]);
        return $response;
    }

    public function downloadFoto(MaterialKeluar $materialKeluar)
    {
        $filePath = public_path($materialKeluar->foto);
        if (!$materialKeluar->foto || !File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }

    public function downloadFotoPetugas(MaterialKeluar $materialKeluar)
    {
        $filePath = public_path($materialKeluar->foto_petugas);
        if (!$materialKeluar->foto_petugas || !File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_keluar_' . $tanggalMulai->format('Ymd') . '_sd_' . $tanggalAkhir->format('Ymd');

        $items = MaterialKeluar::with('material')
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
            $pdf = Pdf::loadView('material_keluar.laporan_pdf', $data);
            return $pdf->download($filename . '.pdf');
        }

        if ($request->has('submit_excel')) {
            return Excel::download(new \App\Exports\MaterialKeluarExport($tanggalMulai, $tanggalAkhir), $filename . '.xlsx');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh laporan.');
    }
}