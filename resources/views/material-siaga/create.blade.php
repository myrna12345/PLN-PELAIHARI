@extends('layouts.app')

@section('title', 'Tambah Material Siaga Standby')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Siaga Standby</h2>
    </div>

    {{-- Alert Error --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Gagal:</strong> Mohon periksa kembali inputan Anda atau lengkapi data yang kosong.
        </div>
    @endif

    <div class="card-form-body">
        <form action="{{ route('material-siaga.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new @error('nama_material') is-invalid @enderror" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach ($materials as $mat)
                        <option value="{{ $mat->nama_material }}" {{ old('nama_material') == $mat->nama_material ? 'selected' : '' }}>
                            {{ $mat->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nomor_meter">Nomor Meter</label> 
                <input type="text" name="nomor_meter" id="nomor_meter" class="form-control-new @error('nomor_meter') is-invalid @enderror" value="{{ old('nomor_meter') }}" placeholder="Masukkan Nomor Meter" required>
            </div>
            
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new @error('stand_meter') is-invalid @enderror" value="{{ old('stand_meter') }}" placeholder="Masukkan Stand Meter" required>
            </div>

            <div class="form-group-new">
                <label for="unggah_foto">Unggah Foto</label> 
                <input type="file" name="unggah_foto" id="unggah_foto" class="form-control-new-file @error('unggah_foto') is-invalid @enderror" accept="image/*" required> 
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*Unggah foto material adalah wajib.</small>
            </div>

            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" class="form-control-new" style="background-color: #e9ecef; cursor: not-allowed;" value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
                <a href="{{ route('material-siaga.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection