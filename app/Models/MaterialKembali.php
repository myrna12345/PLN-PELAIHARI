<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKembali extends Model
{
    use HasFactory;

    protected $table = 'material_kembali';

    protected $fillable = [
        'nama_material',
        'nama_petugas',
        'jumlah_material',
        'tanggal',
        'foto'
    ];
}