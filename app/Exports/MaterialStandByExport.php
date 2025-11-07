<?php

namespace App\Exports;

use App\Models\MaterialStandBy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Untuk membuat kolom otomatis lebar

class MaterialStandByExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalAkhir;

    // 1. Menerima tanggal dari controller
    public function __construct(string $tanggalMulai, string $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    // 2. Mengambil data dari database
    public function query()
    {
        return MaterialStandBy::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc');
    }

    // 3. Menulis judul kolom (header)
    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Nama Petugas',
            'Jumlah',
            'Tanggal (WITA)', // Samakan dengan PDF
        ];
    }

    // 4. Memetakan data per baris
    public function map($item): array
    {
        // Konversi jam UTC ke WITA (Asia/Makassar)
        $tanggalWita = $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i');

        return [
            $item->id, // Ganti ini dengan $loop->index jika Anda mau
            $item->material->nama_material ?? 'N/A',
            $item->nama_petugas,
            $item->jumlah,
            $tanggalWita,
        ];
    }
}