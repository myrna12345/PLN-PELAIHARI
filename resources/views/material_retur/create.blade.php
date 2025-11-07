@extends('layouts.app')

@section('title', 'Tambah Material Retur')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Retur</h2>
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
        
        <form action="{{ route('material-retur.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled selected>Pilih material...</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah/Unit</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control-new" value="{{ old('jumlah') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="" disabled selected>Pilih status...</option>
                    <option value="baik" {{ old('status') == 'baik' ? 'selected' : '' }}>Andal Bagus</option>
                    <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>

            <div class="form-group-new">
                <label for="tanggal">Tanggal dan Jam</label>
                <input type="datetime-local" 
                       name="tanggal" 
                       id="tanggal" 
                       class="form-control-new" 
                       value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}"
                       required>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3">{{ old('keterangan') }}</textarea>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection