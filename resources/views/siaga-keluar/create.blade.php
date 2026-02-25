@extends('layouts.app')


@section('content')
<style>
    /* Perbaikan untuk menghilangkan tanda panah/dropdown pada textarea */
    .form-control-new {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
    }
</style>

<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Siaga Keluar</h2>
    </div>

    {{-- PERBAIKAN: Menghapus blok session('error') di sini karena kemungkinan besar sudah ditampilkan di layout utama (layouts.app), sehingga tidak muncul double --}}
    
    @if ($errors->any())
        <div class="alert alert-danger" role="alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Gagal:</strong> Mohon periksa kembali inputan Anda atau lengkapi data yang kosong.
        </div>
    @endif

    <div class="card-form-body">
        <form action="{{ route('siaga-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new @error('material_id') is-invalid @enderror" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nomor_meter">Nomor Meter</label> 
                <input type="text" name="nomor_meter" id="nomor_meter" class="form-control-new @error('nomor_meter') is-invalid @enderror" value="{{ old('nomor_meter') }}" placeholder="Masukkan Nomor Meter" required>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new @error('nama_petugas') is-invalid @enderror" value="{{ old('nama_petugas') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="stand_meter">Stand Meter</label>
                <input type="text" name="stand_meter" id="stand_meter" class="form-control-new @error('stand_meter') is-invalid @enderror" value="{{ old('stand_meter') }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new @error('keterangan') is-invalid @enderror" placeholder="Masukkan keterangan material keluar" required>{{ old('keterangan') }}</textarea>
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*keterangan wajib diisi</small>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto Material</label> 
                {{-- KODE KETAT: Menambahkan capture="environment" dan onfocus untuk memicu kamera secara paksa --}}
                <input type="file" name="foto" id="foto" class="form-control-new-file @error('foto') is-invalid @enderror" 
                       accept="image/*" capture="environment" onfocus="this.value=''" required> 
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*Foto material wajib diisi (Ambil Foto Kamera).</small>
            </div>

            <div class="form-group-new">
                <label for="foto_petugas">Unggah Foto Petugas</label> 
                {{-- KODE KETAT: Menambahkan capture="environment" dan onfocus untuk memicu kamera secara paksa --}}
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file @error('foto_petugas') is-invalid @enderror" 
                       accept="image/*" capture="environment" onfocus="this.value=''" required> 
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*Foto petugas wajib diisi (Ambil Foto Kamera).</small>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control-new" value="{{ old('status', 'Keluar') }}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" class="form-control-new" style="background-color: #e9ecef; cursor: not-allowed;" value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
                <a href="{{ route('siaga-keluar.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection