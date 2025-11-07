<?php

namespace App\Http\Controllers;

use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Material;

class MaterialSiagaStandByController extends Controller
{
    
    public function export(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $exportType = $request->export; // pdf / excel

    $data = MaterialSiagaStandBy::whereBetween('tanggal', [$start_date, $end_date])->get();

    if ($exportType == 'pdf') {
        $pdf = Pdf::loadView('materialsiaga.export-pdf', compact('data', 'start_date', 'end_date'));
        return $pdf->download('material-siaga.pdf');
    }

}

    public function updateStatus(Request $request, $id)
    {
        $item = MaterialSiagaStandBy::findOrFail($id);
        $item->status = $request->status;
        $item->save();

        return back()->with('success', 'Status berhasil diperbarui!');
    }

    public function index()
    {
        $dataSiaga = MaterialSiagaStandBy::orderBy('tanggal', 'desc')->paginate(10);
        return view('materialsiaga.index', compact('dataSiaga'));
    }

    public function create()
    {
        return view('materialsiaga.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_material'         => 'required|string',
            'nama_petugas'          => 'required|string|max:255',
            'stand_meter'           => 'required|string|max:100',
            'jumlah_siaga_standby'  => 'required|integer|min:0',
            'tanggal'               => 'required|date',
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->hasFile('unggah_foto')
            ? $request->file('unggah_foto')->store('foto_siaga', 'public')
            : null;

        MaterialSiagaStandBy::create([
            'nama_material'         => $validatedData['nama_material'],
            'nama_petugas'          => $validatedData['nama_petugas'],
            'stand_meter'           => $validatedData['stand_meter'],
            'jumlah_siaga_standby'  => $validatedData['jumlah_siaga_standby'],
            'tanggal'               => $validatedData['tanggal'],
            'foto'                  => $path,
            'status'                => 'Ready', // Default Status
        ]);

        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil disimpan!');
    }

    // FORM EDIT
    public function edit($id)
{
    $data = MaterialSiagaStandBy::findOrFail($id);
    $materials = Material::all(); // <-- Ambil semua nama material

    return view('materialsiaga.edit', compact('data', 'materials'));
}


    // UPDATE DATA
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_material'         => 'required|string',
            'nama_petugas'          => 'required|string|max:255',
            'stand_meter'           => 'required|string|max:100',
            'jumlah_siaga_standby'  => 'required|integer|min:0',
            'tanggal'               => 'required|date',
            'status'                => 'required|in:Ready,Terpakai',
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = MaterialSiagaStandBy::findOrFail($id);

        // Jika upload foto baru → hapus foto lama & simpan baru
        if ($request->hasFile('unggah_foto')) {
            if ($data->foto) {
                Storage::disk('public')->delete($data->foto);
            }
            $data->foto = $request->file('unggah_foto')->store('foto_siaga', 'public');
        }

        $data->update([
            'nama_material' => $request->nama_material,
            'nama_petugas' => $request->nama_petugas,
            'stand_meter' => $request->stand_meter,
            'jumlah_siaga_standby' => $request->jumlah_siaga_standby,
            'tanggal' => $request->tanggal,
            'status' => $request->status,
        ]);

        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $item = MaterialSiagaStandBy::findOrFail($id);

        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }

        $item->delete();

        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil dihapus!');
    }
}
