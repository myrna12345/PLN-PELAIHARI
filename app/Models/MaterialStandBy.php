<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialStandBy extends Model
{
    use HasFactory;

    // Paksa Laravel menggunakan nama tabel tunggal sesuai database Anda
    protected $table = 'material_stand_by';

    protected $fillable = [
        'material_id',
        'jumlah',
        'satuan',
        'foto_path',
        'foto_petugas',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}