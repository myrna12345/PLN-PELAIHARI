<?php

namespace App\Exports;

use App\Models\MaterialStandBy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MaterialStandByExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalAkhir;
    private $rowNumber = 0;

    // 1. Menerima tanggal dari controller (sekarang dalam format datetime)
    public function __construct(string $tanggalMulai, string $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai; // cth: '2025-11-06 00:00:00'
        $this->tanggalAkhir = $tanggalAkhir; // cth: '2025-11-15 23:59:59'
    }

    // 2. Mengambil data dari database
    public function query()
    {
        // Query ini sekarang sudah benar
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
            'Tanggal (WITA)',
        ];
    }

    // 4. Memetakan data per baris
    public function map($item): array
    {
        $this->rowNumber++;

        // Konversi jam UTC ke WITA (Asia/Makassar)
        $tanggalWita = $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i');

        return [
            $this->rowNumber, // Nomor urut yang benar
            $item->material->nama_material ?? 'N/A',
            $item->nama_petugas,
            $item->jumlah,
            $tanggalWita,
        ];
    }
}