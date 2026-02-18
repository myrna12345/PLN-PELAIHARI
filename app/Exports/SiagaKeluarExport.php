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
            'Nama Material & Nomor Meter', 
            'Nama Petugas',
            'Stand Meter', 
            'Status',
            'Keterangan', 
            'Tanggal (WITA)',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        
        // --- PERBAIKAN DI SINI ---
        // Mengambil nama material dari relasi atau field nama_material_lengkap
        $namaMaterial = $item->nama_material_lengkap ?? ($item->material->nama_material ?? 'N/A');
        
        // Pastikan memanggil field 'nomor_meter' sesuai dengan yang disimpan di database
        $nomorMeter = $item->nomor_meter; 
        
        // Gabungkan Nama Material dan Nomor Meter
        $namaMaterialNomorMeter = $namaMaterial . ($nomorMeter ? ' - ' . $nomorMeter : '');
        // -------------------------
        
        return [
            $this->rowNumber,
            $namaMaterialNomorMeter,
            $item->nama_petugas,
            $item->stand_meter ?? '-',
            $item->status,
            $item->keterangan,
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