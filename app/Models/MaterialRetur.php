<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MaterialRetur extends Model
{
    protected $table = 'material_retur';

    protected $fillable = [
        'material_id', 'nama_petugas', 'jumlah', 'satuan',
        'tanggal', 'status', 'keterangan', 'foto_path', 'foto_petugas'
    ];

    protected $casts = ['tanggal' => 'datetime'];

    public function material() {
        return $this->belongsTo(Material::class);
    }
    
    protected function status(): Attribute {
        return Attribute::make(
            get: fn ($value) => ($value === 'bekas_andal' || $value === 'baik') ? 'Baik' : ucfirst($value)
        );
    }
}