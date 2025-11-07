@extends('layouts.app')

{{-- Set Judul Halaman --}}
@section('title', 'Lihat Siaga Keluar - SIMAS-PLN')

{{-- Set Konten Halaman --}}
@section('content')
<div class="card-new">
    <div class="index-header">
        <h2>Lihat Siaga Keluar</h2>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            {{-- Form untuk pencarian --}}
            <form action="{{ route('siaga-keluar.index') }}" method="GET">
                <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}">
            </form>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Stand Meter</th>
                    <th>Siaga Keluar</th>
                    <th>Siaga Kembali</th>
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop data siaga keluar --}}
                @forelse ($dataSiagaKeluar as $data)
                    <tr>
                        <td>{{ $data->id }}</td>
                        {{-- Ambil nama material dari relasi --}}
                        <td>{{ $data->materialStandBy->nama_material ?? 'N/A' }}</td>
                        <td>{{ $data->nama_petugas }}</td>
                        <td>{{ $data->stand_meter }}</td>
                        <td>{{ $data->jumlah_siaga_keluar }}</td>
                        <td>{{ $data->jumlah_siaga_kembali }}</td>
                        {{-- Format tanggal --}}
                        <td class="local-datetime" data-timestamp="{{ $data->tanggal }}">
                            {{ $data->tanggal }}
                        </td>
                        <td>
                            @if ($data->foto)
                                <img src="{{ asset('storage/' . $data->foto) }}" alt="Foto" class="table-foto">
                                {{-- TODO: Tambahkan link download foto jika perlu --}}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{-- TODO: Buat ini menjadi dropdown jika status bisa diubah --}}
                            {{ $data->status }}
                        </td>
                        <td class="table-actions">
                            {{-- Tombol Edit --}}
                            <a href="{{ route('siaga-keluar.edit', $data->id) }}" class="btn-edit">Edit</a>
                            
                            {{-- Form Hapus --}}
                            <form action="{{ route('siaga-keluar.destroy', $data->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-hapus">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    {{-- Jika data kosong --}}
                    <tr>
                        <td colspan="10" style="text-align: center;">Tidak ada data siaga keluar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer/Form Download --}}
    <div class="index-footer-form">
        {{-- Form Download PDF & Excel --}}
        <div class="form-download">
            <form action="#" method="GET" style="display:inline;"> {{-- TODO: Ganti '#' dengan route download PDF --}}
                <button type="submit" class="btn-pdf"><i class="fas fa-file-pdf"></i> Unduh PDF</button>
            </form>
            <form action="#" method="GET" style="display:inline;"> {{-- TODO: Ganti '#' dengan route download Excel --}}
                <button type="submit" class="btn-excel"><i class="fas fa-file-excel"></i> Unduh Excel</button>
            </form>
        </div>
    </div>
</div>
@endsection