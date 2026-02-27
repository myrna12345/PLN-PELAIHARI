<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy;
use App\Models\MaterialHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaterialStandByExport;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Laravel\Facades\Image; 

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

    // ===============================
    // 1. INDEX (SEMUA BISA AKSES)
    // ===============================
    public function index(Request $request)
    {
        $search = $request->search;
        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;

        $query = MaterialStandBy::with('material');

        if ($search) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%");
            });
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }

        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        $items = $query->latest('tanggal')->paginate(10);

        return view('material_stand_by.index', compact('items'));
    }

    // ===============================
    // 2. CREATE (SEMUA BISA AKSES)
    // ===============================
    public function create()
    {
        $materials = Material::where('kategori', 'teknik')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);
                             
        return view('material_stand_by.create', compact('materials'));
    }

    // ===============================
    // 3. STORE (SEMUA BISA AKSES)
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string',
            'foto'        => 'required|image|mimes:jpg,jpeg,png,heic,webp|max:15360',
        ]);

        try {
            $path = public_path($this->uploadFolder);
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $fotoName = time() . '_' . uniqid() . '.jpg';
            
            $image = Image::read($request->file('foto'));
            $image->scale(width: 800);
            $encoded = $image->toJpeg(60);
            $encoded->save($path . '/' . $fotoName);

            $material = Material::findOrFail($validated['material_id']);

            $item = MaterialStandBy::where('material_id', $validated['material_id'])
                ->where('satuan', $validated['satuan'])
                ->first();

            if ($item) {
                $item->jumlah += $validated['jumlah'];
                $item->foto_path = $fotoName;
                $item->tanggal = now('Asia/Makassar');
                $item->save();
            } else {
                MaterialStandBy::create([
                    'material_id' => $validated['material_id'],
                    'nama_material_lengkap' => $material->nama_material,
                    'jumlah' => $validated['jumlah'],
                    'satuan' => $validated['satuan'],
                    'foto_path' => $fotoName,
                    'tanggal' => now('Asia/Makassar'),
                ]);
            }

            MaterialHistory::create([
                'nama_material' => $material->nama_material,
                'jumlah' => $validated['jumlah'],
                'satuan' => $validated['satuan'],
                'foto_path' => $fotoName,
                'tanggal_input' => now('Asia/Makassar'),
            ]);

            return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            Log::error("Store Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data.')->withInput();
        }
    }

    // ===============================
    // 4. EDIT
    // ===============================
    public function edit($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')->with('error', 'Akses Ditolak.');
        }

        $item = MaterialStandBy::findOrFail($id);
        $materials = Material::where('kategori', 'teknik')
                             ->get()
                             ->sortBy('nama_material', SORT_NATURAL);

        return view('material_stand_by.edit', compact('item', 'materials'));
    }

    // ===============================
    // 5. UPDATE (DIPERBAIKI UNTUK UPDATE FOTO HISTORY)
    // ===============================
    public function update(Request $request, $id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')->with('error', 'Akses Ditolak.');
        }

        $item = MaterialStandBy::findOrFail($id);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:0',
            'foto'   => 'nullable|image|mimes:jpg,jpeg,png,heic,webp|max:15360',
        ]);

        try {
            $fotoBaru = null;

            if ($request->hasFile('foto')) {
                $path = public_path($this->uploadFolder);

                // Kita buat foto baru tanpa menghapus yang lama agar tidak crash
                $fotoName = time() . '_' . uniqid() . '.jpg';
                $image = Image::read($request->file('foto'));
                $image->scale(width: 800);
                $encoded = $image->toJpeg(60);
                $encoded->save($path . '/' . $fotoName);

                $item->foto_path = $fotoName;
                $fotoBaru = $fotoName;
            }

            $item->jumlah = $validated['jumlah'];
            $item->tanggal = now('Asia/Makassar'); 
            $item->save();

            // LOGIKA UPDATE FOTO DI HISTORY:
            // Jika ada foto baru, update juga foto di tabel history yang terkait
            if ($fotoBaru) {
                $namaMaterial = $item->nama_material_lengkap ?? ($item->material->nama_material ?? null);
                
                if ($namaMaterial) {
                    MaterialHistory::where('nama_material', $namaMaterial)
                        ->where('satuan', $item->satuan)
                        // Update history yang tanggalnya sama dengan data standby ini (opsional)
                        // atau update history terakhir yang merujuk ke foto lama
                        ->whereDate('tanggal_input', Carbon::parse($item->tanggal)->toDateString())
                        ->update(['foto_path' => $fotoBaru]);
                }
            }

            return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error("Update Error: " . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui data.');
        }
    }

    // ===========================================
    // 6. DELETE (DENGAN LOGIKA HAPUS HISTORY)
    // ===========================================
    public function destroy($id)
    {
        if (auth()->user()->role === 'satpam') {
            return redirect()->route('material-stand-by.index')->with('error', 'Akses Ditolak.');
        }

        $item = MaterialStandBy::with('material')->findOrFail($id);
        $namaMaterial = $item->nama_material_lengkap ?? ($item->material->nama_material ?? null);

        if ($namaMaterial) {
            $historyPhotos = MaterialHistory::where('nama_material', $namaMaterial)
                ->where('satuan', $item->satuan)
                ->pluck('foto_path');

            foreach($historyPhotos as $photo) {
                if ($photo && File::exists(public_path($this->uploadFolder . '/' . $photo))) {
                    File::delete(public_path($this->uploadFolder . '/' . $photo));
                }
            }

            MaterialHistory::where('nama_material', $namaMaterial)
                ->where('satuan', $item->satuan)
                ->delete();
        }

        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        if ($item->foto_path && File::exists($path)) {
            File::delete($path);
        }

        $item->delete();
        return redirect()->route('material-stand-by.index')->with('success', 'Data Stok dan Riwayat berhasil dihapus.');
    }

    // ===============================
    // 7. DOWNLOAD PDF & EXCEL & LAINNYA
    // ===============================
    public function downloadPdf(Request $request)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses Ditolak.'); }
        $request->validate(['tanggal_mulai' => 'required|date', 'tanggal_akhir' => 'required|date']);
        $items = MaterialStandBy::whereBetween('tanggal', [Carbon::parse($request->tanggal_mulai)->startOfDay(), Carbon::parse($request->tanggal_akhir)->endOfDay()])->get();
        return Pdf::loadView('material_stand_by.laporan_pdf', ['items' => $items, 'tanggal_mulai' => $request->tanggal_mulai, 'tanggal_akhir' => $request->tanggal_akhir])->download('Laporan_Material_Standby.pdf');
    }

    public function downloadExcel(Request $request)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses Ditolak.'); }
        return Excel::download(new MaterialStandByExport($request->tanggal_mulai, $request->tanggal_akhir), 'Laporan_Material_Standby.xlsx');
    }

    public function showFoto($id)
    {
        $item = MaterialStandBy::findOrFail($id);
        return view('material_stand_by.show_foto', compact('item'));
    }

    public function downloadFoto($id)
    {
        if (auth()->user()->role === 'satpam') { return redirect()->back()->with('error', 'Akses Ditolak.'); }
        $item = MaterialStandBy::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->foto_path);
        return File::exists($path) ? response()->download($path) : abort(404);
    }
}