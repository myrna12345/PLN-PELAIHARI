<?php

namespace App\Http\Controllers;

use App\Models\MaterialSiagaStandBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use App\Models\Material;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Log;

class MaterialSiagaStandByController extends Controller
{
    private $uploadFolder = 'uploads/material_siaga';

    public function index(Request $request)
    {
        // Blokir akses Harmet ke halaman laporan
        if (strtolower(auth()->user()->role) === 'harmet') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak diperbolehkan melihat laporan.');
        }

        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = MaterialSiagaStandBy::query();

        if ($search) {
            $query->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('nomor_meter', 'like', "%{$search}%"); 
        }

        if ($start_date && $end_date) {
            $query->whereBetween('tanggal', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ]);
        }

        // PERBAIKAN: Menggunakan simplePaginate agar hanya muncul tombol Previous & Next
        $dataSiaga = $query->orderBy('id', 'DESC')->simplePaginate(10)->withQueryString();

        return view('material-siaga.index', compact('dataSiaga', 'search', 'start_date', 'end_date'));
    }

    public function create()
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam'])) {
            return redirect()->route('material-siaga-stand-by.index')->with('error', 'Akses ditolak.');
        }

        $materials = Material::where('kategori', 'siaga')->get();
        return view('material-siaga.create', compact('materials'));
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '1024M'); 

        $validatedData = $request->validate([
            'nama_material'         => 'required|string',
            'nomor_meter'           => 'required|string|max:50',
            'stand_meter'           => 'required|string|max:100',
            'tanggal'               => 'required|date',
            'nama_petugas'          => 'nullable|string|max:255',
            'jumlah_siaga_standby'  => 'nullable|integer|min:0', 
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg,heic,webp|max:15360', 
        ]);
        
        $fotoName = null;
        if ($request->hasFile('unggah_foto')) {
            $path = public_path($this->uploadFolder);
            
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }

            $fotoName = time() . '_' . uniqid() . '.jpg';
            $imageFile = $request->file('unggah_foto');
            
            $img = Image::read($imageFile); 
            $img->scale(width: 1200);    
            $img->toJpeg(60)->save($path . '/' . $fotoName);
        }

        MaterialSiagaStandBy::create([
            'nama_material'         => $validatedData['nama_material'],
            'nomor_meter'           => $validatedData['nomor_meter'],
            'stand_meter'           => $validatedData['stand_meter'],
            'tanggal'               => $validatedData['tanggal'],
            'nama_petugas'          => $validatedData['nama_petugas'] ?? null, 
            'jumlah_siaga_standby'  => $validatedData['jumlah_siaga_standby'] ?? 1,
            'unggah_foto'           => $fotoName,
            'status'                => 'Ready',
        ]);

        if (strtolower(auth()->user()->role) === 'harmet') {
            return redirect()->route('dashboard')->with('success', 'Data berhasil disimpan!');
        }

        return redirect()->route('material-siaga-stand-by.index')->with('success', 'Data berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        ini_set('memory_limit', '1024M');
        $data = MaterialSiagaStandBy::findOrFail($id);

        $request->validate([
            'nama_material' => 'required',
            'nomor_meter'   => 'required',
            'stand_meter'   => 'required',
            'status'        => 'required|in:Ready,Terpakai,READY,TERPAKAI',
            'unggah_foto'   => 'nullable|image|mimes:jpeg,png,jpg,heic,webp|max:15360', 
        ]);

        $fotoName = $data->unggah_foto;

        try {
            if ($request->hasFile('unggah_foto')) {
                $path = public_path($this->uploadFolder);
                if ($fotoName && File::exists($path . '/' . $fotoName)) {
                    File::delete($path . '/' . $fotoName);
                }

                $fotoName = time() . '_' . uniqid() . '.jpg';
                $imageFile = $request->file('unggah_foto');
                $img = Image::read($imageFile);
                $img->scale(width: 1200); 
                $img->toJpeg(60)->save($path . '/' . $fotoName);
            }

            $data->update([
                'nama_material' => $request->nama_material,
                'nomor_meter'   => $request->nomor_meter,
                'stand_meter'   => $request->stand_meter,
                'status'        => ucwords(strtolower($request->status)),
                'unggah_foto'   => $fotoName,
            ]);

            return redirect()->route('material-siaga-stand-by.index')->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error("Update Material Siaga Standby Error: " . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui data atau foto.');
        }
    }
    
    public function edit($id)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('material-siaga-stand-by.index')->with('error', 'Akses ditolak.');
        }
        $materialSiaga = MaterialSiagaStandBy::findOrFail($id);
        return view('material-siaga.edit', compact('materialSiaga'));
    }

    public function showFoto($id) 
    {
        $item = MaterialSiagaStandBy::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->unggah_foto);
        
        if (!$item->unggah_foto || !File::exists($path)) {
            abort(404, 'File foto tidak ditemukan.');
        }
        
        return response()->file($path);
    }

    public function downloadFoto($id)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $item = MaterialSiagaStandBy::findOrFail($id);
        $path = public_path($this->uploadFolder . '/' . $item->unggah_foto);
        
        if ($item->unggah_foto && File::exists($path)) {
            return response()->download($path);
        }
        
        return redirect()->back()->with('error', 'File foto tidak ditemukan.');
    }

    public function destroy($id)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('material-siaga-stand-by.index')->with('error', 'Akses ditolak.');
        }

        $item = MaterialSiagaStandBy::findOrFail($id);
        if ($item->unggah_foto) {
            $path = public_path($this->uploadFolder . '/' . $item->unggah_foto);
            if (File::exists($path)) { File::delete($path); }
        }

        $item->delete();
        return redirect()->route('material-siaga-stand-by.index')->with('success', 'Data berhasil dihapus!');
    }

    public function updateStatus(Request $request, $id)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->route('material-siaga-stand-by.index')->with('error', 'Akses ditolak.');
        }

        $request->validate(['status' => 'required|in:Ready,Terpakai']);
        $item = MaterialSiagaStandBy::findOrFail($id);
        $item->status = $request->status;
        $item->save();
        return back()->with('success', 'Status berhasil diperbarui!');
    }
    
    public function export(Request $request)
    {
        if (in_array(strtolower(auth()->user()->role), ['satpam', 'harmet'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $exportType = $request->input('export');

        if (!$start_date || !$end_date) { return back()->with('error', 'Pilih rentang tanggal.'); }

        $start = Carbon::parse($start_date)->startOfDay();
        $end = Carbon::parse($end_date)->endOfDay();
        $data = MaterialSiagaStandBy::whereBetween('tanggal', [$start, $end])->orderBy('id', 'DESC')->get();

        if ($data->isEmpty()) { return back()->with('error', 'Data kosong.'); }
        
        if ($exportType === 'pdf') {
            foreach ($data as $item) {
                $path = public_path($this->uploadFolder . '/' . $item->unggah_foto);
                if ($item->unggah_foto && File::exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($path);
                    $item->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                } else { $item->foto_base64 = null; }
            }
            $pdf = Pdf::loadView('material-siaga.export_pdf', compact('data', 'start_date', 'end_date'))->setPaper('a4', 'portrait');
            return $pdf->download('material-siaga-' . now()->format('Ymd_His') . '.pdf');
        }

        if ($exportType === 'excel') {
            $exportData[] = ['No', 'Nama Material & Nomor Meter', 'Stand Meter', 'Tanggal Input', 'Status'];
            foreach ($data as $index => $item) {
                $exportData[] = [$index + 1, strtoupper($item->nama_material) . ' - ' . ($item->nomor_meter ?? '-'), $item->stand_meter, Carbon::parse($item->tanggal)->format('d-m-Y H:i'), strtoupper($item->status)];
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