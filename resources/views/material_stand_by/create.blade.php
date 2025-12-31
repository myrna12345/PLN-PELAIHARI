@extends('layouts.app')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Stand By</h2>
    </div>
    <div class="card-form-body">
        <form action="{{ route('material-stand-by.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}">{{ $material->nama_material }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-new">
                <label for="jumlah">Jumlah dan Satuan</label>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="jumlah" class="form-control-new" style="flex: 2;" required min="1" placeholder="Jumlah">
                    <select name="satuan" class="form-control-new" style="flex: 1;" required>
                        <option value="" disabled selected>Satuan</option>
                        <option value="Buah">Buah</option>
                        <option value="Meter">Meter</option>
                    </select>
                </div>
            </div>

            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Jam</label>
                <input type="text" id="tanggal_display" class="form-control-new" 
                       style="background-color: #e9ecef; cursor: not-allowed;" 
                       value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Waktu otomatis terisi saat data disimpan.
                </small>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto Material</label> 
                <input type="file" name="foto" id="foto" class="form-control-new-file" required>
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">
                    *foto material wajib diisi
                </small>
            </div>

            {{-- Input Foto Petugas DIHAPUS --}}

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection