<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class MaterialHistoryExport implements FromCollection, WithMapping, WithHeadings
{
    protected $data;

    // Menerima koleksi data hasil filter dari Controller
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Jumlah',
            'Satuan',
            'Tanggal Input (WITA)'
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            strtoupper($item->nama_material),
            ltrim($item->jumlah, '+ '), // Menghapus tanda + agar menjadi angka murni
            strtoupper($item->satuan),
            Carbon::parse($item->tanggal_input)->format('d/m/Y H:i')
        ];
    }
}