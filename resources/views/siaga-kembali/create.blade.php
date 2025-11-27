@extends('layouts.app')

@section('title', 'Tambah Siaga Kembali')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Siaga Kembali</h2>
    </div>

    <!-- Menampilkan Error Validasi -->
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
        <form action="{{ route('siaga-kembali.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
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
                <label for="jumlah_siaga_kembali">Jumlah Siaga Kembali</label>
                <input type="number" name="jumlah_siaga_kembali" id="jumlah_siaga_kembali" class="form-control-new" value="{{ old('jumlah_siaga_kembali') }}" min="1" required>
            </div>

            <!-- PERUBAHAN: Mengganti Keterangan menjadi Status (Readonly) -->
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control-new" value="Kembali" readonly style="background-color: #e9ecef; cursor: not-allowed;">
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