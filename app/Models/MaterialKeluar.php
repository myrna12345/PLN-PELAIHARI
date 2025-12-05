<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKeluar extends Model
{
    use HasFactory;

    protected $table = 'material_keluar';

    protected $fillable = [
        'nama_material',
        'nama_petugas',
        'jumlah_material',
        'satuan_material', 
        'tanggal',
        'foto'
    ];
}