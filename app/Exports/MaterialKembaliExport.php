<?php

namespace App\Exports;

use App\Models\MaterialKembali;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection; // Import Collection
use Carbon\Carbon; // Import Carbon untuk kejelasan

class MaterialKembaliExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $mulai;
    protected $akhir;

    public function __construct($mulai, $akhir)
    {
        $this->mulai = $mulai;
        $this->akhir = $akhir;
    }

    /**
     * Mengambil data material kembali, menggabungkan Jumlah dan Satuan, lalu memformat tanggal.
     */
    public function collection(): Collection
    {
        // 🛠️ PERBAIKAN: Gunakan with('material') untuk memuat data relasi Material
        $materialKembali = MaterialKembali::with('material')
            ->whereBetween('tanggal', [$this->mulai, $this->akhir])
            ->orderBy('tanggal', 'asc')
            ->get(); 
            // 💡 Catatan: Menghapus get([...]) agar relasi material dapat diakses dengan mudah

        return $materialKembali->map(function ($item) {
            
            // ✅ PERBAIKAN NAMA MATERIAL: Ambil nama material dari relasi material->nama_material
            // Menggunakan null coalescing operator untuk fallback jika relasi gagal dimuat
            $namaMaterial = $item->material->nama_material ?? '-';
            
            // ✅ PERBAIKAN SATUAN: Gabungkan jumlah_material dan satuan (menggunakan $item->satuan sesuai model yang diunggah)
            $jumlahSatuan = $item->jumlah_material . ' ' . $item->satuan;

            // Format tanggal ke zona waktu WITA (Asia/Makassar)
            $tanggalWITA = Carbon::parse($item->tanggal)
                ->setTimezone('Asia/Makassar')
                ->format('d M Y, H:i');

            return [
                $namaMaterial,      // Data Nama Material dari relasi
                $item->nama_petugas,
                $jumlahSatuan,      // Kolom yang sudah digabungkan
                $tanggalWITA,
            ];
        });
    }

    /**
     * Menentukan judul kolom pada file Excel
     */
    public function headings(): array
    {
        // Judul kolom sudah benar dan sesuai dengan urutan di collection()
        return [
            'Nama Material',
            'Nama Petugas',
            'Jumlah', // Kolom gabungan Jumlah dan Satuan
            'Tanggal (WITA)',
        ];
    }
}