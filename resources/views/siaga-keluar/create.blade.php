@extends('layouts.app')

@section('title', 'Tambah Siaga Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Siaga Keluar</h2>
    </div>

    <div class="card-form-body">
        <form action="{{ route('siaga-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- DROPDOWN MATERIAL --}}
            <div class="form-group-new">
                <label for="material">Nama Material</label>
                {{-- Nama input sudah BENAR: name="material" --}}
                <select id="material" name="material" class="form-control-new @error('material') is-invalid @enderror">
                    <option value="">Pilih Material</option>

                    {{-- Data 1P --}}
                    <optgroup label="1P">
                        @foreach($oneP as $item)
                            {{-- Value yang dikirim: "1P-5" --}}
                            <option value="1P-{{ $item }}" {{ old('material') == "1P-{$item}" ? 'selected' : '' }}>
                                {{ "1P - " . $item }}
                            </option>
                        @endforeach
                    </optgroup>

                    {{-- Data 3P --}}
                    <optgroup label="3P">
                        @foreach($threeP as $item)
                            {{-- Value yang dikirim: "3P-1" --}}
                            <option value="3P-{{ $item }}" {{ old('material') == "3P-{$item}" ? 'selected' : '' }}>
                                {{ "3P - " . $item }}
                            </option>
                        @endforeach
                    </optgroup>

                </select>
                @error('material')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                {{-- Catatan: Validasi 'material_count' sudah dipindahkan ke 'material' di Controller --}}
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" id="nama_petugas" name="nama_petugas"
                        class="form-control-new @error('nama_petugas') is-invalid @enderror"
                        placeholder="Masukkan nama petugas"
                        value="{{ old('nama_petugas') }}">
                @error('nama_petugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Stand Meter --}}
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" id="stand_meter" name="stand_meter"
                        class="form-control-new @error('stand_meter') is-invalid @enderror"
                        placeholder="Masukkan stand meter"
                        value="{{ old('stand_meter') }}">
                @error('stand_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jumlah --}}
            <div class="form-group-new">
                <label for="jumlah_siaga_keluar">Jumlah Siaga Keluar</label>
                <input type="number" id="jumlah_siaga_keluar" name="jumlah_siaga_keluar"
                        class="form-control-new @error('jumlah_siaga_keluar') is-invalid @enderror"
                        placeholder="Masukkan jumlah"
                        value="{{ old('jumlah_siaga_keluar') }}">
                @error('jumlah_siaga_keluar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal (OTOMATIS & MATI/READONLY) --}}
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                
                {{-- Input ini hanya tampilan (Visual), tidak dikirim ke server --}}
                <input type="text" 
                        class="form-control-new"
                        value="{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d M Y, H:i') }}" 
                        readonly 
                        tabindex="-1" 
                        style="background-color: #e9ecef; color: #495057; pointer-events: none; cursor: not-allowed; border: 1px solid #ced4da;">
                
                <small class="text-muted" style="display:block; margin-top:5px; color:#6c757d;">
                    Waktu akan otomatis terisi saat data disimpan.
                </small>
            </div>

            {{-- Foto --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label>
                <input type="file" id="foto" name="foto"
                        class="form-control-new-file @error('foto') is-invalid @enderror">
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Aksi --}}
            <div class="form-actions">
                <a href="{{ route('siaga-keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .is-invalid {
        border-color: #dc3545;
    }
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
</style>
@endpush

@endsection