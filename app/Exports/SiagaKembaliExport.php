<?php

namespace App\Exports;

use App\Models\SiagaKembali;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SiagaKembaliExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tanggalMulai;
    protected $tanggalAkhir;
    private $rowNumber = 0;

    public function __construct($tanggalMulai, $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function query()
    {
        return SiagaKembali::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Nama Petugas',
            'Stand Meter',
            'Jumlah Siaga Keluar',    // Kolom Baru
            'Jumlah Siaga Kembali',   // Kolom Baru
            'Status',                 // Mengganti Keterangan -> Status
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $item->material->nama_material ?? 'N/A',
            $item->nama_petugas,
            $item->stand_meter ?? '-',
            $item->jumlah_siaga_keluar,   // Mengambil dari Accessor (Relasi)
            $item->jumlah_siaga_kembali,  // Mengambil dari Database
            $item->status ?? 'Kembali',   // Mengambil Status
            Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}