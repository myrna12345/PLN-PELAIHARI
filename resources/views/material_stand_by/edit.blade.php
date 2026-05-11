@extends('layouts.app')

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
        <h2>Edit Data Material Stand By</h2>
    </div>
    <div class="card-form-body">
        <form action="{{ route('material-stand-by.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                {{-- ID material_id digunakan oleh script JavaScript --}}
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
                    {{-- Input Jumlah --}}
                    <input type="number" name="jumlah" class="form-control-new" value="{{ $item->jumlah }}" style="flex: 2;" required min="1" placeholder="Jumlah">
                    
                    {{-- Input Satuan (Otomatis & Readonly) --}}
                    <input type="text" name="satuan" id="satuan" class="form-control-new" 
                           value="{{ $item->satuan }}"
                           style="flex: 1; background-color: #e9ecef; cursor: not-allowed;" 
                           placeholder="Satuan" readonly required>
                </div>
            </div>

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
                @if($item->foto_path)
                    <img src="{{ asset('uploads/material_stand_by/' . $item->foto_path) }}" style="max-width: 150px; display: block; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
                @endif
                <label for="foto">Unggah Foto Material Baru (Opsional)</label>
                {{-- PERBAIKAN: Menggunakan onclick="this.value=null" agar foto tidak terhapus otomatis setelah diambil --}}
                <input type="file" name="foto" id="foto" class="form-control-new-file" 
                       accept="image/*" capture="environment" onclick="this.value=null">
                <small style="color: #6c757d; display: block; margin-top: 5px; font-style: italic;">
                    *Klik untuk mengambil foto baru menggunakan kamera.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Update</button>
                <a href="{{ route('material-stand-by.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT OTOMATISASI SATUAN --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const materialSelect = document.getElementById('material_id');
        const satuanInput = document.getElementById('satuan');

        if (materialSelect && satuanInput) {
            function updateSatuan() {
                const selectedText = materialSelect.options[materialSelect.selectedIndex].text.toUpperCase();

                if (selectedText.includes('KWH') || 
                    selectedText.includes('MCB') || 
                    selectedText.includes('AMPERE') || 
                    selectedText.includes('TRAFO') || 
                    selectedText.includes('FUSE') || 
                    selectedText.includes('NH') ||
                    selectedText.includes('CONNECTOR') ||
                    selectedText.includes('ISOLATOR') ||
                    selectedText.includes('LBS') ||
                    selectedText.includes('FCO')) {
                    
                    satuanInput.value = 'Buah';
                
                } else if (selectedText.includes('KABEL') || 
                           selectedText.includes('TWISTED') || 
                           selectedText.includes('SR') || 
                           selectedText.includes('NYY') || 
                           selectedText.includes('NYM') ||
                           selectedText.includes('METER')) {
                    
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