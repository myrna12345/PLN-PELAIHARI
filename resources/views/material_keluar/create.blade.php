@extends('layouts.app')

@section('title', 'Tambah Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Keluar</h2>
        
        {{-- Tampilkan error dari controller, misalnya stok tidak cukup --}}
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Material (Select) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="">Pilih Material</option>
                    @foreach($materialList as $material)
                        @php
                            $namaMaterial = strtoupper($material->nama_material);
                            $satuanOtomatis = 'Buah'; // Default untuk MCB, KWH, dll
                            
                            if (str_contains($namaMaterial, 'KABEL')) {
                                $satuanOtomatis = 'Meter';
                            }
                        @endphp
                        <option value="{{ $material->id }}" 
                                data-satuan="{{ $satuanOtomatis }}"
                                {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('material_id')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="d-flex-group-form">
                {{-- Jumlah Material Keluar --}}
                <div class="form-group-new half-width">
                    <label for="jumlah_material">Jumlah Material Keluar</label>
                    <input type="number" 
                        name="jumlah_material" 
                        id="jumlah_material" 
                        class="form-control-new" 
                        placeholder="Masukkan jumlah material" 
                        value="{{ old('jumlah_material') }}"
                        min="1"
                        required>
                    @error('jumlah_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan Material (DIKUNCI / AUTOMATIC) --}}
                <div class="form-group-new half-width">
                    <label for="satuan_material">Satuan Material</label>
                    {{-- Menggunakan input readonly agar user tidak bisa mengubah, tapi data tetap terkirim ke server --}}
                    <input type="text" 
                        name="satuan_material" 
                        id="satuan_material" 
                        class="form-control-new" 
                        style="background-color: #f8f9fa; cursor: not-allowed;"
                        value="{{ old('satuan_material') }}" 
                        placeholder="Satuan"
                        readonly 
                        required>
                    @error('satuan_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" 
                    name="nama_petugas" 
                    id="nama_petugas" 
                    class="form-control-new" 
                    placeholder="Masukkan nama petugas" 
                    value="{{ old('nama_petugas') }}"
                    required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea 
                    name="keterangan" 
                    id="keterangan" 
                    class="form-control-new" 
                    rows="3"
                    placeholder="Masukkan keterangan material keluar" 
                    required>{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color: red;display: block; margin-top: 5px; font-style: italic;">*keterangan wajib diisi.</small>
            </div>

            {{-- Tanggal dan Waktu --}}
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>
                <input type="text" 
                    id="tanggal_display" 
                    class="form-control-new"
                    value="{{ now('Asia/Makassar')->format('d M Y, H:i') }} WITA"
                    disabled>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Waktu otomatis terisi saat data disimpan.
                </small>
            </div>

            {{-- Upload Foto Material--}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto Material</label>
                <input type="file" 
                    name="foto" 
                    id="foto" 
                    class="form-control-new-file" 
                    accept="image/*"
                    required>
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color: red;display: block; margin-top: 5px; font-style: italic;">*foto material wajib diisi.</small>
            </div>

            {{-- Upload Foto Petugas --}}
            <div class="form-group-new">
                <label for="foto_petugas">Unggah Foto Petugas</label>
                <input type="file" 
                    name="foto_petugas" 
                    id="foto_petugas" 
                    class="form-control-new-file" 
                    accept="image/*"
                    required>
                @error('foto_petugas')
                    <small style="color: red;">{{ $message }}</small>
                @enderror
                <small style="color: red;display: block; margin-top: 5px; font-style: italic;">*foto petugas wajib diisi.</small>
            </div>

            {{-- Tombol Aksi --}}
            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    showConfirmButton: false,
    timer: 2000
});
</script>
@endif

{{-- Script untuk Otomatisasi Satuan --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialSelect = document.getElementById('material_id');
    const satuanInput = document.getElementById('satuan_material');

    materialSelect.addEventListener('change', function() {
        // Ambil opsi yang dipilih
        const selectedOption = this.options[this.selectedIndex];
        
        // Ambil atribut data-satuan
        const satuanOtomatis = selectedOption.getAttribute('data-satuan');

        if (satuanOtomatis) {
            satuanInput.value = satuanOtomatis;
        } else {
            satuanInput.value = "";
        }
    });
});
</script>

<style>
    .d-flex-group-form {
        display: flex;
        gap: 20px;
    }
    .d-flex-group-form .half-width {
        flex: 1;
    }
</style>

@endsection