<?php

namespace App\Exports;

use App\Models\MaterialKeluar;
use App\Models\MaterialStandBy; // 🟢 IMPORT MODEL BARU: Untuk mengambil data stok saat ini
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Carbon\Carbon; // Pastikan Carbon diimpor jika belum

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
     * Mengambil data material keluar, menggabungkan Jumlah dan Satuan, lalu memformat tanggal.
     */
    public function collection(): Collection
    {
        // 🛠️ PERBAIKAN: Gunakan with('material') untuk memuat data relasi Material
        $materialKeluar = MaterialKeluar::with('material')
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir])
            ->orderBy('tanggal', 'asc')
            ->get(); // Hapus seleksi kolom agar relasi dapat diakses dengan mudah

        // 🟢 PERBAIKAN: Gunakan map() untuk memanipulasi collection (menggabungkan kolom)
        return $materialKeluar->map(function ($item) {
            
            // Gabungkan jumlah_material dan satuan_material
            $jumlahSatuan = $item->jumlah_material . ' ' . $item->satuan_material;

            // Format tanggal ke zona waktu WITA
            $tanggalWITA = Carbon::parse($item->tanggal)
                ->setTimezone('Asia/Makassar')
                ->format('d M Y, H:i');

            // 🛠️ PERBAIKAN: Ambil nama material dari relasi material->nama_material
            $namaMaterial = $item->material->nama_material ?? '-';
            
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
                $stokSaatIni, // 🟢 TAMBAH DATA STOK
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