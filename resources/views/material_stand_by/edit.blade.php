@extends('layouts.app')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Data Material Stand By</h2>
    </div>
    <div class="card-form-body">
        <form action="{{ route('material-stand-by.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ $item->material_id == $material->id ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-new">
                <label>Jumlah dan Satuan</label>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="jumlah" class="form-control-new" value="{{ $item->jumlah }}" style="flex: 2;" required min="1">
                    <select name="satuan" class="form-control-new" style="flex: 1;" required>
                        <option value="Buah" {{ $item->satuan == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ $item->satuan == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>
            </div>

            {{-- Menampilkan Tanggal Pembuatan (Readonly) --}}
            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Jam Pembuatan</label>
                <input type="text" id="tanggal_display" class="form-control-new" 
                       style="background-color: #e9ecef; cursor: not-allowed;" 
                       value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Tanggal pembuatan data tidak dapat diubah.
                </small>
            </div>

            <div class="form-group-new">
                <label>Foto Material Saat Ini</label>
                <img src="{{ route('material-stand-by.show-foto', $item->id) }}" style="max-width: 150px; display: block; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
                <label for="foto">Unggah Foto Material Baru (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-group-new">
                <label>Foto Petugas Saat Ini</label>
                @if($item->foto_petugas)
                    {{-- Pastikan kamu ada route show-foto-petugas jika menggunakan symlink protection, atau gunakan asset() jika publik --}}
                    <img src="{{ asset('storage/' . $item->foto_petugas) }}" style="max-width: 150px; display: block; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
                @endif
                <label for="foto_petugas">Unggah Foto Petugas Baru (Opsional)</label>
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection