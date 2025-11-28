<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SiagaKeluar; // Pastikan ini diimpor

class SiagaKembali extends Model
{
    use HasFactory;

    protected $table = 'siaga_kembalis';

    protected $fillable = [
        'material_id',
        // START: PENAMBAHAN KOLOM BARU
        'nomor_unit',
        'nama_material_lengkap',
        // END: PENAMBAHAN KOLOM BARU
        'nama_petugas',
        'stand_meter',
        'jumlah_siaga_kembali', 
        'status', 
        'tanggal',
        'foto_path',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Accessor untuk mengambil data 'jumlah_siaga_keluar' secara otomatis
     * Berdasarkan kecocokan Material ID dan Nomor Unit TERBARU.
     */
    public function getJumlahSiagaKeluarAttribute()
    {
        // 🟢 PERBAIKAN: Cari data di tabel Siaga Keluar berdasarkan material_id DAN nomor_unit
        $dataKeluar = SiagaKeluar::where('material_id', $this->material_id)
                                 ->where('nomor_unit', $this->nomor_unit) // ⬅️ Kunci perbaikan utama
                                 ->latest('tanggal')
                                 ->first();

        // Jika ketemu, kembalikan jumlah keluar. Jika tidak, kembalikan 0.
        return $dataKeluar ? $dataKeluar->jumlah_siaga_keluar : 0;
    }
}