@extends('layouts.app')

@section('title', 'Edit Siaga Keluar')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Data Siaga Keluar</h2>
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
        <form action="{{ route('siaga-keluar.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
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

            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
            </div>

            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" value="{{ old('stand_meter', $item->stand_meter) }}" required>
            </div>

            <div class="form-group-new">
                <label for="jumlah_siaga_keluar">Jumlah Siaga Keluar</label>
                <input type="number" name="jumlah_siaga_keluar" id="jumlah_siaga_keluar" class="form-control-new" value="{{ old('jumlah_siaga_keluar', $item->jumlah_siaga_keluar) }}" min="1" required>
            </div>
            
            <!-- PERBAIKAN: Menambahkan input Status (ReadOnly) -->
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
                <label for="foto">Unggah Foto (Opsional: Ganti foto)</label>
                @if($item->foto_path)
                    <img src="{{ asset('storage/' . ltrim($item->foto_path, '/')) }}" alt="Foto Lama" width="150" style="margin-bottom:10px; display:block; border-radius: 5px;">
                @endif
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('siaga-keluar.index') }}" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection