@extends('layouts.app')

@section('title', 'Edit Siaga Keluar')

@section('content')

<div class="card-form-container">
<div class="card-form-header">
<h2>Edit Data Siaga Keluar</h2>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-form-body">
    <form action="{{ route('siaga-keluar.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- 💡 PERBAIKAN: HIDDEN INPUT UNTUK MEMPERTAHANKAN NILAI NAMA MATERIAL LENGKAP --}}
        <input type="hidden" 
               name="nama_material_lengkap" 
               value="{{ old('nama_material_lengkap', $item->nama_material_lengkap) }}">
        
        <div class="form-group-new">
            <label for="material_id">Nama Material</label>
            <select name="material_id" id="material_id" class="form-control-new" required>
                <option value="" disabled>Pilih material...</option>
                {{-- Loop data material --}}
                @foreach($materials as $material)
                    <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                        {{ $material->nama_material }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 💡 PERBAIKAN KRUSIAL: MENGUBAH NOMOR METER DARI DROPDOWN KE INPUT TEXT --}}
        <div class="form-group-new">
            <label for="nomor_unit">Nomor Meter (Wajib)</label>
            {{-- Menggunakan input teks, nilai dipertahankan dari $item->nomor_unit --}}
            <input type="text" 
                   name="nomor_unit" 
                   id="nomor_unit" 
                   class="form-control-new" 
                   value="{{ old('nomor_unit', $item->nomor_unit) }}" 
                   placeholder="Masukkan Nomor Meter"
                   required>
            @error('nomor_unit')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group-new">
            <label for="nama_petugas">Nama Petugas</label>
            <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
        </div>

        <div class="form-group-new">
            {{-- KEKAL: Label Stand Meter --}}
            <label for="stand_meter">Stand Meter</label>
            <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" value="{{ old('stand_meter', $item->stand_meter) }}" required>
        </div>

        {{-- ❌ Dihapus: Blok untuk Jumlah Siaga Keluar --}}
        
        <div class="form-group-new">
            <label for="status">Status</label>
            <input type="text" 
                    name="status" 
                    id="status" 
                    class="form-control-new" 
                    value="{{ $item->status ?? 'Keluar' }}" 
                    readonly 
                    style="background-color: #e9ecef; cursor: not-allowed;">
        </div>
        
        <div class="form-group-new">
            <label>Tanggal dan Jam</label>
            <input type="text" 
                    class="form-control-new" 
                    style="background-color: #e9ecef; cursor: not-allowed;"
                    value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}"
                    readonly>
            <small class="text-muted" style="display: block; margin-top: 5px;">
                Tanggal pembuatan data tidak dapat diubah.
            </small>
        </div>

        <div class="form-group-new">
            <label for="foto">Foto</label>
            {{-- 🖼️ Pratinjau Foto --}}
            @if($item->foto_path)
                <div style="margin-bottom: 10px;">
                    {{-- Menggunakan route show-foto dari Controller --}}
                    <img src="{{ route('siaga-keluar.show-foto', $item->id) }}" 
                            alt="Foto Lama" 
                            style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                </div>
            @endif
            <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Baru (Opsional)</label>
            {{-- Input foto tanpa atribut required (sudah benar) --}}
            <input type="file" name="foto" id="foto" class="form-control-new-file">
            <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                Biarkan kosong jika Anda tidak ingin mengubah foto yang sudah ada.
            </small>
                @error('foto')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-simpan">Update</button>
            <a href="{{ route('siaga-keluar.index') }}" class="btn-batal">Batal</a>
        </div>
    </form>
</div>


</div>
@endsection