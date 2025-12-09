<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKeluar extends Model
{
    use HasFactory;

    protected $table = 'material_keluar';

    protected $fillable = [
        'material_id',
        'nama_petugas',
        'jumlah_material',
        'satuan_material', 
        'tanggal',
        'foto'
    ];
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
