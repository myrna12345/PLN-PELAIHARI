<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSiagaStandBy extends Model
{
    use HasFactory;

    protected $table = 'material_siaga_standbies';

    protected $fillable = [
        'nama_material',
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_standby',
        'tanggal',
        'foto',
        'status',
    ];
}
