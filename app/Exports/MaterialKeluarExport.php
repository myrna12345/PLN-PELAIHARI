<?php

namespace App\Exports;

use App\Models\MaterialKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaterialKeluarExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalAkhir;

    /**
     * Konstruktor untuk menerima tanggal awal dan akhir
     */
    public function __construct($tanggalMulai, $tanggalAkhir)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    /**
     * Mengambil data material keluar berdasarkan rentang tanggal
     */
    public function collection()
    {
        return MaterialKeluar::whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc')
            ->get([
                'nama_material',
                'nama_petugas',
                'jumlah_material',
                'tanggal',
            ]);
    }

    /**
     * Menentukan judul kolom pada file Excel
     */
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