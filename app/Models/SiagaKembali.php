<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKembali extends Model
{
    use HasFactory;
    
    // SELARASKAN DENGAN KOLOM MIGRATION
    protected $fillable = [
        'material_id',
        'nomor_meter', // <-- Nama kolom baru (selaras)
        'nama_material_lengkap', // <-- Kolom tambahan di migration
        'nama_petugas',
        'stand_meter',
        'keterangan',
        'status',
        'tanggal',
        'foto_path',
    ];

    /**
     * Relasi ke Material
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
    
    /**
     * ACCESSOR: Menghitung jumlah siaga keluar berdasarkan material_id dan nomor_meter.
     * (Mengasumsikan kolom di SiagaKeluar juga sudah 'nomor_meter')
     */
    public function getJumlahSiagaKeluarAttribute()
    {
        // Cari data di Model SiagaKeluar menggunakan kolom baru 'nomor_meter'
        $dataSiagaKeluar = SiagaKeluar::where('material_id', $this->material_id)
                                    ->where('nomor_meter', $this->nomor_meter) // <-- DIGANTI menjadi 'nomor_meter'
                                    ->latest('tanggal')
                                    ->first();

        // Mengasumsikan SiagaKeluar memiliki kolom 'jumlah_siaga_keluar'
        return $dataSiagaKeluar ? $dataSiagaKeluar->jumlah_siaga_keluar : 0;
    }
}