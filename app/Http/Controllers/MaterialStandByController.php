<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\MaterialStandByExport;

class MaterialStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_stand_by';

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

    public function create()
    {
        $materials = Material::all()->sortBy('nama_material', SORT_NATURAL);
        return view('material_stand_by.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string', 
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Validasi foto_petugas DIHAPUS
        ]);

        $destinationPath = public_path($this->uploadFolder);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Upload Foto Material
        $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move($destinationPath, $fotoName);
        
        // Simpan Data
        MaterialStandBy::create([
            'material_id' => $validated['material_id'],
            'satuan' => $request->satuan, 
            'jumlah' => $validated['jumlah'],
            'foto_path' => $fotoName,
            // 'foto_petugas' DIHAPUS
            'tanggal' => Carbon::now('Asia/Makassar'),
        ]);

        return redirect()->route('material-stand-by.index')->with('success', 'Data Material Stand By berhasil disimpan!');
    }

    public function edit(MaterialStandBy $materialStandBy)
    {
        $materials = Material::all()->sortBy('nama_material', SORT_NATURAL);
        return view('material_stand_by.edit', ['item' => $materialStandBy, 'materials' => $materials]);
    }

    public function update(Request $request, MaterialStandBy $materialStandBy)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string', 
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Validasi foto_petugas DIHAPUS
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

        $materialStandBy->material_id = $validated['material_id'];
        $materialStandBy->satuan = $request->satuan; 
        $materialStandBy->jumlah = $validated['jumlah'];
        
        $materialStandBy->save();

        return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(MaterialStandBy $materialStandBy) 
    {
        if ($materialStandBy->foto_path) {
            $path = public_path($this->uploadFolder . '/' . $materialStandBy->foto_path);
            if (File::exists($path)) { File::delete($path); }
        }

        $materialStandBy->delete();
        return redirect()->route('material-stand-by.index')->with('success', 'Data berhasil dihapus!');
    }

    // --- PERBAIKAN: Menggunakan $id manual agar aman dari error binding ---
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
}