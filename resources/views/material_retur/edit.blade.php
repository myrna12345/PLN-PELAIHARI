@extends('layouts.app')

@section('title', 'Edit Material Retur')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Material Retur</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-form-body">
        
        <form action="{{ route('material-retur.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled>Pilih material...</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah Retur</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control-new" value="{{ old('jumlah', $item->jumlah) }}" required>
            </div>

            <div class="form-group-new">
                <label>Jumlah Keluar (Otomatis)</label>
                <input type="number" class="form-control-new" value="{{ $item->material_keluar ?? 0 }}" disabled style="background-color: #e9ecef;">
            </div>
            <div class="form-group-new">
                <label>Jumlah Kembali (Otomatis)</label>
                <input type="number" class="form-control-new" value="{{ $item->material_kembali ?? 0 }}" disabled style="background-color: #e9ecef;">
            </div>
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="baik" {{ old('status', $item->status) == 'baik' ? 'selected' : '' }}>Baik (Andal Bagus)</option>
                    <option value="rusak" {{ old('status', $item->status) == 'rusak' ? 'selected' : '' }}>Rusak (Andal Rusak)</option>
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="tanggal">Tanggal dan Jam</label>
                <input type="datetime-local" 
                       name="tanggal" 
                       id="tanggal" 
                       class="form-control-new" 
                       value="{{ old('tanggal', $item->tanggal->format('Y-m-d\TH:i')) }}"
                       required>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3">{{ old('keterangan', $item->keterangan) }}</textarea>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto (Opsional: Ganti foto)</label>
                @if($item->foto_path)
                    <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Lama" width="150" style="margin-bottom:10px; display:block; border-radius: 5px;">
                @endif
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-retur.index') }}" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection