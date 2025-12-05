<?php

namespace App\Exports;

use App\Models\MaterialKembali;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection; // Import Collection

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
        $materialKembali = MaterialKembali::whereBetween('tanggal', [$this->mulai, $this->akhir])
            ->orderBy('tanggal', 'asc')
            ->get([
                'nama_material',
                'nama_petugas',
                'jumlah_material',
                'satuan_material', // 🟢 PENAMBAHAN: Ambil kolom satuan
                'tanggal',
            ]);

        // 🟢 PERBAIKAN: Gunakan map() untuk memanipulasi collection (menggabungkan kolom)
        return $materialKembali->map(function ($item) {
            // Gabungkan jumlah_material dan satuan_material
            $jumlahSatuan = $item->jumlah_material . ' ' . $item->satuan_material;

            // Format tanggal ke zona waktu WITA (Asia/Makassar)
            $tanggalWITA = \Carbon\Carbon::parse($item->tanggal)
                ->setTimezone('Asia/Makassar')
                ->format('d M Y, H:i');

            return [
                $item->nama_material,
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
        // 🟢 PERBAIKAN: Judul kolom disesuaikan dengan urutan di collection()
        return [
            'Nama Material',
            'Nama Petugas',
            'Jumlah', // 🟢 PENYESUAIAN: Judul kolom diganti menjadi 'Jumlah'
            'Tanggal (WITA)',
        ];
    }
}