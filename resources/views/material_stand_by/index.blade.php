@extends('layouts.app')

@section('title', 'Saldo Material Stand By')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>SALDO MATERIAL STAND BY</h2>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search">
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah/Unit</th>
                    <th>Tanggal (Jam Lokal)</th> <th>Foto</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah }}</td>
                        
                        <td class="local-datetime" data-timestamp="{{ $item->tanggal->toIso8601String() }}">
                            Memuat...
                        </td>
                        
                        <td>
                            @if($item->foto_path)
                                <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Material" class="table-foto">
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material-stand-by.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('material-stand-by.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $items->links() }}
    </div>

    <div class="index-footer">
        <a href="#" class="btn btn-pdf"><i class="fas fa-file-pdf"></i> Unduh Pdf</a>
        <a href="#" class="btn btn-excel"><i class="fas fa-file-excel"></i> Unduh Excel</a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Jalankan script ini setelah halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Cari semua sel tabel dengan class 'local-datetime'
        document.querySelectorAll('.local-datetime').forEach(function(cell) {
            try {
                // 1. Ambil timestamp server dari atribut data-timestamp
                const serverTimestamp = cell.dataset.timestamp;
                
                // 2. Buat objek Date. Ini otomatis diubah ke ZONA WAKTU DEVICE
                const localDate = new Date(serverTimestamp);

                // 3. Opsi format (Hari, Bulan, Tahun, Jam, Menit)
                const options = {
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: false // Gunakan format 24 jam
                };

                // 4. Ubah teks di dalam sel menjadi format jam lokal
                cell.textContent = new Intl.DateTimeFormat('id-ID', options).format(localDate);

            } catch (e) {
                console.error('Gagal memformat tanggal:', e);
                cell.textContent = 'Invalid Date'; 
            }
        });
    });
</script>
@endpush