<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKeluar extends Model
{
    use HasFactory;

    protected $table = 'siaga_keluars';

    protected $fillable = [
        'material_id',
        'nomor_unit', 
        'nama_material_lengkap', 
        'nama_petugas',
        'stand_meter',
<<<<<<< HEAD
        // 'jumlah_siaga_keluar' dan 'jumlah_siaga_masuk' DIHAPUS dari fillable
=======
        'keterangan',
>>>>>>> cc9a267bda4b4962e10bd56f9d2880d0840578b9
        'status',
        'tanggal',
        'foto_path',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // POSISI KODE RELASI DI SINI
    public function standbyDetail() 
    {
        return $this->belongsTo(MaterialSiagaStandBy::class, 'nomor_meter', 'nomor_meter');
    }
}