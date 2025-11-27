@extends('layouts.app')

@section('title', 'Tambah Siaga Keluar')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Siaga Keluar</h2>
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
        
        <form action="{{ route('siaga-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- BAGIAN INI YANG DIPERBAIKI -->
            <!-- Menggunakan $materials dari database, bukan $oneP manual -->
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" value="{{ old('stand_meter') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah_siaga_keluar">Jumlah</label>
                <input type="number" name="jumlah_siaga_keluar" id="jumlah_siaga_keluar" class="form-control-new" value="{{ old('jumlah_siaga_keluar') }}" required min="1">
            </div>

            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control-new" value="{{ old('status', 'Keluar') }}" required>
            </div>
            
            <!-- Input Tanggal READONLY (Otomatis Waktu Sekarang) -->
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" 
                       class="form-control-new" 
                       style="background-color: #e9ecef; cursor: not-allowed;"
                       value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}"
                       readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Waktu akan otomatis terisi saat data disimpan.
                </small>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection