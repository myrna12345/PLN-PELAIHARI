<?php

namespace App\Exports;

use App\Models\MaterialRetur;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MaterialReturExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalAkhir;
    private $rowNumber = 0;

    public function __construct(string $tanggalMulai, string $tanggalAkhir)
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
            'Jumlah Keluar',
            'Jumlah Kembali',
            'Status',
            'Keterangan',
            'Tanggal (WITA)',
        ];
    }

    /**
     * Memetakan data per baris.
     * $item adalah data dari database.
     */
    public function map($item): array
    {
        $this->rowNumber++;
        $tanggalWita = $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i');

        return [
            // ==============================================
            // === PERBAIKAN TYPO ADA DI BARIS INI ===
            // ==============================================
            $this->rowNumber,
            
            $item->material->nama_material ?? 'N/A',
            $item->nama_petugas,
            $item->jumlah,
            $item->material_keluar ?? 0,
            $item->material_kembali ?? 0,
            $item->status == 'baik' ? 'Baik' : 'Rusak',
            $item->keterangan,
            $tanggalWita,
        ];
    }
}