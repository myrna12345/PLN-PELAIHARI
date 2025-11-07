<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialKembali;
use App\Exports\MaterialKembaliExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaterialKembaliController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = MaterialKembali::query();

        if ($search) {
            $query->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nama_petugas', 'like', "%{$search}%");
        }

        $materialKembali = $query->orderByDesc('tanggal')->paginate(10);

        return view('material_kembali.index', compact('materialKembali'));
    }

    public function create()
    {
        $materialList = Material::all();
        return view('material_kembali.create', compact('materialList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'tanggal' => 'nullable|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['tanggal'] = now('Asia/Makassar');

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        MaterialKembali::create($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil disimpan!');
    }



    public function lihat($id)
    {
        $item = MaterialKembali::findOrFail($id);
        return view('material_kembali.lihat', compact('item'));
    }

    public function edit($id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);
        $materialList = Material::all(); 

        return view('material_kembali.edit', compact('materialKembali', 'materialList'));
    }



    public function update(Request $request, $id)
    {
        $materialKembali = MaterialKembali::findOrFail($id);

        $validated = $request->validate([
            'nama_material' => 'required|string|max:255',
            'nama_petugas' => 'required|string|max:255',
            'jumlah_material' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($materialKembali->foto) {
                Storage::disk('public')->delete($materialKembali->foto);
            }
            $validated['foto'] = $request->file('foto')->store('material_kembali', 'public');
        }

        $materialKembali->update($validated);

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $data = MaterialKembali::findOrFail($id);
        
        if ($data->foto) {
            Storage::disk('public')->delete($data->foto);
        }

        $data->delete();

        return redirect()->route('material_kembali.index')->with('success', 'Data berhasil dihapus!');
    }

    public function downloadReport(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $mulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $akhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        $filename = 'laporan_material_kembali_'.$mulai->format('Ymd').'_sd_'.$akhir->format('Ymd');

        $items = MaterialKembali::whereBetween('tanggal', [$mulai, $akhir])
                ->orderBy('tanggal', 'asc')
                ->get();

        if ($request->has('submit_pdf')) {
            $pdf = Pdf::loadView('material_kembali.laporan_pdf', compact('items','mulai','akhir'));
            return $pdf->download($filename . '.pdf');
        }

        if ($request->has('submit_excel')) {
            return Excel::download(new MaterialKembaliExport($mulai, $akhir), $filename . '.xlsx');
        }

        return back()->with('error', 'Gagal unduh laporan.');
    }
}