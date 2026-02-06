<?php

namespace App\Exports;

use App\Models\MaterialStandBy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar lebar kolom otomatis rapi
use Carbon\Carbon;

class MaterialStandByExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $row = 0;
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    public function query()
    {
        // Gunakan startOfDay dan endOfDay agar data hari terakhir terbawa semua
        // (Misal: dari jam 00:00 sampai 23:59)
        $startDate = Carbon::parse($this->start)->startOfDay();
        $endDate   = Carbon::parse($this->end)->endOfDay();

        return MaterialStandBy::query()
            ->with('material')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->latest('tanggal'); // Urutkan dari yang terbaru (sesuai tampilan web)
    }

    /**
     * Judul Kolom (Header) Excel
     * Disesuaikan dengan th di index.blade.php
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Jumlah',         // Di web: "10 Buah" (digabung)
            'Tanggal (WITA)', // Di web: format "d M Y, H:i"
        ];
    }

    /**
     * Isi Data Per Baris
     * Disesuaikan dengan td di index.blade.php
     */
    public function map($row): array
    {
        return [
            ++$this->row,
            
            // Nama Material
            $row->material->nama_material ?? '-',
            
            // Jumlah & Satuan digabung agar sama persis dengan tampilan web
            $row->jumlah . ' ' . $row->satuan, 
            
            // Format Tanggal disamakan (Contoh: 05 Feb 2026, 14:30)
            Carbon::parse($row->tanggal)->format('d M Y, H:i'),
        ];
    }
}