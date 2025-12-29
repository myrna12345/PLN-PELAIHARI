<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRetur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MaterialReturController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = MaterialRetur::with('material');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_petugas', 'like', '%' . $search . '%')
                  ->orWhereHas('material', function($subQ) use ($search) {
                        $subQ->where('nama_material', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($tanggalMulai) { $query->whereDate('tanggal', '>=', $tanggalMulai); }
        if ($tanggalAkhir) { $query->whereDate('tanggal', '<=', $tanggalAkhir); }

        $items = $query->latest('tanggal')->paginate(10); 
        return view('material_retur.index', compact('items'));
    }

    public function create()
    {
        $materials = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->get()->sortBy('nama_material', SORT_NATURAL);
        return view('material_retur.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Buah,Meter',
            'status' => 'required|in:bekas_andal,rusak,baik', 
            'keterangan' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $pathMaterial = $request->file('foto')->store('fotos_material_retur', 'public');
        $pathPetugas = $request->file('foto_petugas')->store('foto_petugas', 'public');

        MaterialRetur::create([
            'material_id' => $validated['material_id'],
            'nama_petugas' => $validated['nama_petugas'],
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'foto_path' => $pathMaterial,
            'foto_petugas' => $pathPetugas,
            'tanggal' => Carbon::now('Asia/Makassar')
        ]);

        return redirect()->route('material-retur.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = MaterialRetur::findOrFail($id);
        $materials = Material::where('kategori', '!=', 'siaga')->orWhereNull('kategori')->get()->sortBy('nama_material', SORT_NATURAL);
        return view('material_retur.edit', compact('item', 'materials'));
    }

    public function update(Request $request, $id)
    {
        $materialRetur = MaterialRetur::findOrFail($id);
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'nama_petugas' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Buah,Meter',
            'status' => 'required|in:bekas_andal,rusak,baik', 
            'keterangan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $data = $validated;
        if ($request->hasFile('foto')) {
            if ($materialRetur->foto_path) Storage::disk('public')->delete($materialRetur->foto_path);
            $data['foto_path'] = $request->file('foto')->store('fotos_material_retur', 'public');
        }
        if ($request->hasFile('foto_petugas')) {
            if ($materialRetur->foto_petugas) Storage::disk('public')->delete($materialRetur->foto_petugas);
            $data['foto_petugas'] = $request->file('foto_petugas')->store('foto_petugas', 'public');
        }

        $materialRetur->update($data); 
        return redirect()->route('material-retur.index')->with('success', 'Data berhasil diperbarui.');
    }

    // --- FITUR DOWNLOAD REPORT (PERBAIKAN ERROR) ---
    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggal_mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggal_akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $items = MaterialRetur::with('material')
                    ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                    ->orderBy('tanggal', 'asc')
                    ->get();

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_retur.laporan_pdf', compact('items', 'tanggal_mulai', 'tanggal_akhir'));
            return $pdf->download('laporan_material_retur.pdf');
        }
        
        if ($request->has('submit_excel')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\MaterialReturExport($tanggal_mulai, $tanggal_akhir), 
                'laporan_material_retur.xlsx'
            );
        }
    }

    public function showFoto($id)
    {
        $item = MaterialRetur::findOrFail($id);
        return Storage::disk('public')->response($item->foto_path);
    }

    public function downloadFoto($id)
    {
        $item = MaterialRetur::findOrFail($id);
        return Storage::disk('public')->download($item->foto_path);
    }

    public function downloadFotoPetugas($id)
    {
        $item = MaterialRetur::findOrFail($id);
        return Storage::disk('public')->download($item->foto_petugas);
    }

    public function destroy($id)
    {
        $item = MaterialRetur::findOrFail($id);
        if ($item->foto_path) Storage::disk('public')->delete($item->foto_path);
        if ($item->foto_petugas) Storage::disk('public')->delete($item->foto_petugas);
        $item->delete();
        return redirect()->route('material-retur.index')->with('success', 'Data berhasil dihapus.');
    }
}