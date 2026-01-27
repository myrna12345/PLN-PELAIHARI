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
                {{-- ID material_id digunakan oleh script JavaScript --}}
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
                    {{-- Input Jumlah --}}
                    <input type="number" name="jumlah" class="form-control-new" style="flex: 2;" required min="1" placeholder="Jumlah">
                    
                    {{-- Input Satuan (Otomatis & Readonly) --}}
                    <input type="text" name="satuan" id="satuan" class="form-control-new" 
                           style="flex: 1; background-color: #e9ecef; cursor: not-allowed;" 
                           placeholder="Satuan" readonly required>
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

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
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
                // Ambil teks nama material dan ubah ke huruf besar
                const selectedText = materialSelect.options[materialSelect.selectedIndex].text.toUpperCase();

                // LOGIKA BARU: Cek KWH/MCB/Komponen "Buah" Terlebih Dahulu (Prioritas Utama)
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
                
                // Baru kemudian cek Kabel (Meter)
                } else if (selectedText.includes('KABEL') || 
                           selectedText.includes('TWISTED') || 
                           selectedText.includes('SR') || 
                           selectedText.includes('NYY') || 
                           selectedText.includes('NYM')) {
                    
                    satuanInput.value = 'Meter';
                
                } else {
                    // Default jika tidak dikenali
                    satuanInput.value = 'Buah'; 
                }
            }

            // Jalankan fungsi saat dropdown material berubah
            materialSelect.addEventListener('change', updateSatuan);
        }
    });
</script>
@endsection