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
            
            <input type="hidden" name="nama_material_lengkap" value="{{ old('nama_material_lengkap', $item->nama_material_lengkap) }}">
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $item->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-new">
                <label for="nomor_meter">Nomor Meter (Wajib)</label>
                <input type="text" name="nomor_meter" id="nomor_meter" class="form-control-new" value="{{ old('nomor_meter', $item->nomor_meter) }}" required>
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
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" required>{{ old('keterangan', $item->keterangan) }}</textarea>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control-new" value="{{ $item->status ?? 'Keluar' }}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" class="form-control-new" style="background-color: #e9ecef; cursor: not-allowed;" value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
            </div>

            <div class="form-group-new">
                <label for="foto">Foto Material</label>
                @if($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('uploads/siaga_keluar/' . $item->foto_path) }}" alt="Foto Lama" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Material Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            {{-- Input Foto Petugas DITAMBAHKAN KEMBALI --}}
            <div class="form-group-new">
                <label for="foto_petugas">Foto Petugas</label>
                @if($item->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('uploads/siaga_keluar/' . $item->foto_petugas) }}" alt="Foto Petugas Lama" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto_petugas" style="display: block; margin-top: 10px;">Unggah Foto Petugas Baru (Opsional)</label>
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file">
            </div>


            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('siaga-keluar.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection