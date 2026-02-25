@extends('layouts.app')

@section('title', 'Edit Material Retur')

@section('content')
<style>
    /* Perbaikan untuk tampilan input agar bersih dan menghilangkan dropdown otomatis browser */
    .form-control-new {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
    }
</style>

<div class="card-form-container">
    <div class="card-form-header">
        <h2>Edit Material Retur</h2>
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
        <form action="{{ route('material-retur.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
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
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas', $item->nama_petugas) }}" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah dan Satuan</label>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                           value="{{ old('jumlah', $item->jumlah) }}" placeholder="Jumlah" style="flex: 2;" required min="1">
                    
                    <input type="text" name="satuan" id="satuan" class="form-control-new" 
                           value="{{ old('satuan', $item->satuan) }}"
                           style="flex: 1; background-color: #e9ecef; cursor: not-allowed;" 
                           placeholder="Satuan" readonly required>
                </div>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="bekas_andal" {{ old('status', $item->status) == 'bekas_andal' ? 'selected' : '' }}>Bekas Andal</option>
                    <option value="rusak" {{ old('status', $item->status) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    <option value="baik" {{ old('status', $item->status) == 'baik' ? 'selected' : '' }}>Baik</option>
                </select>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" class="form-control-new" style="background-color: #e9ecef; cursor: not-allowed;"
                       value="{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3">{{ old('keterangan', $item->keterangan) }}</textarea>
            </div>

            {{-- --- BAGIAN FOTO MATERIAL --- --}}
            <div class="form-group-new">
                <label>Foto Material</label>
                @if ($item->foto_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('uploads/material_retur/' . $item->foto_path) }}" 
                             alt="Foto Material Saat Ini" 
                             style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto" style="display: block; margin-top: 10px;">Unggah Foto Material Baru (Opsional)</label>
                {{-- PERBAIKAN: Menggunakan onclick="this.value=null" agar foto tidak terhapus otomatis --}}
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*" capture="environment" onclick="this.value=null"> 
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Klik untuk mengambil foto baru menggunakan kamera.
                </small>
            </div>

            {{-- --- BAGIAN FOTO PETUGAS --- --}}
            <div class="form-group-new">
                <label>Foto Petugas</label>
                @if ($item->foto_petugas)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('uploads/material_retur/' . $item->foto_petugas) }}" 
                             alt="Foto Petugas Saat Ini" 
                             style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: block;">
                    </div>
                @endif
                <label for="foto_petugas" style="display: block; margin-top: 10px;">Unggah Foto Petugas Baru (Opsional)</label>
                {{-- PERBAIKAN: Menggunakan onclick="this.value=null" agar foto tidak terhapus otomatis --}}
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file" accept="image/*" capture="environment" onclick="this.value=null"> 
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    *Klik untuk mengambil foto baru menggunakan kamera.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-retur.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const materialSelect = document.getElementById('material_id');
        const satuanInput = document.getElementById('satuan');

        if (materialSelect && satuanInput) {
            function updateSatuan() {
                const selectedText = materialSelect.options[materialSelect.selectedIndex].text.toUpperCase();
                if (selectedText.includes('KWH') || selectedText.includes('MCB') || selectedText.includes('AMPERE') || 
                    selectedText.includes('TRAFO') || selectedText.includes('FUSE') || selectedText.includes('NH') ||
                    selectedText.includes('CONNECTOR') || selectedText.includes('ISOLATOR') || selectedText.includes('LBS') ||
                    selectedText.includes('FCO')) {
                    satuanInput.value = 'Buah';
                } else if (selectedText.includes('KABEL') || selectedText.includes('TWISTED') || selectedText.includes('SR') || 
                           selectedText.includes('NYY') || selectedText.includes('NYM') || selectedText.includes('METER')) {
                    satuanInput.value = 'Meter';
                } else {
                    satuanInput.value = 'Buah'; 
                }
            }
            materialSelect.addEventListener('change', updateSatuan);
        }
    });
</script>
@endsection