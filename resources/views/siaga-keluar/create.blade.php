@extends('layouts.app')

{{-- Set Judul Halaman --}}
@section('title', 'Tambah Siaga Keluar - SIMAS-PLN')

{{-- Set Konten Halaman --}}
@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Siaga Keluar</h2>
    </div>

    {{-- Formulir Tambah Data --}}
    {{-- Arahkan action ke route 'siaga-keluar.store' dengan method POST --}}
    <div class="card-form-body">
        <form action="{{ route('siaga-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf {{-- Token CSRF untuk keamanan --}}

            {{-- 1. Input Nama Material (Dropdown) --}}
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select id="nama_material" name="nama_material" class="form-control-new @error('nama_material') is-invalid @enderror">
                    <option value="" disabled selected>Pilih Material</option>
                    {{-- Loop data material untuk dropdown --}}
                    @foreach ($materials as $material)
                        <option value="{{ $material->id }}" {{ old('nama_material') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }} (Stok: {{ $material->jumlah_stok }})
                        </option>
                    @endforeach
                </select>
                @error('nama_material')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. Input Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" id="nama_petugas" name="nama_petugas" class="form-control-new @error('nama_petugas') is-invalid @enderror" placeholder="Masukkan nama petugas" value="{{ old('nama_petugas') }}">
                @error('nama_petugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3. Input Stand Meter --}}
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" id="stand_meter" name="stand_meter" class="form-control-new @error('stand_meter') is-invalid @enderror" placeholder="Masukkan stand meter" value="{{ old('stand_meter') }}">
                @error('stand_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. Input Jumlah Siaga Keluar --}}
            <div class="form-group-new">
                <label for="jumlah_siaga_keluar">Jumlah Siaga Keluar</label>
                <input type="number" id="jumlah_siaga_keluar" name="jumlah_siaga_keluar" class="form-control-new @error('jumlah_siaga_keluar') is-invalid @enderror" placeholder="Masukkan jumlah" value="{{ old('jumlah_siaga_keluar') }}">
                @error('jumlah_siaga_keluar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 5. Input Tanggal --}}
            <div class="form-group-new">
                <label for="tanggal_local_input">Tanggal</label>
                {{-- Input ini yang dilihat pengguna --}}
                <input type="datetime-local" id="tanggal_local_input" class="form-control-new @error('tanggal') is-invalid @enderror" value="{{ old('tanggal') }}">
                {{-- Input ini yang dikirim ke server (tersembunyi) --}}
                <input type="hidden" id="tanggal_utc_output" name="tanggal">
                @error('tanggal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 6. Input Unggah Foto --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label>
                <input type="file" id="foto" name="foto" class="form-control-new-file @error('foto') is-invalid @enderror">
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="form-actions">
                <a href="{{ route('siaga-keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- CSS Tambahan untuk pesan error validasi (jika belum ada di app.blade.php) --}}
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