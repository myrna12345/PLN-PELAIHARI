<?php

namespace App\Http\Controllers;

use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Material;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

  
class MaterialSiagaStandByController extends Controller
{
    
public function export(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;

    // pastikan end date hingga 23:59
    $end = \Carbon\Carbon::parse($end_date)->endOfDay();

    $exportType = $request->export;

    // === URUTAN SAMA DENGAN HALAMAN INDEX (id DESC) ===
    $data = MaterialSiagaStandBy::whereBetween('tanggal', [$start_date, $end])
        ->orderBy('id', 'DESC') // <-- SAMA PERSIS DENGAN HALAMAN
        ->get();

    // ========== EXPORT PDF ==========
    if ($exportType == 'pdf') {
        $pdf = \PDF::loadView(
            'material-siaga.export_pdf',
            compact('data', 'start_date', 'end_date')
        )->setPaper('a4', 'portrait');

        return $pdf->download('material-siaga.pdf');
    }

    // ========== EXPORT EXCEL ==========
    if ($exportType == 'excel') {

        $exportData = [];

        // Header
        $exportData[] = [
            'No',
            'Nama Material',
            'Nama Petugas',
            'Stand Meter',
            'Jumlah Material',
            'Tanggal',
            'Status'
        ];

        // Data
        foreach ($data as $index => $item) {
            $exportData[] = [
                $index + 1,
                $item->nama_material,
                $item->nama_petugas,
                $item->stand_meter,
                $item->jumlah_siaga_standby,
                \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i'),
                $item->status
            ];
        }

        $collection = new \Illuminate\Support\Collection($exportData);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($collection) implements \Maatwebsite\Excel\Concerns\FromCollection {
                protected $collection;
                public function __construct($collection) { $this->collection = $collection; }
                public function collection() { return $this->collection; }
            },
            'material-siaga.xlsx'
        );
    }

    return back()->with('error', 'Jenis export tidak dikenali.');
}

    public function updateStatus(Request $request, $id)
    {
        $item = MaterialSiagaStandBy::findOrFail($id);
        $item->status = $request->status;
        $item->save();

        return back()->with('success', 'Status berhasil diperbarui!');
    }

    public function index(Request $request)
{
    $search = $request->search;
    $start_date = $request->start_date;
    $end_date = $request->end_date;

    $dataSiaga = MaterialSiagaStandBy::when($search, function ($query, $search) {
        $query->where(function($q) use ($search) {
            $q->where('nama_material', 'like', "%{$search}%")
              ->orWhere('nama_petugas', 'like', "%{$search}%");
        });
    })
    ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
        $query->whereBetween('tanggal', [
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ]);
    })
    ->orderBy('id', 'DESC')
    ->paginate(10);

    return view('material-siaga.index', compact('dataSiaga'));
}

    public function create()
    {
        return view('material-siaga.create');
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

    return view('material-siaga.edit', compact('data', 'materials'));
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