@extends('layouts.app')

@section('title', 'Lihat Material Retur')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Detail Material Retur</h2>
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
                <label>Jumlah Retur</label>
                <input type="number" class="form-control-new" value="{{ $item->jumlah }}" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Jumlah Keluar (Otomatis)</label>
                <input type="number" class="form-control-new" value="{{ $item->material_keluar ?? 0 }}" disabled>
            </div>
            <div class="form-group-new">
                <label>Jumlah Kembali (Otomatis)</label>
                <input type="number" class="form-control-new" value="{{ $item->material_kembali ?? 0 }}" disabled>
            </div>
            <div class="form-group-new">
                <label>Status Material</label>
                <input type="text" class="form-control-new" value="{{ $item->status == 'baik' ? 'Baik (Andal Bagus)' : 'Rusak (Andal Rusak)' }}" disabled>
            </div>

            <div class="form-group-new">
                <label>Tanggal dan Jam (WITA)</label>
                <input type="text" class="form-control-new" value="{{ $item->tanggal->format('d M Y, H:i') }}" disabled>
            </div>

            <div class="form-group-new">
                <label>Keterangan</label>
                <textarea class="form-control-new" rows="3" disabled>{{ $item->keterangan ?? '-' }}</textarea>
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
                <a href="{{ route('material-retur.index') }}" class="btn-batal">Kembali</a>
            </div>
        
    </div>
</div>
@endsection