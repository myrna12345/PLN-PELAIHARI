<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKeluar extends Model
{
    use HasFactory;

    protected $table = 'siaga_keluars';

    // DAFTAR KOLOM YANG BOLEH DISIMPAN (Mass Assignment)
    protected $fillable = [
        'material_id',
        // START: PERBAIKAN UTAMA
        'nomor_unit', // DITAMBAHKAN
        'nama_material_lengkap', // DITAMBAHKAN
        // END: PERBAIKAN UTAMA
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_keluar',
        'jumlah_siaga_masuk', // ⬅️ Kolom ini seharusnya menyimpan nilai kembalian
        'status',
        'tanggal',
        'foto_path',
    ];
    
    // Pastikan tidak ada Accessor yang menghitung selisih di sini.
    // Jika ada Accessor di Model SiagaKeluar yang bernama getJumlahSiagaKembaliAttribute, hapus Accessor tersebut.

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}