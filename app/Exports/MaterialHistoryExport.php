<?php

namespace App\Exports;

use App\Models\MaterialHistory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class MaterialHistoryExport implements FromQuery, WithMapping, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = MaterialHistory::query();

        if ($this->request->filled('search')) {
            $query->where('nama_material', 'like', "%{$this->request->search}%");
        }
        if ($this->request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_input', '>=', $this->request->tanggal_mulai);
        }
        if ($this->request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_input', '<=', $this->request->tanggal_akhir);
        }

        return $query->orderBy('tanggal_input', 'desc');
    }

    // Header Kolom di Excel
    public function headings(): array
    {
        return [
            'No',
            'Nama Material',
            'Jumlah',
            'Satuan',
            'Tanggal (WITA)'
        ];
    }

    // Mapping Data (Memecah kolom jumlah & satuan)
    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            strtoupper($item->nama_material),
            ltrim($item->jumlah, '+ '), // Hapus tanda +
            strtoupper($item->satuan),
            Carbon::parse($item->tanggal_input)->format('d/m/Y H:i')
        ];
    }
}