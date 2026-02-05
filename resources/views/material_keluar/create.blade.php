@extends('layouts.app')

@section('title', 'Tambah Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Keluar</h2>
        {{-- ALERT ERROR DIHAPUS, SUDAH DITANGANI DI layouts.app --}}
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Material --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="">Pilih Material</option>
                    @foreach($materialList as $material)
                        @php
                            $namaMaterial = strtoupper($material->nama_material);
                            $satuanOtomatis = 'Buah';

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
                {{-- Jumlah Material --}}
                <div class="form-group-new half-width">
                    <label for="jumlah_material">Jumlah Material Keluar</label>
                    <input type="number"
                        name="jumlah_material"
                        id="jumlah_material"
                        class="form-control-new"
                        value="{{ old('jumlah_material') }}"
                        min="1"
                        required>
                    @error('jumlah_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan Material --}}
                <div class="form-group-new half-width">
                    <label for="satuan_material">Satuan Material</label>
                    <input type="text"
                        name="satuan_material"
                        id="satuan_material"
                        class="form-control-new"
                        style="background-color:#f8f9fa; cursor:not-allowed;"
                        value="{{ old('satuan_material') }}"
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
                    value="{{ old('nama_petugas') }}"
                    required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Keterangan --}}
            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea
                    name="keterangan"
                    id="keterangan"
                    class="form-control-new"
                    rows="3"
                    required>{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color:red; font-style:italic;">*keterangan wajib diisi.</small>
            </div>

            {{-- Tanggal --}}
            <div class="form-group-new">
                <label>Tanggal dan Waktu</label>
                <input type="text"
                    class="form-control-new"
                    value="{{ now('Asia/Makassar')->format('d M Y, H:i') }} WITA"
                    disabled>
                <small class="text-muted">*Waktu otomatis terisi saat data disimpan.</small>
            </div>

            {{-- Foto Material --}}
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
            </div>

            {{-- Foto Petugas --}}
            <div class="form-group-new">
                <label for="foto_petugas">Unggah Foto Petugas</label>
                <input type="file"
                    name="foto_petugas"
                    id="foto_petugas"
                    class="form-control-new-file"
                    accept="image/*"
                    required>
                @error('foto_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert Success --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

{{-- Script Satuan Otomatis --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const materialSelect = document.getElementById('material_id');
    const satuanInput = document.getElementById('satuan_material');

    materialSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        satuanInput.value = selected.getAttribute('data-satuan') || '';
    });
});
</script>

<style>
.d-flex-group-form {
    display: flex;
    gap: 20px;
}
.half-width {
    flex: 1;
}
</style>
@endsection
