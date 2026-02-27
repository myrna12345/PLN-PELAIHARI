<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SiagaKembali; 
use App\Models\SiagaKeluar; 
use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SiagaKembaliExport; 
use Intervention\Image\Laravel\Facades\Image;

class SiagaKembaliController extends Controller
{
    private $uploadFolder = 'uploads/siaga_kembali';

    public function index(Request $request)
    {
        // [PROTEKSI HARMET] Blokir akses ke halaman laporan
        if (strtolower(auth()->user()->role) === 'harmet') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

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
        
        if ($tanggalMulai) { 
            $query->whereDate('tanggal', '>=', $tanggalMulai); 
        }
        if ($tanggalAkhir) { 
            $query->whereDate('tanggal', '<=', $tanggalAkhir); 
        }

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
            'stand_meter'   => 'required|numeric|min:0',
            'keterangan'    => 'nullable|string',
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360',
            'foto_petugas'  => 'required|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360',
        ]);

        $materialMaster = Material::findOrFail($validated['material_id']);
        $dataKeluar = SiagaKeluar::where('nomor_meter', $validated['nomor_meter'])
                                 ->where('nama_material_lengkap', $materialMaster->nama_material)
                                 ->first();

        if (!$dataKeluar) { 
            return redirect()->back()->withInput()->with('error', 'Material belum pernah dikeluarkan.'); 
        }

        if ($validated['stand_meter'] < $dataKeluar->stand_meter) { 
            return redirect()->back()->withInput()->with('error', 'Stand meter kembali tidak boleh lebih kecil dari stand keluar.'); 
        }

        $path = public_path($this->uploadFolder);
        if (!File::isDirectory($path)) { 
            File::makeDirectory($path, 0755, true, true); 
        }

        try {
            $fotoName = time() . '_mat_' . uniqid() . '.jpg';
            Image::read($request->file('foto'))->scale(width: 800)->toJpeg(60)->save($path . '/' . $fotoName);

            $fotoPetugasName = time() . '_pet_' . uniqid() . '.jpg';
            Image::read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(60)->save($path . '/' . $fotoPetugasName);

            SiagaKembali::create([
                'material_id'           => $validated['material_id'],
                'nomor_meter'           => $validated['nomor_meter'],
                'nama_material_lengkap' => $materialMaster->nama_material,
                'nama_petugas'          => $validated['nama_petugas'],
                'stand_meter'           => $validated['stand_meter'],
                'status'                => 'Kembali', 
                'keterangan'            => $validated['keterangan'],
                'foto_path'             => $fotoName,
                'foto_petugas'          => $fotoPetugasName,
                'tanggal'               => Carbon::now('Asia/Makassar'),
            ]);

            MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
                ->update(['stand_meter' => $validated['stand_meter'], 'status' => 'Ready']);

            // [PROTEKSI HARMET & SATPAM] Redirect ke Dashboard
            if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
                return redirect()->route('dashboard')->with('success', 'Material berhasil dikembalikan.');
            }

            return redirect()->route('siaga-kembali.index')->with('success', 'Material berhasil dikembalikan.');

        } catch (\Exception $e) { 
            return back()->with('error', 'Gagal memproses data.'); 
        }
    }

    public function edit(SiagaKembali $siagaKembali) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.'); 
        } 
        $allowedMaterials = ['KWH Siaga 1P', 'KWH Siaga 3P']; 
        $materials = Material::where('kategori', 'siaga')->whereIn('nama_material', $allowedMaterials)->get()->sortBy('nama_material', SORT_NATURAL); 
        return view('siaga-kembali.edit', ['item' => $siagaKembali, 'materials' => $materials]); 
    }

    public function update(Request $request, SiagaKembali $siagaKembali) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.'); 
        } 
        
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nomor_meter' => 'required|string|max:255', 
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|numeric|min:0',
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif,heic,webp|max:15360',
        ]);

        $destinationPath = public_path($this->uploadFolder);

        try {
            if ($request->hasFile('foto')) {
                if ($siagaKembali->foto_path && File::exists(public_path($this->uploadFolder . '/' . $siagaKembali->foto_path))) {
                    File::delete(public_path($this->uploadFolder . '/' . $siagaKembali->foto_path));
                }
                $fotoName = time() . '_mat_' . uniqid() . '.jpg';
                Image::read($request->file('foto'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoName);
                $siagaKembali->foto_path = $fotoName;
            }

            if ($request->hasFile('foto_petugas')) {
                if ($siagaKembali->foto_petugas && File::exists(public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas))) {
                    File::delete(public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas));
                }
                $fotoPetugasName = time() . '_pet_' . uniqid() . '.jpg';
                Image::read($request->file('foto_petugas'))->scale(width: 800)->toJpeg(60)->save($destinationPath . '/' . $fotoPetugasName);
                $siagaKembali->foto_petugas = $fotoPetugasName;
            }

            $material = Material::findOrFail($validated['material_id']);
            $siagaKembali->fill([
                'material_id' => $validated['material_id'],
                'nomor_meter' => $validated['nomor_meter'],
                'nama_petugas' => $validated['nama_petugas'],
                'stand_meter' => $validated['stand_meter'],
                'status' => $validated['status'] ?? 'Kembali',
                'keterangan' => $validated['keterangan'],
                'nama_material_lengkap' => $material->nama_material,
            ])->save();

            MaterialSiagaStandBy::where('nomor_meter', $validated['nomor_meter'])
                ->update(['stand_meter' => $validated['stand_meter']]);

            return redirect()->route('siaga-kembali.index')->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy(SiagaKembali $siagaKembali) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->route('siaga-kembali.index')->with('error', 'Akses ditolak.'); 
        } 
        if ($siagaKembali->foto_path) { File::delete(public_path($this->uploadFolder . '/' . $siagaKembali->foto_path)); }
        if ($siagaKembali->foto_petugas) { File::delete(public_path($this->uploadFolder . '/' . $siagaKembali->foto_petugas)); }
        $siagaKembali->delete();
        return redirect()->route('siaga-kembali.index')->with('success', 'Data dihapus!');
    }

    public function downloadFoto($id) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->back()->with('error', 'Akses ditolak.'); 
        } 
        $item = SiagaKembali::findOrFail($id);
        return response()->download(public_path($this->uploadFolder . '/' . $item->foto_path));
    }

    public function downloadFotoPetugas($id) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->back()->with('error', 'Akses ditolak.'); 
        } 
        $item = SiagaKembali::findOrFail($id);
        return response()->download(public_path($this->uploadFolder . '/' . $item->foto_petugas));
    }

    public function downloadReport(Request $request) 
    { 
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) { 
            return redirect()->back()->with('error', 'Akses ditolak.'); 
        } 
        $request->validate(['tanggal_mulai' => 'required|date', 'tanggal_akhir' => 'required|date']);
        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $items = SiagaKembali::with('material')->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])->orderBy('tanggal', 'asc')->get();
        return Pdf::loadView('siaga-kembali.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'))->download('laporan_siaga_kembali.pdf');
    }

    public function showFoto(SiagaKembali $siagaKembali)
    {
        $path = public_path($this->uploadFolder . '/' . $siagaKembali->foto_path);
        if (File::exists($path)) {
            return response()->file($path);
        }
        abort(404);
    }
}