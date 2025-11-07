<?php

namespace App\Exports;

use App\Models\MaterialKembali;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaterialKembaliExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $mulai;
    protected $akhir;

    public function __construct($mulai, $akhir)
    {
        $this->mulai = $mulai;
        $this->akhir = $akhir;
    }

    public function collection()
    {
        return MaterialKembali::whereBetween('tanggal', [$this->mulai, $this->akhir])
            ->orderBy('tanggal', 'asc')
            ->get([
                'nama_material',
                'nama_petugas',
                'jumlah_material',
                'tanggal',
            ]);
    }

    public function headings(): array
    {
        return [
            'Nama Material',
            'Nama Petugas',
            'Jumlah / Unit',
            'Tanggal (WITA)',
        ];
    }
}