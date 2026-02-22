@extends('layouts.app')

@section('content')
<style>
    .form-group-new {
        margin-bottom: 15px;
        width: 100%;
        display: flex;
        flex-direction: column; /* Label tetap di atas */
    }

    .form-group-new label {
        display: block;
        margin-bottom: 8px;
    }

    .d-flex-group-form {
        display: flex;
        gap: 15px;
        width: 100%; /* Memenuhi seluruh lebar container */
        align-items: stretch;
    }

    .d-flex-group-form > div {
        display: flex;
    }

    .form-control-new {
        width: 100% !important;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .d-flex-group-form {
            gap: 10px; /* Jarak lebih kecil di HP agar tidak sempit */
        }
    }
</style>

<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Kembali</h2>
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_kembali.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Material (Select) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new @error('material_id') is-invalid @enderror" required>
                    <option value="">Pilih Material</option>
                    @foreach($materialList as $material)
                        @php
                            $namaMaterial = strtoupper($material->nama_material);
                            $satuanOtomatis = str_contains($namaMaterial, 'KABEL') ? 'Meter' : 'Buah';
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
            
            {{-- Gabungan Jumlah dan Satuan --}}
            <div class="form-group-new">
                <label>Jumlah dan Satuan</label>
                <div class="d-flex-group-form">
                    {{-- Input Jumlah --}}
                    <div style="flex: 3;"> {{-- Lebih lebar untuk angka --}}
                        <input type="number" 
                            name="jumlah_material" 
                            id="jumlah_material" 
                            class="form-control-new @error('jumlah_material') is-invalid @enderror" 
                            placeholder="Jumlah" 
                            value="{{ old('jumlah_material') }}"
                            min="1"
                            required>
                    </div>

                    {{-- Input Satuan --}}
                    <div style="flex: 1;"> {{-- Rata kanan dan mengikuti sisa ruang --}}
                        <input type="text" 
                            name="satuan" 
                            id="satuan" 
                            class="form-control-new @error('satuan') is-invalid @enderror" 
                            style="background-color: #f8f9fa; cursor: not-allowed;"
                            value="{{ old('satuan') }}" 
                            placeholder="Satuan"
                            readonly 
                            required>
                    </div>
                </div>
                @error('jumlah_material')
                    <small style="color:red; display:block;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" 
                    name="nama_petugas" 
                    id="nama_petugas" 
                    class="form-control-new @error('nama_petugas') is-invalid @enderror" 
                    placeholder="Masukkan nama petugas" 
                    value="{{ old('nama_petugas') }}"
                    required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tanggal dan Waktu (hanya tampil) --}}
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

            {{-- Upload Foto Material --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto Material</label>
                <input type="file" 
                    name="foto" 
                    id="foto" 
                    class="form-control-new-file @error('foto') is-invalid @enderror" 
                    accept="image/*"
                    required>
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color:red;display: block; margin-top: 5px; font-style: italic;">*foto material wajib diisi.</small>
            </div>

            {{-- Upload Foto Petugas --}}
            <div class="form-group-new">
                <label for="foto_petugas">Unggah Foto Petugas</label>
                <input type="file" 
                    name="foto_petugas" 
                    id="foto_petugas" 
                    class="form-control-new-file @error('foto_petugas') is-invalid @enderror" 
                    accept="image/*"
                    required>
                @error('foto_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color:red;display: block; margin-top: 5px; font-style: italic;">*foto petugas wajib diisi.</small>
            </div>

            {{-- Tombol Aksi --}}
            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
                <a href="{{ route('material_kembali.index') }}" class="btn-batal">Batal</a>
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
    const satuanInput = document.getElementById('satuan');

    materialSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const satuanOtomatis = selectedOption.getAttribute('data-satuan');

        if (satuanOtomatis) {
            satuanInput.value = satuanOtomatis;
        } else {
            satuanInput.value = "";
        }
    });
});
</script>

@endsection