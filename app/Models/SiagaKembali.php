<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Tambahkan Import Model SiagaKeluar
use App\Models\SiagaKeluar;

class SiagaKembali extends Model
{
    use HasFactory;

    protected $table = 'siaga_kembalis';

    protected $fillable = [
        'material_id',
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_kembali', 
        'keterangan',
        'status',
        'tanggal',
        'foto_path',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Accessor untuk mengambil data 'jumlah_siaga_keluar' secara otomatis
     * Berdasarkan kecocokan Nama Petugas dan Material ID.
     * Mengambil data transaksi TERBARU.
     */
    public function getJumlahSiagaKeluarAttribute()
    {
        // Cari data di tabel Siaga Keluar
        $dataKeluar = SiagaKeluar::where('nama_petugas', $this->nama_petugas)
                        ->where('material_id', $this->material_id)
                        ->latest('tanggal') // Ambil yang paling baru
                        ->first();

        // Jika ketemu, kembalikan jumlahnya. Jika tidak, kembalikan 0.
        return $dataKeluar ? $dataKeluar->jumlah_siaga_keluar : 0;
    }
}