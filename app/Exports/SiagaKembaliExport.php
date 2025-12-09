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
            'Nama Material & Nomor Meter', // PERBAIKAN HEADING: Diubah menjadi 'Nomor Meter'
            'Nama Petugas',
            'Stand Meter',
            'Keterangan', // <--- TAMBAHAN: KOLOM HEADER KETERANGAN
            // HAPUS: 'Jumlah Siaga Keluar',
            // HAPUS: 'Jumlah Siaga Kembali',
            'Status',
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        
        // CATATAN PENTING: Mengganti 'nomor_unit' menjadi 'nomor_meter'
        // Jika Anda telah memperbarui database Anda, maka field yang benar adalah 'nomor_meter'.
        $namaMaterialUnit = ($item->material->nama_material ?? 'N/A') . 
                             ($item->nomor_meter ? ' - ' . $item->nomor_meter : ''); // <--- DIGANTI KE nomor_meter

        return [
            $this->rowNumber,
            $namaMaterialUnit, 
            $item->nama_petugas,
            $item->stand_meter ?? '-',
            $item->keterangan, // <--- TAMBAHAN: DATA KETERANGAN
            // HAPUS DATA: $item->jumlah_siaga_keluar,
            // HAPUS DATA: $item->jumlah_siaga_kembali,
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