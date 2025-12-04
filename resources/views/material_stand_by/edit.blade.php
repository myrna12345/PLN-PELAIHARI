@extends('layouts.app')

@section('title', 'Edit Material Stand By')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Data Material Stand By</h2>
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
                    <option value="" disabled>Pilih material</option>
                    {{-- Pastikan variabel $materials di passing dari controller --}}
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- HAPUS: Nomor Unit --}}
            {{-- HAPUS: Stand Meter --}}
            
            {{-- Input Jumlah dan Satuan --}}
            <div class="form-group-new">
                <label for="jumlah">Jumlah dan Satuan (Wajib)</label>
                <div style="display: flex; gap: 10px;">
                    
                    {{-- Input Jumlah --}}
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                            value="{{ old('jumlah', $item->jumlah) }}" 
                            placeholder="Jumlah"
                            style="flex: 2;"
                            required min="1">
                    
                    {{-- Dropdown Satuan --}}
                    <select name="satuan" id="satuan" class="form-control-new" style="flex: 1; min-width: 120px;" required>
                        <option value="" disabled>Pilih Satuan</option>
                        <option value="Buah" {{ old('satuan', $item->satuan) == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ old('satuan', $item->satuan) == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Jumlah dan satuan wajib diisi.
                </small>
            </div>
            
            <!-- Input Tanggal READONLY (Hanya Tampilan) -->
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

            <div class="form-group-new">
                <label for="foto">Foto</label>
                {{-- 🖼️ Pratinjau Foto Lama --}}
                @if($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ route('material-stand-by.show-foto', $item->id) }}" 
                            alt="Foto Lama" 
                            style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                
                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection