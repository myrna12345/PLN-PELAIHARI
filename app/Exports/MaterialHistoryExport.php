<?php

namespace App\Exports;

use App\Models\MaterialHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MaterialHistoryExport implements FromCollection, WithMapping, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = MaterialHistory::query();

        if ($this->request->filled('search')) {
            $query->where('nama_material', 'like', '%' . $this->request->search . '%');
        }
        if ($this->request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_input', '>=', $this->request->tanggal_mulai);
        }
        if ($this->request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_input', '<=', $this->request->tanggal_akhir);
        }

        return $query->orderBy('tanggal_input', 'desc')->get();
    }

    // KUNCI PERBAIKAN: Pastikan $row dibaca sebagai objek
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nama_material, // Baris 30 yang tadi error
            '+ ' . $row->jumlah . ' ' . $row->satuan,
            \Carbon\Carbon::parse($row->tanggal_input)->format('d/m/Y H:i') . ' WITA',
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA MATERIAL',
            'JUMLAH',
            'TANGGAL INPUT',
        ];
    }
}