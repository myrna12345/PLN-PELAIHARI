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
                        @php
                            $namaMaterial = strtoupper($material->nama_material);
                            $satuanOtomatis = str_contains($namaMaterial, 'KABEL') ? 'Meter' : 'Buah';
                        @endphp
                        <option value="{{ $material->id }}" 
                            data-satuan="{{ $satuanOtomatis }}"
                            {{ (old('material_id', $materialKembali->material_id) == $material->id) ? 'selected' : '' }}>
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
                        name="jumlah_material" 
                        id="jumlah_material" 
                        class="form-control-new"
                        min="1"
                        value="{{ old('jumlah_material', $materialKembali->jumlah_material) }}" 
                        required>
                    @error('jumlah_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan Material (DIKUNCI / AUTOMATIC) --}}
                <div class="form-group-new half-width">
                    <label for="satuan">Satuan Material</label>
                    <input type="text" 
                        name="satuan" 
                        id="satuan" 
                        class="form-control-new" 
                        style="background-color: #f8f9fa; cursor: not-allowed;"
                        value="{{ old('satuan', $materialKembali->satuan) }}" 
                        readonly 
                        required>
                    @error('satuan')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new"
                    value="{{ old('nama_petugas', $materialKembali->nama_petugas) }}" placeholder="Masukkan nama petugas" required>
                @error('nama_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>
                <input type="text" 
                    id="tanggal_display" 
                    class="form-control-new"
                    style="background-color: #f8f9fa;"
                    value="{{ \Carbon\Carbon::parse($materialKembali->tanggal)->format('d M Y, H:i') }} WITA"
                    disabled>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Tanggal pembuatan data tidak dapat diubah.
                </small>
            </div>

            <input type="hidden" name="tanggal"
                value="{{ \Carbon\Carbon::parse($materialKembali->tanggal)->format('Y-m-d H:i:s') }}">

            {{-- FOTO MATERIAL --}}
            <div class="form-group-new">
                <label for="foto">Foto Material</label>
                @if($materialKembali->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_kembali.show-foto', $materialKembali->id) }}"
                            alt="Foto Material"
                            style="max-width:150px; border-radius: 8px; border:1px solid #ddd; padding:5px;">
                    </div>
                @endif
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Upload ulang jika ingin mengganti foto material
                </small>
                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- FOTO PETUGAS --}}
            <div class="form-group-new">
                <label for="foto_petugas">Foto Petugas</label>
                @if($materialKembali->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_kembali.show-foto-petugas', $materialKembali->id) }}"
                            alt="Foto Petugas"
                            style="max-width:150px; border-radius: 8px; border:1px solid #ddd; padding:5px;">
                    </div>
                @endif
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file" accept="image/*">
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Upload ulang jika ingin mengganti foto petugas
                </small>
                @error('foto_petugas')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="form-actions">
                <a href="{{ route('material_kembali.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialSelect = document.getElementById('material_id');
    const satuanInput = document.getElementById('satuan');

    // Fungsi untuk update satuan
    materialSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const satuanOtomatis = selectedOption.getAttribute('data-satuan');
        satuanInput.value = satuanOtomatis || "";
    });
});

@if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    showConfirmButton: false,
    timer: 2000
});
@endif
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