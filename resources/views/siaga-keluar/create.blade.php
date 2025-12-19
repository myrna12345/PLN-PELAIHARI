@extends('layouts.app')

@section('title', 'Tambah Siaga Keluar')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Siaga Keluar</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert"> 
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-form-body">
        
        <form action="{{ route('siaga-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- 1. Nama Material (Dropdown) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new @error('material_id') is-invalid @enderror" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('material_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 2. Nomor Meter --}}
            <div class="form-group-new">
                <label for="nomor_meter">Nomor Meter</label> 
                <input type="text" 
                        name="nomor_meter" 
                        id="nomor_meter" 
                        class="form-control-new @error('nomor_meter') is-invalid @enderror" 
                        value="{{ old('nomor_meter') }}" 
                        placeholder="Masukkan Nomor Meter"
                        required>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Pastikan nomor meter yang dimasukkan sesuai dengan data.
                </small>
                @error('nomor_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 3. Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" 
                        name="nama_petugas" 
                        id="nama_petugas" 
                        class="form-control-new @error('nama_petugas') is-invalid @enderror" 
                        value="{{ old('nama_petugas') }}" 
                        required>
                @error('nama_petugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 4. Stand Meter --}}
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" 
                        name="stand_meter" 
                        id="stand_meter" 
                        class="form-control-new @error('stand_meter') is-invalid @enderror" 
                        value="{{ old('stand_meter') }}" 
                        required>
                @error('stand_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 5. KETERANGAN (WAJIB) --}}
            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" 
                          id="keterangan" 
                          class="form-control-new @error('keterangan') is-invalid @enderror" 
                          placeholder="Masukkan keterangan material keluar"
                          required>{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 6. UNGGAH FOTO --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label> 
                <input type="file" 
                        name="foto" 
                        id="foto" 
                        class="form-control-new-file @error('foto') is-invalid @enderror" 
                        accept="image/*"
                        required> 
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-danger" style="display: block; margin-top: 5px;">
                    *Unggah foto material adalah wajib.
                </small>
            </div>
            
            {{-- 7. Status (Readonly - Keluar) --}}
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" 
                        name="status" 
                        id="status" 
                        class="form-control-new" 
                        value="{{ old('status', 'Keluar') }}" 
                        readonly 
                        style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            {{-- 8. TANGGAL DAN JAM (READONLY) --}}
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
            </div>


            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection