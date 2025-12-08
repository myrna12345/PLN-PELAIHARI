<?php

namespace App\Exports;

use App\Models\MaterialKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class MaterialKeluarExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalAkhir;

    /**
     * Konstruktor untuk menerima tanggal awal dan akhir
     */
    public function __construct($tanggalMulai, $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    /**
     * Mengambil data material keluar, menggabungkan Jumlah dan Satuan, lalu memformat tanggal.
     */
    public function collection(): Collection
    {
        // 🛠️ PERBAIKAN: Gunakan with('material') untuk memuat data relasi Material
        $materialKeluar = MaterialKeluar::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc')
            // Ambil kolom yang ada di tabel material_keluar
            ->get([
                'material_id', // Diperlukan untuk mengakses relasi
                'nama_petugas',
                'jumlah_material',
                'satuan_material',
                'tanggal',
            ]);

        // 🟢 PERBAIKAN: Gunakan map() untuk memanipulasi collection (menggabungkan kolom)
        return $materialKeluar->map(function ($item) {
            // Gabungkan jumlah_material dan satuan_material
            $jumlahSatuan = $item->jumlah_material . ' ' . $item->satuan_material;

            // Format tanggal ke zona waktu WITA
            $tanggalWITA = \Carbon\Carbon::parse($item->tanggal)
                ->setTimezone('Asia/Makassar')
                ->format('d M Y, H:i');

            // 🛠️ PERBAIKAN: Ambil nama material dari relasi material->nama_material
            $namaMaterial = $item->material->nama_material ?? '-';

            return [
                $namaMaterial, // Data Nama Material dari relasi
                $item->nama_petugas,
                $jumlahSatuan, // Kolom yang sudah digabungkan
                $tanggalWITA,
            ];
        });
    }

    /**
     * Menentukan judul kolom pada file Excel
     */
    public function headings(): array
    {
        // 🟢 Judul kolom disesuaikan dengan urutan di collection() (Tidak ada perubahan di sini)
        return [
            'Nama Material',
            'Nama Petugas',
            'Jumlah',
            'Tanggal (WITA)',
        ];
    }
}