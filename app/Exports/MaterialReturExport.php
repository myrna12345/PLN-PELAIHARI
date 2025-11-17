<?php

namespace App\Exports;

use App\Models\MaterialRetur;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahan untuk styling header
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Tambahan untuk worksheet
use Carbon\Carbon;

class MaterialReturExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tanggalMulai;
    protected $tanggalAkhir;
    private $rowNumber = 0;

    // Terima objek Carbon
    public function __construct($tanggalMulai, $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function query()
    {
        return MaterialRetur::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Nama Petugas',
            'Jumlah Retur',
            'Status',
            'Keterangan',
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        // Format tanggal sesuai zona waktu WITA
        $tanggalWita = Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i');

        return [
            $this->rowNumber,
            $item->material->nama_material ?? 'N/A',
            $item->nama_petugas,
            $item->jumlah,
            $item->status == 'baik' ? 'Baik' : 'Rusak',
            $item->keterangan,
            $tanggalWita,
        ];
    }

    // Tambahan style: Header bold
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}