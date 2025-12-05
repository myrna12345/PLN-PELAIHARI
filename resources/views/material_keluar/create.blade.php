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
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    <option value="">Pilih Material</option>
                    @foreach($materialList as $material)
                        {{-- Mempertahankan input value setelah error --}}
                        <option value="{{ $material->nama_material }}" {{ old('nama_material') == $material->nama_material ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('nama_material')
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

                {{-- Satuan Material --}}
                <div class="form-group-new half-width">
                    <label for="satuan_material">Satuan Material</label>
                    <select name="satuan_material" id="satuan_material" class="form-control-new" required>
                        <option value="" selected disabled>Pilih Satuan</option>
                        {{-- Tambahkan opsi Satuan seperti pada Material Stand By --}}
                        <option value="Buah" {{ old('satuan_material') == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ old('satuan_material') == 'Meter' ? 'selected' : '' }}>Meter</option>
                        {{-- Anda bisa menambahkan opsi satuan lain jika diperlukan --}}
                    </select>
                    @error('satuan_material')
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

            {{-- Tanggal dan Waktu (hanya tampil, tidak bisa diubah) --}}
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>

                {{-- Menampilkan waktu lokal, tetapi disabled --}}
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
                    class="form-control-new-file" 
                    accept="image/*"
                    required>
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small class="text-danger">Unggah foto material wajib diisi.</small>
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