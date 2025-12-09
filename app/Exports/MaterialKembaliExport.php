<?php

namespace App\Exports;

use App\Models\MaterialKembali;
use App\Models\MaterialStandBy; // 🟢 IMPORT MODEL BARU: Untuk mengambil data stok saat ini
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class MaterialKembaliExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $mulai;
    protected $akhir;

    public function __construct($mulai, $akhir)
    {
        $this->mulai = $mulai;
        $this->akhir = $akhir;
    }

    /**
     * Mengambil data material kembali, menggabungkan Jumlah dan Satuan, lalu memformat tanggal.
     */
    public function collection(): Collection
    {
        // 🛠️ PERBAIKAN: Gunakan with('material') untuk memuat data relasi Material
        $materialKembali = MaterialKembali::with('material')
            ->whereBetween('tanggal', [$this->mulai, $this->akhir])
            ->orderBy('tanggal', 'asc')
            ->get(); 
        
        return $materialKembali->map(function ($item) {
            
            // ✅ PERBAIKAN NAMA MATERIAL: Ambil nama material dari relasi material->nama_material
            $namaMaterial = $item->material->nama_material ?? '-';
            
            // ✅ PERBAIKAN SATUAN: Gabungkan jumlah_material dan satuan
            $jumlahSatuan = $item->jumlah_material . ' ' . $item->satuan;

            // Format tanggal ke zona waktu WITA (Asia/Makassar)
            $tanggalWITA = Carbon::parse($item->tanggal)
                ->setTimezone('Asia/Makassar')
                ->format('d M Y, H:i');

            // 🟢 PENAMBAHAN LOGIKA STOK SAAT INI 🟢
            // Ambil data stok terbaru dari MaterialStandBy
            $materialStok = MaterialStandBy::where('material_id', $item->material_id)->first();
            
            // Format stok (jumlah dan satuan)
            $stokSaatIni = $materialStok 
                ? $materialStok->jumlah . ' ' . $materialStok->satuan 
                : '0';
            // ------------------------------------------

            return [
                $namaMaterial,
                $item->nama_petugas,
                $jumlahSatuan, 
                $stokSaatIni,       // 🟢 TAMBAH DATA STOK
                $tanggalWITA,
            ];
        });
    }

    /**
     * Menentukan judul kolom pada file Excel
     */
    public function headings(): array
    {
        return [
            'Nama Material',
            'Nama Petugas',
            'Jumlah', 
            'Stok', // 🟢 TAMBAH JUDUL KOLOM STOK
            'Tanggal (WITA)',
        ];
    }
}