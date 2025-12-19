@extends('layouts.app')

@section('title', 'Tambah Material Stand By')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Stand By</h2>
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
        
        <form action="{{ route('material-stand-by.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled selected>Pilih material</option>
                    {{-- Pastikan variabel $materials di passing dari controller --}}
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- HAPUS TOTAL: Blok Nomor Unit --}}
            {{--
            <div class="form-group-new">
                <label for="nomor_unit">Nomor Unit</label>
                <input type="text" name="nomor_unit" id="nomor_unit" class="form-control-new" value="{{ old('nomor_unit') }}" required>
            </div>
            --}}

            {{-- HAPUS TOTAL: Blok Stand Meter --}}
            {{--
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" value="{{ old('stand_meter') }}" required>
            </div>
            --}}
            
            {{-- 🟢 PERBAIKAN: Menggabungkan Input Jumlah dan Dropdown Satuan 🟢 --}}
            <div class="form-group-new">
                <label for="jumlah">Jumlah dan Satuan (Wajib)</label>
                <div style="display: flex; gap: 10px;">
                    
                    {{-- Input Jumlah (Manual Number Input) --}}
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                            value="{{ old('jumlah') }}" 
                            placeholder="Jumlah"
                            style="flex: 2;"
                            required min="1">
                    
                    {{-- Dropdown Satuan (Wajib) --}}
                    <select name="satuan" id="satuan" class="form-control-new" style="flex: 1; min-width: 120px;" required>
                        <option value="" disabled selected>Pilih Satuan</option>
                        <option value="Buah" {{ old('satuan') == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ old('satuan') == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Jumlah dan satuan wajib diisi.
                </small>
            </div>
            {{-- ⬆️ END KODE PERBAIKAN --}}
            
            <!-- Input Tanggal READONLY (Hanya Tampilan) -->
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Jam</label>
                <input type="text" 
                        id="tanggal_display" 
                        class="form-control-new" 
                        style="background-color: #e9ecef; cursor: not-allowed;"
                        value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}"
                        readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Waktu akan otomatis terisi saat data disimpan.
                </small>
                <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s') }}">
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto (Wajib)</label> 
                <input type="file" name="foto" id="foto" class="form-control-new-file" required>
                <small class="text-muted" style="display: block; margin-top: 5px; color: red;">
                    *Unggah foto material adalah wajib.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection