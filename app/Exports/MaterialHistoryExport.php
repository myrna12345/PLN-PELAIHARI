<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaterialHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $items;

    public function __construct($items) {
        $this->items = $items;
    }

    public function collection() {
        return $this->items;
    }

    public function headings(): array {
        return ['NO', 'NAMA MATERIAL', 'JUMLAH', 'SATUAN', 'TANGGAL INPUT (WITA)'];
    }

    public function map($item): array {
        static $no = 1;
        return [
            $no++,
            strtoupper($item->nama_material),
            $item->jumlah,
            $item->satuan,
            \Carbon\Carbon::parse($item->tanggal_input)->format('d/m/Y H:i')
        ];
    }
}