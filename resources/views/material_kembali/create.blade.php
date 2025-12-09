@extends('layouts.app')

@section('title', 'Tambah Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Kembali</h2>
        
        {{-- Tampilkan error dari controller, misalnya unit tidak cocok --}}
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
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
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
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
                    {{-- ✅ PERBAIKAN: name diubah dari 'jumlah' menjadi 'jumlah_material' --}}
                    <input type="number" 
                        name="jumlah_material" 
                        id="jumlah_material" 
                        class="form-control-new @error('jumlah_material') is-invalid @enderror" 
                        placeholder="Masukkan jumlah material" 
                        value="{{ old('jumlah_material') }}"
                        min="1"
                        required>
                    @error('jumlah_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan Material (Dropdown Dinamis) --}}
                <div class="form-group-new half-width">
                    <label for="satuan">Satuan Material</label>
                    {{-- ✅ PERBAIKAN: name diubah dari 'satuan_material' menjadi 'satuan' --}}
                    <select name="satuan" id="satuan" class="form-control-new @error('satuan') is-invalid @enderror" required>
                        <option value="" selected disabled>Pilih Satuan</option>
                        {{-- $satuanList dikirim dari Controller --}}
                        @foreach($satuanList as $satuan) 
                            <option value="{{ $satuan }}" {{ old('satuan') == $satuan ? 'selected' : '' }}>
                                {{ $satuan }}
                            </option>
                        @endforeach
                    </select>
                    @error('satuan')
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
                    class="form-control-new @error('nama_petugas') is-invalid @enderror" 
                    placeholder="Masukkan nama petugas" 
                    value="{{ old('nama_petugas') }}"
                    required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tanggal dan Waktu (hanya tampil, tidak bisa diubah) --}}
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>
                <input type="text" 
                    id="tanggal_display" 
                    class="form-control-new"
                    value="{{ now('Asia/Makassar')->format('d M Y, H:i') }} WITA"
                    disabled>
                <small>Waktu akan otomatis terisi saat disimpan.</small>
            </div>

            {{-- Upload Foto --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label>
                <input type="file" 
                    name="foto" 
                    id="foto" 
                    class="form-control-new-file @error('foto') is-invalid @enderror" 
                    accept="image/*"
                    required> {{-- Foto wajib diisi --}}
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small class="text-danger">Unggah foto material wajib diisi.</small>
            </div>

            {{-- Tombol Aksi --}}
            <div class="form-actions">
                <a href="{{ route('material_kembali.index') }}" class="btn-batal">Batal</a>
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

{{-- CSS untuk tata letak bersebelahan --}}
<style>
    .d-flex-group-form {
        display: flex;
        gap: 20px; /* Jarak antar kolom */
    }
    .d-flex-group-form .half-width {
        flex: 1; /* Agar kedua kolom memiliki lebar yang sama */
    }
    /* Tambahkan style is-invalid jika Anda belum memilikinya di CSS utama */
    .is-invalid {
        border-color: red !important;
    }
</style>

@endsection