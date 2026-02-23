<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialHistory extends Model
{
    protected $fillable = [
        'nama_material', 
        'jumlah',
        'satuan',
        'foto_path',
        'tanggal_input'
    ];
}