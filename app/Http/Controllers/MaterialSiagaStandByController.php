<?php

namespace App\Http\Controllers;

use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Material;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MaterialSiagaStandByController extends Controller
{
    // --- 1. INDEX (Dapat diakses oleh Satpam) ---
    public function index(Request $request)
    {
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $dataSiaga = MaterialSiagaStandBy::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama_material', 'like', "%{$search}%")
                      ->orWhere('nomor_meter', 'like', "%{$search}%"); 
            })
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('tanggal', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay()
                ]);
            })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('material-siaga.index', compact('dataSiaga', 'search', 'start_date', 'end_date'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('material-siaga.index')->with('error', 'Akses ditolak.');
        }

        $materials = Material::where('kategori', 'siaga')->get();
        return view('material-siaga.create', compact('materials'));
    }

    // --- 3. STORE ---
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_material'         => 'required|string',
            'nomor_meter'           => 'required|string|max:50',
            'stand_meter'           => 'required|string|max:100',
            'tanggal'               => 'required|date',
            'nama_petugas'          => 'nullable|string|max:255',
            'jumlah_siaga_standby'  => 'nullable|integer|min:0', 
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:5120', 
        ]);
        
        $path = null;
        if ($request->hasFile('unggah_foto')) {
            $path = $request->file('unggah_foto')->store('foto_siaga', 'public');
        }

        MaterialSiagaStandBy::create([
            'nama_material'         => $validatedData['nama_material'],
            'nomor_meter'           => $validatedData['nomor_meter'],
            'stand_meter'           => $validatedData['stand_meter'],
            'tanggal'               => $validatedData['tanggal'],
            'nama_petugas'          => $validatedData['nama_petugas'] ?? null, 
            'jumlah_siaga_standby'  => $validatedData['jumlah_siaga_standby'] ?? 1,
            'unggah_foto'           => $path,
            'status'                => 'Ready',
        ]);

        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil disimpan!');
    }
    
    // --- 4. EDIT ---
    public function edit($id)
{
    // Cari data berdasarkan ID
    $materialSiaga = MaterialSiagaStandBy::findOrFail($id);

    // Pastikan nama variabel di dalam compact('materialSiaga') 
    // sama dengan yang dipanggil di Blade
    return view('material-siaga.edit', compact('materialSiaga'));
}
    // --- 5. UPDATE ---
    public function update(Request $request, $id)
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('material-siaga.index')->with('error', 'Akses ditolak.');
        }

        $data = MaterialSiagaStandBy::findOrFail($id);

        $request->validate([
            'nama_material'         => 'required|string',
            'nomor_meter'           => 'required|string|max:50',
            'stand_meter'           => 'required|string|max:100',
            'tanggal'               => 'required|date',
            'status'                => 'required|in:Ready,Terpakai',
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $path = $data->unggah_foto; 

        if ($request->hasFile('unggah_foto')) {
            if ($path && Storage::disk('public')->exists($path)) { 
                Storage::disk('public')->delete($path); 
            }
            $path = $request->file('unggah_foto')->store('foto_siaga', 'public');
        }

        $data->update([
            'nama_material'         => $request->nama_material,
            'nomor_meter'           => $request->nomor_meter,
            'stand_meter'           => $request->stand_meter,
            'tanggal'               => $request->tanggal,
            'status'                => $request->status,
            'nama_petugas'          => $request->nama_petugas,
            'jumlah_siaga_standby'  => $request->jumlah_siaga_standby,
            'unggah_foto'           => $path,
        ]);

        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil diperbarui!');
    }

   public function showFoto($id) 
    {
        $item = MaterialSiagaStandBy::findOrFail($id);
        
        if (!$item->unggah_foto || !Storage::disk('public')->exists($item->unggah_foto)) {
            abort(404, 'File fisik tidak ditemukan di folder storage.');
        }
        
        return Storage::disk('public')->response($item->unggah_foto);
    }

    public function downloadFoto($id)
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = MaterialSiagaStandBy::findOrFail($id);
        
        if ($item->unggah_foto && Storage::disk('public')->exists($item->unggah_foto)) {
            return Storage::disk('public')->download($item->unggah_foto);
        }
        
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    // --- 6. DESTROY ---
    public function destroy($id)
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('material-siaga.index')->with('error', 'Akses ditolak.');
        }

        $item = MaterialSiagaStandBy::findOrFail($id);

        if ($item->unggah_foto) {
            Storage::disk('public')->delete($item->unggah_foto);
        }

        $item->delete();
        return redirect()->route('material-siaga.index')->with('success', 'Data berhasil dihapus!');
    }

    // --- 7. UPDATE STATUS ---
    public function updateStatus(Request $request, $id)
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->route('material-siaga.index')->with('error', 'Akses ditolak.');
        }

        $request->validate(['status' => 'required|in:Ready,Terpakai']);
        $item = MaterialSiagaStandBy::findOrFail($id);
        $item->status = $request->status;
        $item->save();
        return back()->with('success', 'Status berhasil diperbarui!');
    }
    
    // --- 8. EXPORT ---
    public function export(Request $request)
    {
        if (strtolower(auth()->user()->role) === 'satpam') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $exportType = $request->input('export');

        if (!$start_date || !$end_date) {
            return back()->with('error', 'Pilih rentang tanggal.');
        }

        $start = Carbon::parse($start_date)->startOfDay();
        $end = Carbon::parse($end_date)->endOfDay();

        $data = MaterialSiagaStandBy::whereBetween('tanggal', [$start, $end])
            ->orderBy('id', 'DESC')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Data kosong.');
        }
        
        if ($exportType === 'pdf') {
            foreach ($data as $item) {
                if ($item->unggah_foto && Storage::disk('public')->exists($item->unggah_foto)) {
                    $path = storage_path('app/public/' . $item->unggah_foto);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($path);
                    $item->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                } else {
                    $item->foto_base64 = null;
                }
            }

            $pdf = Pdf::loadView('material-siaga.export_pdf', compact('data', 'start_date', 'end_date'))
                      ->setPaper('a4', 'portrait');
            return $pdf->download('material-siaga-' . now()->format('Ymd_His') . '.pdf');
        }

        if ($exportType === 'excel') {
            $exportData[] = ['No', 'Nama Material & Nomor Meter', 'Stand Meter', 'Tanggal Input', 'Status'];
            foreach ($data as $index => $item) {
                $exportData[] = [
                    $index + 1,
                    strtoupper($item->nama_material) . ' - ' . ($item->nomor_meter ?? '-'),
                    $item->stand_meter,
                    Carbon::parse($item->tanggal)->format('d-m-Y H:i'),
                    strtoupper($item->status)
                ];
            }
            
            $collection = new Collection($exportData);
            $exportClass = new class($collection) implements \Maatwebsite\Excel\Concerns\FromCollection {
                protected $collection;
                public function __construct($collection) { $this->collection = $collection; }
                public function collection() { return $this->collection; }
            };

            return Excel::download($exportClass, 'material-siaga-' . now()->format('Ymd_His') . '.xlsx');
        }
    }
}