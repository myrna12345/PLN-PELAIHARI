<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy; 
<<<<<<< HEAD
use App\Models\MaterialHistory; // PERBAIKAN: Import Model yang benar
=======
>>>>>>> 05bf57db89d32966c7cb155d9f708f0d7dc57793
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\MaterialStandByExport;

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

    // --- 1. INDEX ---
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');
        
        $query = MaterialStandBy::with('material'); 
        
        if ($search) {
            $query->whereHas('material', function($q) use ($search) {
                $q->where('nama_material', 'like', "%$search%");
            });
        }
        
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10);
        
        return view('material_stand_by.index', compact('items'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        $materials = Material::all()->sortBy('nama_material', SORT_NATURAL);
        return view('material_stand_by.create', compact('materials'));
    }

    // --- 3. STORE (FIX ERROR MATERIAL_ID) ---
    public function store(Request $request)
    {
<<<<<<< HEAD
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah'      => 'required|integer|min:1',
            'satuan'      => 'required|string', 
            'foto'        => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
=======
        // Validasi
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string', 
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
>>>>>>> 05bf57db89d32966c7cb155d9f708f0d7dc57793
        ]);

        $destinationPath = public_path($this->uploadFolder);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

<<<<<<< HEAD
        // Upload Foto Baru
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($destinationPath, $fotoName);

        $material = Material::findOrFail($validated['material_id']);

        // A. UPDATE/CREATE STOK (Tabel Utama)
        $existingItem = MaterialStandBy::where('material_id', $validated['material_id'])
                                       ->where('satuan', $validated['satuan'])
                                       ->first();

        if ($existingItem) {
            $oldPath = public_path($this->uploadFolder . '/' . $existingItem->foto_path);
            if ($existingItem->foto_path && File::exists($oldPath)) { File::delete($oldPath); }

            $existingItem->jumlah += $validated['jumlah']; // Logika Penjumlahan (100 + 50 = 150)
            $existingItem->foto_path = $fotoName;
            $existingItem->tanggal = Carbon::now('Asia/Makassar'); 
            $existingItem->save();
        } else {
            MaterialStandBy::create([
                'material_id'           => $validated['material_id'],
                'nama_material_lengkap' => $material->nama_material,
                'satuan'                => $validated['satuan'], 
                'jumlah'                => $validated['jumlah'],
                'foto_path'             => $fotoName,
                'tanggal'               => Carbon::now('Asia/Makassar'),
            ]);
        }

        // B. CATAT RIWAYAT: Sekarang menyertakan material_id agar tidak error 1364
        MaterialHistory::create([
            'material_id'   => $validated['material_id'], // Diambil dari migrasi Anda
            'nama_material' => $material->nama_material,
            'jumlah'        => $validated['jumlah'], 
            'satuan'        => $validated['satuan'],
            'foto_path'     => $fotoName,
            'tanggal_input' => Carbon::now('Asia/Makassar'),
        ]);
=======
        // 1. Upload Foto Baru Terlebih Dahulu
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($destinationPath, $fotoName);

        // 2. LOGIKA MERGE: Cek apakah material sudah ada?
        $existingItem = MaterialStandBy::where('material_id', $validated['material_id'])->first();

        if ($existingItem) {
            // --- JIKA DATA SUDAH ADA (UPDATE) ---
            
            // Hapus foto lama agar tidak menumpuk di folder
            $oldPath = public_path($this->uploadFolder . '/' . $existingItem->foto_path);
            if ($existingItem->foto_path && File::exists($oldPath)) { 
                File::delete($oldPath); 
            }

            // Tambahkan Jumlah Lama + Jumlah Baru
            $existingItem->jumlah = $existingItem->jumlah + $validated['jumlah'];
            
            // Update data lainnya (Foto baru, Satuan baru, Tanggal update ke sekarang)
            $existingItem->foto_path = $fotoName;
            $existingItem->satuan = $request->satuan;
            $existingItem->tanggal = Carbon::now('Asia/Makassar'); 
            
            $existingItem->save();

            $message = 'Material sudah ada. Jumlah berhasil ditambahkan dan data diperbarui!';

        } else {
            // --- JIKA DATA BELUM ADA (CREATE BARU) ---
            
            $material = Material::findOrFail($validated['material_id']);

            MaterialStandBy::create([
                'material_id' => $validated['material_id'],
                'nama_material_lengkap' => $material->nama_material, // Opsional
                'satuan' => $request->satuan, 
                'jumlah' => $validated['jumlah'],
                'foto_path' => $fotoName,
                'tanggal' => Carbon::now('Asia/Makassar'),
            ]);
>>>>>>> 05bf57db89d32966c7cb155d9f708f0d7dc57793

            $message = 'Data Material Stand By baru berhasil disimpan!';
        }

<<<<<<< HEAD
    // --- SISA FUNGSI TETAP SAMA ---
    public function edit(MaterialStandBy $materialStandBy) { /* ... */ }
    public function update(Request $request, MaterialStandBy $materialStandBy) { /* ... */ }
    public function destroy(MaterialStandBy $materialStandBy) { /* ... */ }
    public function downloadFoto($id) { /* ... */ }
    public function downloadReport(Request $request) { /* ... */ }
=======
        return redirect()->route('material-stand-by.index')->with('success', $message);
    }

    public function edit(MaterialStandBy $materialStandBy)
    {
        $materials = Material::all()->sortBy('nama_material', SORT_NATURAL);
        return view('material_stand_by.edit', ['item' => $materialStandBy, 'materials' => $materials]);
    }

    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        // Validasi Update
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $destinationPath = public_path($this->uploadFolder);

        // Update Foto Material
        if ($request->hasFile('foto')) {
            $oldPath = public_path($this->uploadFolder . '/' . $materialStandBy->foto_path);
            if ($materialStandBy->foto_path && File::exists($oldPath)) { File::delete($oldPath); }
            
            $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($destinationPath, $fotoName);
            $materialStandBy->foto_path = $fotoName;
        }

        $material = Material::findOrFail($validated['material_id']);
        
        $materialStandBy->material_id = $validated['material_id'];
        $materialStandBy->satuan = $request->satuan; 
        $materialStandBy->jumlah = $validated['jumlah'];
        
        $materialStandBy->save();

        return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(MaterialStandBy $materialStandBy) 
    {
        // Hapus Foto Material Saja
        if ($materialStandBy->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $materialStandBy->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }
        
        $materialStandBy->delete();
        return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil dihapus!');
    }

    public function downloadFoto($id)
    {
        // Cari manual berdasarkan ID
        $materialStandBy = MaterialStandBy::findOrFail($id);

        if (!$materialStandBy->foto_path) {
            return back()->with('error', 'Nama file tidak ditemukan di database.');
        }

        $path = public_path($this->uploadFolder . '/' . $materialStandBy->foto_path);
        
        if (File::exists($path)) {
            return response()->download($path);
        }
        
        return back()->with('error', 'File fisik tidak ditemukan di server.');
    }
    
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = MaterialStandBy::with('material')
            ->whereBetween('tanggal', [$dateStart, $dateEnd]) 
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pada periode tersebut.');
        }

        $tanggal_mulai = $dateStart->format('d M Y');
        $tanggal_akhir = $dateEnd->format('d M Y');

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_stand_by.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('Laporan_Material_StandBy.pdf');
        } elseif ($request->has('submit_excel')) {
            return Excel::download(new MaterialStandByExport($dateStart, $dateEnd), 'Laporan_Material_StandBy.xlsx'); 
        }
        
        return redirect()->back();
    }
>>>>>>> 05bf57db89d32966c7cb155d9f708f0d7dc57793
}