@extends('layouts.app')

@section('title', 'Edit Material Stand By')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Material Stand By</h2>
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
        
        <form action="{{ route('material-stand-by.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled>Pilih material...</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah/Unit</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control-new" value="{{ old('jumlah', $item->jumlah) }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Jam</label>
                <input type="text" 
                        id="tanggal_display" 
                        class="form-control-new" 
                        style="background-color: #e9ecef; cursor: not-allowed;"
                        value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}"
                        readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Tanggal pembuatan data tidak dapat diubah.
                </small>
            </div>

            {{-- 🖼️ KODE PERBAIKAN: Form Group Foto dengan Pratinjau 🖼️ --}}
            <div class="form-group-new">
                <label for="foto">Foto</label>
                
                @if($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        {{-- Menggunakan route show-foto dari Controller untuk menghindari masalah symlink --}}
                        <img src="{{ route('material-stand-by.show-foto', $item->id) }}" 
                             alt="Foto Saat Ini" 
                             style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif

                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>
            {{-- ⬆️ END KODE PERBAIKAN --}}

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection