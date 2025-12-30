@extends('layouts.app')

@section('title', 'Edit Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Edit Material Kembali</h2>
        
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_kembali.update', $materialKembali->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Material (Select) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled>Pilih Material</option>
                    @foreach($materialList as $material)
                        <option value="{{ $material->id }}" 
                            {{ (old('material_id') == $material->id || $materialKembali->material_id == $material->id) ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('material_id')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>
            
            {{-- Group Jumlah dan Satuan Material --}}
            <div class="d-flex-group-form">
                {{-- Jumlah Material Kembali --}}
                <div class="form-group-new half-width">
                    <label for="jumlah_material">Jumlah Material Kembali</label>
                    <input type="number" 
                        {{-- Nama input disesuaikan agar cocok dengan Controller (Diasumsikan menggunakan 'jumlah_material' untuk jumlah) --}}
                        name="jumlah_material" 
                        id="jumlah_material" 
                        class="form-control-new"
                        min="1"
                        {{-- Pemuatan nilai yang benar --}}
                        value="{{ old('jumlah_material') ?? $materialKembali->jumlah_material }}" 
                        required>
                    @error('jumlah_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan Material --}}
                <div class="form-group-new half-width">
                    <label for="satuan">Satuan Material</label>
                    {{-- ✅ PERBAIKAN UTAMA: Name diubah kembali menjadi 'satuan' agar lolos validasi 'The satuan field is required.' --}}
                    <select name="satuan" id="satuan" class="form-control-new" required>
                        <option value="" disabled>Pilih Satuan</option>
                        @foreach($satuanList as $satuan)
                            <option value="{{ $satuan }}" 
                                {{-- Menggunakan nama field/kolom 'satuan' untuk memuat nilai lama --}}
                                {{ (old('satuan') == $satuan || $materialKembali->satuan == $satuan) ? 'selected' : '' }}>
                                {{ $satuan }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Error message disesuaikan menggunakan nama 'satuan' --}}
                    @error('satuan')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new"
                    value="{{ old('nama_petugas') ?? $materialKembali->nama_petugas }}" placeholder="Masukkan nama petugas" required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>
                <input type="text" 
                    id="tanggal_display" 
                    class="form-control-new"
                    value="{{ \Carbon\Carbon::parse($materialKembali->tanggal)->format('d M Y, H:i') }} WITA"
                    disabled>
                <small>Hanya tanggal ini yang tidak dapat diubah.</small>
            </div>

            <input type="hidden" name="tanggal"
                value="{{ \Carbon\Carbon::parse($materialKembali->tanggal)->format('Y-m-d H:i:s') }}">

            {{-- Foto Lama & Baru --}}
            <div class="form-group-new">
                <label for="foto">Foto</label>
                @if($materialKembali->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_kembali.show-foto', $materialKembali->id) }}" 
                            alt="Foto Material" 
                            class="table-foto" 
                            style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;">
                    </div>
                @endif
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                <small style="color: #777;">*Upload jika ingin mengganti foto</small>
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="form-actions">
                <a href="{{ route('material_kembali.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert Success --}}
@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

{{-- Optional: CSS untuk tata letak bersebelahan --}}
<style>
    .d-flex-group-form {
        display: flex;
        gap: 20px; /* Jarak antar kolom */
    }
    .d-flex-group-form .half-width {
        flex: 1; /* Agar kedua kolom memiliki lebar yang sama */
    }
</style>

@endsection