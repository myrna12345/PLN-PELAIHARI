<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SiagaKeluar extends Model
{
    use HasFactory;

    // Tentukan nama tabel
    protected $table = 'siaga_keluars';

    /**
     * Kolom yang boleh diisi secara massal (mass assignment)
     * Pastikan 'nama_material' ada agar data bisa masuk ke database.
     */
    protected $fillable = [
        'nama_material', 
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_keluar',
        'tanggal',
        'foto',
        'status', 
        'jumlah_siaga_kembali',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];
}