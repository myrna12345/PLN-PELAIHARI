@extends('layouts.app')

@section('title', 'Edit Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-form-container mx-auto">
    <div class="card-form-header text-center mb-4">
        <h2>Edit Material Keluar</h2>
        
        {{-- Tampilkan error dari controller, misalnya stok tidak cukup --}}
        @if(session('error'))
            <div class="alert alert-danger text-center mb-3 mt-3">{{ session('error') }}</div>
        @endif
    </div>

    <div class="card-form-body">
        <form action="{{ route('material_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    <option value="">-- Pilih Material --</option>
                    @foreach($materialList as $material)
                        <option value="{{ $material->nama_material }}" 
                            {{ old('nama_material', $data->nama_material) == $material->nama_material ? 'selected' : '' }}>
                            {{ $material->nama_material }}
                        </option>
                    @endforeach
                </select>
                @error('nama_material') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>
            
            <div class="d-flex-group-form">
                {{-- Jumlah Material Keluar --}}
                <div class="form-group-new half-width">
                    <label for="jumlah_material">Jumlah Material Keluar</label>
                    <input type="number" 
                        name="jumlah_material" 
                        id="jumlah_material" 
                        class="form-control-new" 
                        placeholder="Masukkan jumlah material" 
                        value="{{ old('jumlah_material', $data->jumlah_material) }}" 
                        min="1"
                        required>
                    @error('jumlah_material') 
                        <small style="color:red;">{{ $message }}</small> 
                    @enderror
                </div>
                
                {{-- 🟢 PERBAIKAN: Satuan Material Keluar 🟢 --}}
                <div class="form-group-new half-width">
                    <label for="satuan_material">Satuan Material</label>
                    <select name="satuan_material" id="satuan_material" class="form-control-new" required>
                        <option value="" disabled>Pilih Satuan</option>
                        {{-- Menampilkan nilai lama atau nilai dari database --}}
                        @php $current_satuan = old('satuan_material', $data->satuan_material); @endphp
                        
                        <option value="Buah" {{ $current_satuan == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Meter" {{ $current_satuan == 'Meter' ? 'selected' : '' }}>Meter</option>
                        {{-- Tambahkan opsi lain di sini jika ada --}}
                    </select>
                    @error('satuan_material')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" 
                        class="form-control-new" 
                        value="{{ old('nama_petugas', $data->nama_petugas) }}" required>
                @error('nama_petugas') 
                    <small style="color:red;">{{ $message }}</small> 
                @enderror
            </div>

            <div class="form-group-new">
                <label for="tanggal_display">Tanggal dan Waktu</label>

                <input type="text" id="tanggal_display"
                    class="form-control-new"
                    value="{{ \Carbon\Carbon::parse($data->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }} WITA"
                    disabled>
                <small>Waktu dikeluarkan tidak bisa diubah.</small>
            </div>

            <input type="hidden" name="tanggal"
                value="{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d H:i:s') }}">


            <div class="form-group-new">
                <label for="foto">Foto</label>

                @if($data->foto)
                    <div style="margin-bottom: 10px;">
                        {{-- Menggunakan route show-foto untuk menampilkan gambar yang terlindungi --}}
                        <img src="{{ route('material_keluar.show-foto', $data->id) }}" 
                            alt="Foto Material Lama" 
                            class="table-foto"
                            style="max-width: 150px; height: auto;">
                    </div>
                @endif

                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                <small style="color: #777;">*Kosongkan jika tidak ingin mengganti foto.</small>

                @error('foto')
                    <small style="color:red;">{{ $message }}</small>
                @enderror
            </div>


            <div class="form-actions">
                <a href="{{ route('material_keluar.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert (tetap gunakan, asumsikan library sudah di-link) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    showConfirmButton: false,
    timer: 2000
});
</script>
@endif

{{-- CSS untuk tata letak bersebelahan (dari create.blade.php) --}}
<style>
    .d-flex-group-form {
        display: flex;
        gap: 20px; /* Jarak antar kolom */
    }
    .d-flex-group-form .half-width {
        flex: 1; /* Agar kedua kolom memiliki lebar yang sama */
    }
</style>
@endsection