@extends('layouts.app')

@section('title', 'Tambah Material Retur')

@section('content')
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Retur</h2>
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
        <form action="{{ route('material-retur.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                {{-- ID material_id digunakan oleh script JavaScript --}}
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
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="{{ old('nama_petugas') }}" required placeholder="Masukkan nama petugas">
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah dan Satuan</label>
                <div style="display: flex; gap: 10px;">
                    {{-- Input Jumlah --}}
                    <input type="number" name="jumlah" id="jumlah" class="form-control-new" 
                           value="{{ old('jumlah') }}" placeholder="Jumlah" style="flex: 2;" required min="1">
                    
                    {{-- Input Satuan (Teks Readonly, Otomatis) --}}
                    <input type="text" name="satuan" id="satuan" class="form-control-new" 
                           style="flex: 1; background-color: #e9ecef; cursor: not-allowed;" 
                           placeholder="Satuan" readonly required>
                </div>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="" disabled selected>Pilih status</option>
                    <option value="bekas_andal" {{ old('status') == 'bekas_andal' ? 'selected' : '' }}>Bekas Andal</option> 
                    <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3" placeholder="Tambahkan keterangan material" required>{{ old('keterangan') }}</textarea>
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*keterangan wajib diisi</small>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" class="form-control-new" style="background-color: #e9ecef; cursor: not-allowed;"
                       value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto Material</label> 
                <input type="file" name="foto" id="foto" class="form-control-new-file" required>
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*foto material wajib diisi</small>
            </div>

            <div class="form-group-new">
                <label for="foto_petugas">Unggah Foto Petugas</label> 
                <input type="file" name="foto_petugas" id="foto_petugas" class="form-control-new-file" required>
                <small style="color: red; display: block; margin-top: 5px; font-style: italic;">*foto petugas wajib diisi</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
                <a href="{{ route('material-retur.index') }}" class="btn-batal" style="text-decoration: none; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; margin-left: 10px;">Batal</a>
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

                // LOGIKA: Prioritas Buah (KWH/MCB), sisanya Meter (Kabel)
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