@extends('layouts.app')

@section('content')
<style>
    .form-group-new {
        margin-bottom: 15px;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .form-group-new label {
        display: block;
        margin-bottom: 8px;
    }

    .d-flex-group-form {
        display: flex;
        gap: 15px;
        width: 100%;
        align-items: stretch;
    }

    .d-flex-group-form > div {
        display: flex;
    }

    .form-control-new {
        width: 100% !important;
        box-sizing: border-box;
    }

    .table-foto {
        object-fit: cover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .d-flex-group-form {
            gap: 10px;
        }
    }
</style>

<div class="card-form-container mx-auto">
    <div class="card-form-header text-center mb-4">
        <h2>Edit Material Keluar</h2>
        
        {{-- Tampilkan error dari controller, misalnya stok tidak cukup --}}
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Material (Select) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="">-- Pilih Material --</option>
                    @foreach($materialList as $material)
                        @php
                            $namaMaterial = strtoupper($material->nama_material);
                            $satuanOtomatis = str_contains($namaMaterial, 'KABEL') ? 'Meter' : 'Buah';
                        @endphp
                        <option value="{{ $material->id }}" 
                            data-satuan="{{ $satuanOtomatis }}"
                            {{ old('material_id', $data->material_id) == $material->id ? 'selected' : '' }}>
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
                <label>Jumlah dan Satuan Material Keluar</label>
                <div class="d-flex-group-form">
                    {{-- Input Jumlah --}}
                    <div style="flex: 3;">
                        <input type="number" 
                            name="jumlah_material" 
                            id="jumlah_material" 
                            class="form-control-new" 
                            placeholder="Jumlah" 
                            value="{{ old('jumlah_material', $data->jumlah_material) }}" 
                            min="1"
                            required>
                    </div>

                    {{-- Input Satuan --}}
                    <div style="flex: 1;">
                        <input type="text" 
                            name="satuan_material" 
                            id="satuan_material" 
                            class="form-control-new" 
                            style="background-color: #f8f9fa; cursor: not-allowed;"
                            value="{{ old('satuan_material', $data->satuan_material) }}" 
                            placeholder="Satuan"
                            readonly 
                            required>
                    </div>
                </div>
                @error('jumlah_material') 
                    <small style="color:red; display:block;">{{ $message }}</small> 
                @enderror
                @error('satuan_material')
                    <small style="color:red; display:block;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" 
                        class="form-control-new" 
                        value="{{ old('nama_petugas', $data->nama_petugas) }}" required>
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
                    required>{{ old('keterangan', $data->keterangan) }}</textarea>
                @error('keterangan')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
                <small style="color: red;display: block; margin-top: 5px; font-style: italic;">*keterangan wajib diisi.</small>
            </div>

            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>
                <input type="text" id="tanggal_display"
                    class="form-control-new"
                    value="{{ \Carbon\Carbon::parse($data->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }} WITA"
                    disabled>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Tanggal pembuatan data tidak dapat diubah.
                </small>
            </div>

            <input type="hidden" name="tanggal"
                value="{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d H:i:s') }}">

            {{-- FOTO MATERIAL --}}
            <div class="form-group-new">
                <label for="foto">Foto Material</label>
                @if($data->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_keluar.show-foto', $data->id) }}?v={{ time() }}" 
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
                @if($data->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_keluar.show-foto-petugas', $data->id) }}?v={{ time() }}"
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

            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>

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

<style>
    .d-flex-group-form {
        display: flex;
        gap: 20px;
    }
    .d-flex-group-form .half-width {
        flex: 1;
    }
    .table-foto {
        object-fit: cover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endsection