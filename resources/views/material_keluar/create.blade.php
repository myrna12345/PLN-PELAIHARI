@extends('layouts.app')

@section('title', 'Tambah Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Keluar</h2>
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Material --}}
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    <option value="">-- Pilih Material --</option>
                    @foreach($materialList as $material)
                        <option value="{{ $material->nama_material }}">{{ $material->nama_material }}</option>
                    @endforeach
                </select>
                @error('nama_material')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" placeholder="Masukkan nama petugas" required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Jumlah Material Keluar --}}
            <div class="form-group-new">
                <label for="jumlah_material">Jumlah Material Keluar</label>
                <input type="number" name="jumlah_material" id="jumlah_material" class="form-control-new" placeholder="Masukkan jumlah material" required>
                @error('jumlah_material')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tanggal dan Waktu (hanya tampil, tidak bisa diubah) --}}
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>

                {{-- Menampilkan tanggal, tetapi disabled --}}
                <input type="datetime-local" 
                    id="tanggal_display" 
                    class="form-control-new"
                    value="{{ now('Asia/Makassar')->format('Y-m-d\TH:i') }}"
                    disabled>
            </div>

            {{-- Mengirim nilai ke server via input hidden --}}
            <input type="hidden" 
                name="tanggal" 
                value="{{ now('Asia/Makassar')->format('Y-m-d H:i:s') }}">


            {{-- Upload Foto --}}
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
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

{{-- Otomatis set datetime sekarang --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputTanggal = document.getElementById('tanggal');
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const localDatetime = `${year}-${month}-${day}T${hours}:${minutes}`;
    inputTanggal.value = localDatetime;
});
</script>
@endsection
