@extends('layouts.app')

@section('title', 'Edit Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Edit Material Kembali</h2>
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_kembali.update', $materialKembali->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Material --}}
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    @foreach($materialList as $material)
                        <option value="{{ $material->nama_material }}" {{ $materialKembali->nama_material == $material->nama_material ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('nama_material')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new"
                    value="{{ $materialKembali->nama_petugas }}" placeholder="Masukkan nama petugas" required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Jumlah Material --}}
            <div class="form-group-new">
                <label for="jumlah_material">Jumlah Material Kembali</label>
                <input type="number" name="jumlah_material" id="jumlah_material" class="form-control-new"
                    value="{{ $materialKembali->jumlah_material }}" required>
                @error('jumlah_material')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tanggal & Waktu --}}
            <div class="form-group-new">
                <label for="tanggal">Tanggal & Waktu</label>
                <input type="datetime-local" id="tanggal" name="tanggal" class="form-control-new"
                    value="{{ \Carbon\Carbon::parse($materialKembali->tanggal)->format('Y-m-d\TH:i') }}" required>
                @error('tanggal')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Foto Lama & Baru --}}
            <div class="form-group-new">
                <label for="foto">Foto</label>
                @if($materialKembali->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $materialKembali->foto) }}" 
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

@endsection
