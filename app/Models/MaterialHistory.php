<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialHistory extends Model
{
    // Nama tabel disesuaikan dengan database Anda
    protected $table = 'material_history';

    protected $fillable = [
        'material_id',
        'nama_material',
        'jumlah',
        'satuan',
        'foto_path',
        'tanggal_input'
    ];

    // Karena ini history, biasanya kita hanya ingin Read-Only di UI
}