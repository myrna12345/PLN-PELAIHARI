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
            'Nama Material & Unit', // 🟢 PERBAIKAN: Menggabungkan Heading
            'Nama Petugas',
            'Stand Meter',
            'Jumlah Siaga Keluar',
            'Jumlah Siaga Kembali',
            'Status',
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        
        // 🟢 PERBAIKAN: Menggabungkan Nama Material dan Nomor Unit
        $namaMaterialUnit = ($item->material->nama_material ?? 'N/A') . 
                            ($item->nomor_unit ? ' - ' . $item->nomor_unit : '');

        return [
            $this->rowNumber,
            $namaMaterialUnit, // Menggunakan variabel gabungan
            $item->nama_petugas,
            $item->stand_meter ?? '-',
            $item->jumlah_siaga_keluar,
            $item->jumlah_siaga_kembali,
            $item->status ?? 'Kembali',
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