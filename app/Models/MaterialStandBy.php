<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialStandBy extends Model
{
    use HasFactory;
    
    protected $table = 'material_stand_by';

    protected $fillable = [
        'material_id', 
        'nama_petugas', 
        'jumlah',
        'satuan', // 🟢 BARU: Field Satuan
        'tanggal', 
        'foto_path'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}