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
    // --- 1. INDEX (Menampilkan Daftar Data) ---

    public function index(Request $request)
    {
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $dataSiaga = MaterialSiagaStandBy::query()
            ->when($search, function ($query) use ($search) {
                // Pencarian berdasarkan nama material dan nomor meter (sesuai view terakhir)
                $query->where('nama_material', 'like', "%{$search}%")
                      ->orWhere('nomor_meter', 'like', "%{$search}%"); 
            })
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                // Filter berdasarkan rentang tanggal
                $query->whereBetween('tanggal', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay()
                ]);
            })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString(); // Mempertahankan filter saat pagination

        return view('material-siaga.index', compact('dataSiaga', 'search', 'start_date', 'end_date'));
    }

    // --- 2. CREATE (Menampilkan Form Tambah Data) ---

    public function create()
    {
        // Ambil hanya material dengan kategori 'siaga' untuk dropdown form
        $materials = Material::where('kategori', 'siaga')->get();

        return view('material-siaga.create', compact('materials'));
    }

    // --- 3. STORE (Menyimpan Data Baru) ---

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_material'         => 'required|string',
            'nomor_meter'           => 'required|string|max:50',      // Required, sesuai form StandBy
            'stand_meter'           => 'required|string|max:100',
            'tanggal'               => 'required|date',
            
            // Dibuat nullable karena tidak terlihat di form StandBy
            'nama_petugas'          => 'nullable|string|max:255',
            'jumlah_siaga_standby'  => 'nullable|integer|min:0', 
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Handle upload foto
        $path = null;
        if ($request->hasFile('unggah_foto')) {
            $path = $request->file('unggah_foto')->store('foto_siaga', 'public');
        }

        MaterialSiagaStandBy::create([
            'nama_material'         => $validatedData['nama_material'],
            'nomor_meter'           => $validatedData['nomor_meter'],
            'stand_meter'           => $validatedData['stand_meter'],
            'tanggal'               => $validatedData['tanggal'],
            
            // Data opsional atau default
            'nama_petugas'          => $validatedData['nama_petugas'] ?? null, 
            'jumlah_siaga_standby'  => $validatedData['jumlah_siaga_standby'] ?? 1, // Default 1
            'unggah_foto'           => $path,
            'status'                => 'Ready',
        ]);

        return redirect()->route('material-siaga.index')->with('success', 'Data Material Siaga StandBy berhasil disimpan!');
    }
    
    // --- 4. EDIT (Menampilkan Form Edit) ---

   public function edit($id)
{
    $material = MaterialSiagaStandBy::findOrFail($id);
    return view('material-siaga.edit', compact('material'));
}

    // --- 5. UPDATE (Memperbarui Data) ---

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_material'         => 'required|string',
            'nomor_meter'           => 'required|string|max:50',
            'stand_meter'           => 'required|string|max:100',
            'tanggal'               => 'required|date',
            'status'                => 'required|in:Ready,Terpakai',
            
            'nama_petugas'          => 'nullable|string|max:255',
            'jumlah_siaga_standby'  => 'nullable|integer|min:0',
            'unggah_foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = MaterialSiagaStandBy::findOrFail($id);

        // Handle upload foto baru dan hapus yang lama
        $path = $data->unggah_foto;
        if ($request->hasFile('unggah_foto')) {
            // Hapus foto lama jika ada
            if ($data->unggah_foto) {
                Storage::disk('public')->delete($data->unggah_foto);
            }
            // Simpan foto baru
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

        return redirect()->route('material-siaga.index')->with('success', 'Data Material Siaga StandBy berhasil diperbarui!');
    }
    
    // --- 6. DESTROY (Menghapus Data) ---

    public function destroy($id)
    {
        $item = MaterialSiagaStandBy::findOrFail($id);

        // Hapus foto terkait sebelum menghapus record
        if ($item->unggah_foto) {
            Storage::disk('public')->delete($item->unggah_foto);
        }

        $item->delete();

        return redirect()->route('material-siaga.index')->with('success', 'Data Material Siaga StandBy berhasil dihapus!');
    }
    
    // --- 7. UPDATE STATUS (Aksi Cepat di Halaman Index) ---
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Ready,Terpakai'
        ]);
        
        $item = MaterialSiagaStandBy::findOrFail($id);
        $item->status = $request->status;
        $item->save();

        return back()->with('success', 'Status berhasil diperbarui!');
    }
    
    // --- 8. EXPORT (PDF & Excel) ---

    // --- 8. EXPORT (PDF & Excel) ---

    public function export(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $exportType = $request->input('export');

        if (!$start_date || !$end_date) {
            return back()->with('error', 'Pilih rentang tanggal untuk ekspor.');
        }

        $start = Carbon::parse($start_date)->startOfDay();
        $end = Carbon::parse($end_date)->endOfDay();

        $data = MaterialSiagaStandBy::whereBetween('tanggal', [$start, $end])
            ->orderBy('id', 'DESC')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data dalam rentang tanggal tersebut.');
        }
        
        // ========== EXPORT PDF ==========
        if ($exportType === 'pdf') {
            $pdf = Pdf::loadView(
                'material-siaga.export_pdf',
                compact('data', 'start_date', 'end_date')
            )->setPaper('a4', 'portrait');

            return $pdf->download('material-siaga-' . Carbon::now()->format('Ymd_His') . '.pdf');
        }

        // ========== EXPORT EXCEL ==========
        if ($exportType === 'excel') {
            $exportData = [];

            // Header Excel disesuaikan dengan tabel (Tanpa Nama Petugas & Jumlah)
            $exportData[] = [
                'No', 
                'Nama Material & Nomor Meter', 
                'Stand Meter', 
                'Tanggal Input', 
                'Status'
            ];

            // Data Baris
            foreach ($data as $index => $item) {
                $exportData[] = [
                    $index + 1,
                    // Menggabungkan Nama Material & Nomor Meter (Kapital)
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

            return Excel::download(
                $exportClass,
                'material-siaga-' . Carbon::now()->format('Ymd_His') . '.xlsx'
            );
        }

        return back()->with('error', 'Jenis export tidak dikenali.');
    }
}