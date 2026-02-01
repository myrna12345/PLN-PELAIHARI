<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStandBy; 
use App\Models\MaterialHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\DB; // Perbaikan: Menambahkan Facade DB
use Carbon\Carbon;
use App\Exports\MaterialHistoryExport; 
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Exports\MaterialStandByExport;

class MaterialStandByController extends Controller
{
    // Folder penyimpanan disatukan agar konsisten di semua method
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
        
        // 1. Validasi Input
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'jumlah' => 'required|numeric',
            'satuan' => 'required',
            'foto' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // 2. Ambil data material master untuk mencegah error 'nama_material' null di history
        $materialMaster = Material::findOrFail($request->material_id);

        // 3. Handle Upload Foto
        $nama_file = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $nama_file = time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();
            
            $destinationPath = public_path($this->uploadFolder);
            $file->move($destinationPath, $nama_file);
        }

        // 4. Database Transaction: Menjamin data masuk ke kedua tabel atau tidak sama sekali
        DB::transaction(function () use ($request, $nama_file, $materialMaster) {
            
            // Simpan/Update Tabel Utama (material_stand_by)
            MaterialStandBy::updateOrCreate(
                ['material_id' => $request->material_id],
                [
                    'jumlah'    => $request->jumlah,
                    'satuan'    => $request->satuan,
                    'tanggal'   => now(),
                    'foto_path' => $nama_file,
                ]
            );

            // Simpan ke Tabel History (Log permanen)
            MaterialHistory::create([
                'material_id'   => $request->material_id,
                'nama_material' => $materialMaster->nama_material, // Diambil dari master, bukan request
                'jumlah'        => $request->jumlah,
                'satuan'        => $request->satuan,
                'foto_path'     => $nama_file,
                'tanggal_input' => now(),
            ]);
        });

        return redirect()->route('material-stand-by.index')
                         ->with('success', 'Data berhasil diperbarui dan dicatat di riwayat!');
    }

    public function history()
    {
        $histories = MaterialHistory::orderBy('created_at', 'desc')->get();
        return view('material_history.index', compact('histories'));
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
        ]);

        $destinationPath = public_path($this->uploadFolder);

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            $oldPath = $destinationPath . '/' . $materialStandBy->foto_path;
            if ($materialStandBy->foto_path && File::exists($oldPath)) { 
                File::delete($oldPath); 
            }
            
            $fotoName = time() . '_mat_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move($destinationPath, $fotoName);
            $materialStandBy->foto_path = $fotoName;
        }

        $materialStandBy->material_id = $validated['material_id'];
        $materialStandBy->satuan = $request->satuan; 
        $materialStandBy->jumlah = $validated['jumlah'];
        $materialStandBy->tanggal = now();
        
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

    public function downloadFoto($id)
    {
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
    public function downloadHistory(Request $request)
    {
        $query = MaterialHistory::orderBy('tanggal_input', 'desc');

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $dateStart = Carbon::parse($request->tanggal_mulai)->startOfDay();
            $dateEnd = Carbon::parse($request->tanggal_akhir)->endOfDay();
            $query->whereBetween('tanggal_input', [$dateStart, $dateEnd]);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        if ($request->has('submit_pdf')) {
            // Menggunakan view khusus PDF
            $pdf = Pdf::loadView('material-history.pdf_view', compact('items'))
                      ->setPaper('a4', 'portrait');
            return $pdf->download('Riwayat_Material_Standby.pdf');
        }

        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialHistoryExport($items), 'Riwayat_Material_Standby.xlsx');
        }
    }
}