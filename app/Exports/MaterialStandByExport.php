<?php

namespace App\Exports;

use App\Models\MaterialStandBy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialStandByExport implements FromQuery, WithHeadings, WithMapping
{
    private $row = 0;
    protected $start, $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    public function query()
    {
        return MaterialStandBy::with('material')
            ->whereBetween('tanggal', [$this->start, $this->end]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Jumlah',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        return [
            ++$this->row,
            $row->material->nama_material ?? '-',
            $row->jumlah,
            $row->tanggal,
        ];
    }
}
