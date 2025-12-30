<?php

namespace App\Exports;

use App\Models\SiagaKeluar;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SiagaKeluarExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        return SiagaKeluar::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            // KEKAL: Nama Material & Nomor Meter (Gabungan)
            'Nama Material & Nomor Meter', 
            'Nama Petugas',
            'Stand Meter', // PERBAIKAN: Ditampilkan kembali sebagai kolom terpisah
            // DIHAPUS: 'Jumlah Siaga Keluar', 
            // DIHAPUS: 'Jumlah Siaga Masuk',
            'Status',
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        
        // PERBAIKAN: Menggabungkan Nama Material dan NOMOR METER (field: nomor_unit)
        $namaMaterialNomorMeter = $item->material->nama_material ?? 'N/A';
        if ($item->nomor_unit) { // Menggunakan nomor_unit untuk Nomor Meter
            // HILANGKAN KATA 'UNIT'
            $namaMaterialNomorMeter .= ' - ' . $item->nomor_unit;
        }
        
        return [
            $this->rowNumber,
            $namaMaterialNomorMeter, // Data gabungan
            $item->nama_petugas,
            $item->stand_meter ?? '-', // KEKAL: Stand Meter sebagai kolom terpisah
            // DIHAPUS: $item->jumlah_siaga_keluar,
            // DIHAPUS: $item->jumlah_siaga_masuk ?? 0,
            $item->status,
            Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header tebal
        ];
    }
}