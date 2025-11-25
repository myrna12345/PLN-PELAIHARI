@extends('layouts.app')

@section('title', 'Edit Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header text-center mb-4">
        <h2>Edit Material Keluar</h2>
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Nama Material -->
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    <option value="">-- Pilih Material --</option>
                    @foreach($materialList as $material)
                        <option value="{{ $material->nama_material }}" 
                            {{ $material->nama_material == $data->nama_material ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('nama_material') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>

            <!-- Nama Petugas -->
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" 
                       class="form-control-new" value="{{ old('nama_petugas', $data->nama_petugas) }}" required>
                @error('nama_petugas') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>

            <!-- Jumlah Material -->
            <div class="form-group-new">
                <label for="jumlah_material">Jumlah Material Keluar</label>
                <input type="number" name="jumlah_material" id="jumlah_material" 
                       class="form-control-new" value="{{ old('jumlah_material', $data->jumlah_material) }}" required>
                @error('jumlah_material') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>

            <!-- Tanggal dan Waktu -->
            <div class="form-group-new">
                <label for="tanggal">Tanggal dan Waktu</label>
                <input type="datetime-local" name="tanggal" id="tanggal"
                       class="form-control-new"
                       value="{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d\TH:i') }}" required>
                @error('tanggal') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>

            <!-- Foto Lama & Baru -->
            <div class="form-group-new">
                <label for="foto">Foto</label>

                @if($data->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $data->foto) }}" 
                            alt="Foto Material" 
                            class="table-foto">
                    </div>
                @endif

                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                <small style="color: #777;">*Upload jika ingin mengganti foto</small>

                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>


            <!-- Tombol Aksi -->
            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Notifikasi Berhasil -->
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

<!-- Script untuk Auto Isi Tanggal Lokal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.getElementById('tanggal');
    if (!tanggalInput.value) {
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        const localTime = new Date(now - offset).toISOString().slice(0,16);
        tanggalInput.value = localTime;
    }
});
</script>
@endsection