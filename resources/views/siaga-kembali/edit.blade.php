{{-- resources/views/siaga-kembali/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Siaga Kembali')

@section('content')

<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Material Siaga Kembali</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
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
                <select name="material_id" id="material_id" class="form-control-new @error('material_id') is-invalid @enderror" required>
                    <option value="" disabled {{ old('material_id', $item->material_id) == '' ? 'selected' : '' }}>Pilih material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('material_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Form Group Nomor Meter (Input Teks) --}}
            <div class="form-group-new">
                <label for="nomor_meter">Nomor Meter</label> 
                {{-- PERBAIKAN: Menggunakan $item->nomor_meter, yang match dengan nama kolom database yang baru --}}
                <input type="text" 
                       name="nomor_meter" 
                       id="nomor_meter" 
                       class="form-control-new @error('nomor_meter') is-invalid @enderror" 
                       value="{{ old('nomor_meter', $item->nomor_meter) }}" 
                       required>
                @error('nomor_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Form Nama Petugas --}}
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" 
                       name="nama_petugas" 
                       id="nama_petugas" 
                       class="form-control-new @error('nama_petugas') is-invalid @enderror" 
                       value="{{ old('nama_petugas', $item->nama_petugas) }}" 
                       required>
                @error('nama_petugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Form Stand Meter --}}
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" 
                       name="stand_meter" 
                       id="stand_meter" 
                       class="form-control-new @error('stand_meter') is-invalid @enderror" 
                       value="{{ old('stand_meter', $item->stand_meter) }}" 
                       required>
                @error('stand_meter')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status (Readonly) --}}
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" 
                       name="status" 
                       id="status" 
                       class="form-control-new" 
                       value="{{ old('status', $item->status ?? 'Kembali') }}" 
                       readonly 
                       style="background-color: #e9ecef; cursor: not-allowed;">
            </div>

            {{-- 🖼 Form Group Foto dengan Pratinjau 🖼 --}}
            <div class="form-group-new">
                <label for="foto">Foto</label>
                
                @if ($item->foto_path)
                    <p class="text-sm text-gray-600 mb-2 mt-1">Foto Saat Ini:</p>
                    <div style="margin-bottom: 15px; border: 1px solid #e0e0e0; padding: 5px; border-radius: 6px; display: inline-block;">
                        <img src="{{ route('siaga-kembali.show-foto', $item->id) }}" 
                             alt="Foto Saat Ini" 
                             style="max-width: 250px; height: auto; display: block; border-radius: 4px;">
                    </div>
                @else
                    <p class="text-sm text-gray-600 mb-2 mt-1">Belum ada foto yang diunggah.</p>
                @endif
                
                <label for="foto" style="display: block; margin-top: 10px; font-weight: 500;">Unggah Foto Baru (Opsional)</label>
                <input type="file" 
                       name="foto" 
                       id="foto" 
                       class="form-control-new-file @error('foto') is-invalid @enderror" 
                       accept="image/*">
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection