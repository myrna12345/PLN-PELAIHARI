@extends('layouts.app')

@section('title', 'Lihat Material Stand By')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Detail Material Stand By</h2>
    </div>

    <div class="card-form-body">
        
            <div class="form-group-new">
                <label>Nama Material</label>
                <input type="text" class="form-control-new" value="{{ $item->material->nama_material ?? 'N/A' }}" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Nama Petugas</label>
                <input type="text" class="form-control-new" value="{{ $item->nama_petugas }}" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Jumlah/Unit</label>
                <input type="number" class="form-control-new" value="{{ $item->jumlah }}" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam (WITA)</label>
                <input type="text" class="form-control-new" value="{{ $item->tanggal->format('d M Y, H:i') }}" disabled>
            </div>

            <div class="form-group-new">
                <label>Foto</label>
                @if($item->foto_path)
                    <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Material" style="width: 100%; max-width: 400px; border-radius: 10px; display:block;">
                @else
                    <p>Tidak ada foto yang diunggah.</p>
                @endif
            </div>

            <div class="form-actions" style="justify-content: flex-start;">
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal">Kembali</a>
            </div>
        
    </div>
</div>
@endsection