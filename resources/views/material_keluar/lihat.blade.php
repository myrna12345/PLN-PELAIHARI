@extends('layouts.app')

@section('title', 'Lihat Material Keluar')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Detail Material Keluar</h2>
    </div>

    <div class="card-form-body">

        <div class="form-group-new">
            <label>Nama Material</label>
            <input type="text" class="form-control-new" value="{{ $item->nama_material }}" disabled>
        </div>

        <div class="form-group-new">
            <label>Nama Petugas</label>
            <input type="text" class="form-control-new" value="{{ $item->nama_petugas }}" disabled>
        </div>

        <div class="form-group-new">
            <label>Jumlah/Unit</label>
            <input type="number" class="form-control-new" value="{{ $item->jumlah_material }}" disabled>
        </div>

        <div class="form-group-new">
            <label>Tanggal dan Jam (WITA)</label>
            <input type="text" 
                   class="form-control-new" 
                   value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}" 
                   disabled>
        </div>

        <div class="form-group-new">
            <label>Foto</label>
            @if($item->foto)
                <img src="{{ asset('storage/' . $item->foto) }}" 
                     alt="Foto Material" 
                     style="width: 100%; max-width: 400px; border-radius: 10px; display:block;">
                <div style="margin-top: 10px;">
                    <a href="{{ asset('storage/' . $item->foto) }}" 
                       download 
                       class="btn btn-primary btn-sm"
                       style="background:#3498db; color:white; padding:6px 12px; border-radius:6px; text-decoration:none;">
                        <i class="fas fa-download"></i> Download Foto
                    </a>
                </div>
            @else
                <p>Tidak ada foto yang diunggah.</p>
            @endif
        </div>

        <div class="form-actions" style="justify-content: flex-start;">
            <a href="{{ route('material_keluar.index') }}" class="btn-batal">Kembali</a>
        </div>

    </div>
</div>
@endsection