<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKeluar extends Model
{
    use HasFactory;

    protected $table = 'siaga_keluars';

    // DAFTAR KOLOM YANG BOLEH DISIMPAN
    protected $fillable = [
        'material_id',
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_keluar',
        'jumlah_siaga_masuk', // <--- INI WAJIB ADA AGAR BISA DISIMPAN
        'status',
        'tanggal',
        'foto_path',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}