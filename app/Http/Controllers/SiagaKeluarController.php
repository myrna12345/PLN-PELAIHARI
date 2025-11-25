<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiagaKeluar;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; 

class SiagaKeluarController extends Controller
{
    /**
     * Halaman index
     */
    public function index(Request $request)
    {
        $query = SiagaKeluar::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Pencarian di tiga kolom
            $query->where('nama_petugas', 'like', "%$search%")
                  ->orWhere('stand_meter', 'like', "%$search%")
                  ->orWhere('nama_material', 'like', "%$search%");
        }

        // Mengambil data terbaru dan melakukan pagination
        $dataSiagaKeluar = $query->latest()->paginate(10);

        return view('siaga-keluar.index', compact('dataSiagaKeluar'));
    }

    /**
     * Halaman create
     */
    public function create()
    {
        // Variabel untuk dropdown Jumlah
        $oneP = range(1, 50);   // opsi 1P → 1–50
        $threeP = range(1, 10); // opsi 3P → 1–10

        // Daftar Tipe Material (1P dan 3P)
        $materialTypes = ['1P', '3P']; 

        return view('siaga-keluar.create', compact('oneP', 'threeP', 'materialTypes'));
    }

    /**
     * Store data (Logika penyimpanan material yang diperbaiki dan kuat)
     */
    public function store(Request $request)
    {
        // Batas validasi untuk jumlah 1P dan 3P
        $max1P = 50;
        $max3P = 10;
        
        // 1. Validasi semua input. Input 'material' wajib dan bertipe string.
        $validatedData = $request->validate([
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_keluar' => 'required|numeric|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|string', 
            'material' => 'required|string|max:20', // Input kunci dari form
        ]);
        
        $data = $validatedData;

        // --- PEMROSESAN MATERIAL: Logika Utama ---
        $materialInput = $request->input('material'); // Contoh: "1P-5"
        $materialParts = explode('-', $materialInput);
        
        // 1. Cek Format
        if (count($materialParts) !== 2 || ($materialParts[0] !== '1P' && $materialParts[0] !== '3P') || !is_numeric($materialParts[1])) {
             throw ValidationException::withMessages([
                'material' => 'Format material tidak valid. Harap pilih material yang sesuai dari daftar.',
            ]);
        }

        $materialType = $materialParts[0];
        $materialCount = (int) $materialParts[1];
        
        // 2. Cek Batas Jumlah
        if ($materialType == '1P' && $materialCount > $max1P) {
            throw ValidationException::withMessages([
                'material' => "Jumlah material 1P ({$materialCount}) tidak boleh lebih dari {$max1P}.",
            ]);
        } 
        if ($materialType == '3P' && $materialCount > $max3P) {
            throw ValidationException::withMessages([
                'material' => "Jumlah material 3P ({$materialCount}) tidak boleh lebih dari {$max3P}.",
            ]);
        }

        // 🔥 GABUNGKAN UNTUK DISIMPAN KE DB 🔥
        // Format yang disimpan: "1P - 5" (dengan spasi)
        $data['nama_material'] = "{$materialType} - {$materialCount}";
        
        // Hapus input 'material' asli (agar tidak bentrok dengan kolom DB)
        unset($data['material']);

        // --- PENGISIAN DATA LAIN ---
        $data['tanggal'] = Carbon::now();
        $data['status'] = $data['status'] ?? 'Keluar'; 
        
        // Upload foto
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        // 3. Simpan data, termasuk 'nama_material' yang sudah terisi
        SiagaKeluar::create($data);

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil ditambahkan.');
    }

    /**
     * Halaman edit (Diperbaiki untuk memproses input 'material' tunggal)
     */
    public function edit($id)
    {
        $siagaKeluar = SiagaKeluar::findOrFail($id);

        // Variabel untuk dropdown Jumlah
        $oneP = range(1, 50);
        $threeP = range(1, 10);
        
        // Daftar Tipe Material (1P dan 3P)
        $materialTypes = ['1P', '3P']; 

        // Pisahkan data 'nama_material' dari DB untuk ditampilkan di form edit
        // Format di DB: "1P - 5". Kita ubah ke "1P-5" untuk value di dropdown.
        $parts = explode(' - ', $siagaKeluar->nama_material ?? '');
        $siagaKeluar->material_dropdown_value = implode('-', $parts);


        return view('siaga-keluar.edit', compact('siagaKeluar', 'oneP', 'threeP', 'materialTypes'));
    }

    /**
     * Update data (Logika penyimpanan material yang diperbaiki dan kuat)
     */
    public function update(Request $request, $id)
    {
        $siagaKeluar = SiagaKeluar::findOrFail($id);
        
        // Batas validasi untuk jumlah 1P dan 3P
        $max1P = 50;
        $max3P = 10;

        // HILANGNYA TANDA KURUNG/TITIK KOMA DI SINI YANG MENYEBABKAN GARIS MERAH
        $validatedData = $request->validate([
            'nama_petugas' => 'required|string|max:255',
            'stand_meter' => 'required|string|max:255',
            'jumlah_siaga_keluar' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string', 
            
            // HANYA VALIDASI INPUT 'material' tunggal yang dikirim dari form
            'material' => 'required|string|max:20', 
        ]); 
        
        $data = $validatedData;

        // Ambil input 'material' dari request
        $materialInput = $request->input('material');

        // 🔥 LOGIKA PEMROSESAN MATERIAL LEBIH AMAN 🔥
        $materialParts = explode('-', $materialInput);
        
        // 1. Cek Format
        if (count($materialParts) !== 2 || ($materialParts[0] !== '1P' && $materialParts[0] !== '3P') || !is_numeric($materialParts[1])) {
             throw ValidationException::withMessages([
                'material' => 'Format material tidak valid. Harap pilih material yang sesuai dari daftar.',
            ]);
        }

        $materialType = $materialParts[0];
        $materialCount = (int) $materialParts[1];
        
        // 2. Cek Batas Jumlah
        if ($materialType == '1P' && $materialCount > $max1P) {
            throw ValidationException::withMessages([
                'material' => "Jumlah material 1P ({$materialCount}) tidak boleh lebih dari {$max1P}.",
            ]);
        } 
        if ($materialType == '3P' && $materialCount > $max3P) {
            throw ValidationException::withMessages([
                'material' => "Jumlah material 3P ({$materialCount}) tidak boleh lebih dari {$max3P}.",
            ]);
        }

        // 🔥 GABUNGKAN UNTUK DISIMPAN KE DB 🔥
        $data['nama_material'] = "{$materialType} - {$materialCount}";
        
        // Hapus input 'material' asli
        unset($data['material']);


        // Foto baru
        if ($request->hasFile('foto')) {
            if ($siagaKeluar->foto) {
                Storage::disk('public')->delete($siagaKeluar->foto);
            }

            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        $siagaKeluar->update($data);

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $siagaKeluar = SiagaKeluar::findOrFail($id);

        if ($siagaKeluar->foto) {
            Storage::disk('public')->delete($siagaKeluar->foto);
        }

        $siagaKeluar->delete();

        return redirect()->route('siaga-keluar.index')
                         ->with('success', 'Data Siaga Keluar berhasil dihapus.');
    }
}