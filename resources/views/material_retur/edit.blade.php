{{-- resources/views/material_retur/edit.blade.php --}}

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
                <label for="jumlah">Jumlah dan Satuan (Wajib)</label>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                            value="{{ old('jumlah', $item->jumlah) }}" 
                            placeholder="Jumlah"
                            style="flex: 2;"
                            required min="1">
                    
                    <select name="satuan" id="satuan" class="form-control-new" style="flex: 1; min-width: 120px;" required>
                        <option value="" disabled>Pilih Satuan</option>
                        <option value="Buah" {{ old('satuan', $item->satuan) == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ old('satuan', $item->satuan) == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Jumlah dan satuan wajib diisi.
                </small>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="bekas_andal" {{ old('status', $item->status) == 'bekas_andal' || old('status', $item->status) == 'Baik' ? 'selected' : '' }}>Bekas Andal</option>
                    <option value="rusak" {{ old('status', $item->status) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" 
                        class="form-control-new" 
                        style="background-color: #e9ecef; cursor: not-allowed;"
                        value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}"
                        readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Tanggal pembuatan data tidak dapat diubah.
                </small>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3">{{ old('keterangan', $item->keterangan) }}</textarea>
            </div>

            {{-- --- BAGIAN FOTO MATERIAL --- --}}
            <div class="form-group-new">
                <label>Foto Material</label>
                @if ($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material-retur.show-foto', $item->id) }}" 
                              alt="Foto Material Saat Ini" 
                              style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Material Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file"> 
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Biarkan kosong jika tidak ingin mengubah foto material.
                </small>
            </div>

            {{-- --- BAGIAN FOTO PETUGAS (TAMBAHAN BARU) --- --}}
            <div class="form-group-new">
                <label>Foto Petugas</label>
                @if ($item->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        {{-- Menggunakan asset storage atau route khusus jika ada --}}
                        <img src="{{ asset('storage/' . $item->foto_petugas) }}" 
                              alt="Foto Petugas Saat Ini" 
                              style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto_petugas" style="display: block; margin-top: 10px;">Unggah Foto Petugas Baru (Opsional)</label>
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file"> 
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Biarkan kosong jika tidak ingin mengubah foto petugas.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-retur.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection