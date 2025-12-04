{{-- resources/views/material_retur/create.blade.php --}}

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
                    <option value="" disabled selected>Pilih material</option>
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
                <label for="jumlah">Jumlah dan Satuan (Wajib)</label>
                <div style="display: flex; gap: 10px;">
                    
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                           value="{{ old('jumlah') }}" 
                           placeholder="Jumlah"
                           style="flex: 2;"
                           required min="1">
                    
                    <select name="satuan" id="satuan" class="form-control-new" style="flex: 1; min-width: 120px;" required>
                        <option value="" disabled selected>Pilih Satuan</option>
                        <option value="Buah" {{ old('satuan') == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ old('satuan') == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Jumlah dan satuan wajib diisi.
                </small>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="" disabled selected>Pilih status</option>
                    {{-- 💡 PERBAIKAN KRUSIAL: Nilai dikirim harus 'bekas_andal' agar lolos validasi Controller --}}
                    <option value="bekas_andal" {{ old('status') == 'bekas_andal' ? 'selected' : '' }}>Bekas Andal</option> 
                    <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3">{{ old('keterangan') }}</textarea>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" 
                        class="form-control-new" 
                        style="background-color: #e9ecef; cursor: not-allowed;"
                        value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}"
                        readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Waktu akan otomatis terisi saat data disimpan.
                </small>
                {{-- Input hidden ini memastikan nilai tanggal terkirim sesuai format database --}}
                <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s') }}">
            </div>

            {{-- Upload Foto (Wajib) --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto (Wajib)</label> 
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*" required>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Unggah foto material adalah wajib.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection