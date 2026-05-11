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

    .form-control-new {
        width: 100% !important;
        box-sizing: border-box;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
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
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Material --}}
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
            </div>
            
            {{-- Jumlah dan Satuan --}}
            <div class="form-group-new">
                <label>Jumlah dan Satuan Material Keluar</label>
                <div class="d-flex-group-form">
                    <div style="flex: 3;">
                        <input type="number" name="jumlah_material" id="jumlah_material" class="form-control-new" 
                            value="{{ old('jumlah_material', $data->jumlah_material) }}" min="1" required>
                    </div>
                    <div style="flex: 1;">
                        <input type="text" name="satuan_material" id="satuan_material" class="form-control-new" 
                            style="background-color: #f8f9fa; cursor: not-allowed;"
                            value="{{ old('satuan_material', $data->satuan_material) }}" readonly required>
                    </div>
                </div>
            </div>

            {{-- Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" 
                        value="{{ old('nama_petugas', $data->nama_petugas) }}" required>
            </div>

            {{-- Keterangan --}}
            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3" required>{{ old('keterangan', $data->keterangan) }}</textarea>
            </div>

            {{-- Tanggal --}}
            <div class="form-group-new">
                <label>Tanggal dan Waktu</label>
                <input type="text" class="form-control-new" 
                    value="{{ \Carbon\Carbon::parse($data->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }} WITA" disabled>
            </div>

            {{-- FOTO MATERIAL --}}
            <div class="form-group-new">
                <label for="foto">Foto Material</label>
                @if($data->foto)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_keluar.show-foto', $data->id) }}?v={{ time() }}" 
                        style="max-width:150px; border-radius: 8px; border:1px solid #ddd; padding:5px;">
                    </div>
                @endif
                {{-- PERBAIKAN: Menggunakan onclick="this.value=null" agar foto tidak terhapus otomatis --}}
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*" capture="environment" onclick="this.value=null">
                <small class="text-muted">*Klik untuk mengambil foto baru menggunakan kamera</small>
            </div>

            {{-- FOTO PETUGAS --}}
            <div class="form-group-new">
                <label for="foto_petugas">Foto Petugas</label>
                @if($data->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material_keluar.show-foto-petugas', $data->id) }}?v={{ time() }}"
                        style="max-width:150px; border-radius: 8px; border:1px solid #ddd; padding:5px;">
                    </div>
                @endif
                {{-- PERBAIKAN: Menggunakan onclick="this.value=null" agar foto tidak terhapus otomatis --}}
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file" accept="image/*" capture="environment" onclick="this.value=null">
                <small class="text-muted">*Klik untuk mengambil foto baru menggunakan kamera</small>
            </div>

            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Script Otomatisasi Satuan --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialSelect = document.getElementById('material_id');
    const satuanInput = document.getElementById('satuan_material');
    materialSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        satuanInput.value = selectedOption.getAttribute('data-satuan') || "";
    });
});
</script>
@endsection