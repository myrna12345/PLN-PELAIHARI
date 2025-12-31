<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKembali extends Model
{
    use HasFactory;

    protected $table = 'material_kembali';

    protected $fillable = [
        'material_id',
        'nama_petugas',
        'jumlah_material',
        'satuan',
        'tanggal',
        'foto',
        'foto_petugas'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}