<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKeluar extends Model
{
    use HasFactory;
    protected $table = 'material_keluar'; // Asumsi nama tabel
    // Anda bisa tambahkan $fillable nanti
}