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
        'nomor_meter',
        'stand_meter',
        // Dihapus: 'jumlah_siaga_standby', 'nama_petugas'
        'tanggal',
        'unggah_foto',
        'status',
    ];
    
    protected $casts = [
        'tanggal' => 'datetime', 
    ];

    protected $primaryKey = 'id';
    
    public $timestamps = true;
}