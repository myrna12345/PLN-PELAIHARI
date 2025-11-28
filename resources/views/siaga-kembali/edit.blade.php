@extends('layouts.app')

@section('title', 'Edit Siaga Kembali')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Material Siaga Kembali (ID: {{ $item->id }})</h2>
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
        <form action="{{ route('siaga-kembali.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            {{-- Form Nama Material (Dropdown) --}}
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled>Pilih material</option>
                    @foreach($materials as $material)
                        @php
                            $selected = (old('material_id') == $material->id) || ($item->material_id == $material->id);
                        @endphp
                        <option value="{{ $material->id }}" {{ $selected ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Form Group Nomor Unit --}}
            <div class="form-group-new">
                <label for="nomor_unit">Nomor Unit</label>
                <select name="nomor_unit" id="nomor_unit" class="form-control-new" required>
                    <option value="" disabled selected>Pilih Nomor</option>
                    @for ($i = 1; $i <= 50; $i++)
                        @php
                            $selected_unit = (old('nomor_unit') == $i) || ($item->nomor_unit == $i);
                        @endphp
                        <option value="{{ $i }}" {{ $selected_unit ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            {{-- Form Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" 
                       value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
            </div>

            {{-- Form Stand Meter --}}
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" 
                       value="{{ old('stand_meter', $item->stand_meter) }}" required>
            </div>

            {{-- Form Jumlah Siaga Kembali --}}
            <div class="form-group-new">
                <label for="jumlah_siaga_kembali">Jumlah Siaga Kembali</label>
                <input type="number" name="jumlah_siaga_kembali" id="jumlah_siaga_kembali" class="form-control-new" 
                       value="{{ old('jumlah_siaga_kembali', $item->jumlah_siaga_kembali) }}" min="1" required>
            </div>

            {{-- Status (Readonly) --}}
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control-new" 
                       value="{{ old('status', $item->status ?? 'Kembali') }}" 
                       readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>

            {{-- 🖼️ Form Group Foto dengan Pratinjau (Tanpa Tombol Unduh) 🖼️ --}}
            <div class="form-group-new">
                <label for="foto">Foto</label>
                @if ($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('siaga-kembali.show-foto', $item->id) }}" 
                             alt="Foto Saat Ini" 
                             style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                        
                        {{-- ❌ BARIS TOMBOL UNDUH DIHAPUS ❌ --}}
                    </div>
                @endif
                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>
            {{-- ⬆️ END KODE MODIFIKASI --}}

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection