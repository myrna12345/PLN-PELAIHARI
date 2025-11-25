@extends('layouts.app')

@section('title', 'Lihat Siaga Keluar - SIMAS-PLN')

@section('content')
<div class="card-new">

    {{-- HEADER: Judul + Search --}}
    <div class="index-header">
        <h2>Lihat Siaga Keluar</h2>

        <div class="search-bar">
            <i class="fas fa-search"></i>
            <form action="{{ route('siaga-keluar.index') }}" method="GET">
                <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}">
            </form>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nama Material</th> {{-- Kolom yang bermasalah --}}
                    <th>Nama Petugas</th>
                    <th>Stand Meter</th>
                    <th>Jumlah Siaga Keluar</th>
                    <th>Siaga Kembali</th> 
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                {{-- Pastikan nama variabel adalah $dataSiagaKeluar (dari Controller) --}}
                @forelse ($dataSiagaKeluar as $data) 
                    <tr>
                        <td>{{ $data->id }}</td>

                        {{-- PERBAIKAN UTAMA: Akses langsung kolom 'nama_material' --}}
                        {{-- Jika kolom ini masih kosong di DB, yang muncul adalah '-' --}}
                        <td>{{ $data->nama_material ?? '-' }}</td> 

                        <td>{{ $data->nama_petugas }}</td>
                        <td>{{ $data->stand_meter }}</td>

                        <td>{{ $data->jumlah_siaga_keluar }}</td>
                        
                        {{-- Asumsi 'jumlah_siaga_kembali' adalah kolom di tabel Siaga Keluar 
                           untuk menunjukkan referensi kembali --}}
                        <td>{{ $data->jumlah_siaga_kembali ?? '-' }}</td> 

                        {{-- Format tanggal --}}
                        <td class="local-datetime" data-timestamp="{{ $data->tanggal }}">
                            {{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->translatedFormat('d M Y, H:i') : '-' }}
                        </td>

                        <td>
                            @if ($data->foto)
                                <img src="{{ asset('storage/' . $data->foto) }}" class="table-foto" style="max-height:50px; border-radius: 8px;">
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $data->status ?? '-' }}</td>

                        {{-- Aksi --}}
                        <td class="table-actions">
                            <a href="{{ route('siaga-keluar.edit', $data->id) }}" class="btn-edit">Edit</a>

                            {{-- Hapus tanpa pop-up alert: pakai custom modal atau konfirmasi di sini --}}
                            <form action="{{ route('siaga-keluar.destroy', $data->id) }}" 
                                    method="POST" 
                                    style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;">Tidak ada data siaga keluar.</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    {{-- FOOTER --}}
    <div class="index-footer-form">
        
        {{-- Download PDF & Excel (Pastikan rute sudah terdefinisi) --}}
        <div class="form-download">
            <form action="{{ route('siaga-keluar.index') }}" method="GET" style="display:inline;">
                <button type="submit" name="export" value="pdf" class="btn-pdf">
                    <i class="fas fa-file-pdf"></i> Unduh PDF
                </button>
            </form>

            <form action="{{ route('siaga-keluar.index') }}" method="GET" style="display:inline;">
                <button type="submit" name="export" value="excel" class="btn-excel">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </button>
            </form>
        </div>

        {{-- PAGINATION --}}
        <div class="pagination-links">
            {{ $dataSiagaKeluar->links() }}
        </div>

    </div>

</div>
@endsection

{{-- Asumsi Anda memiliki CSS dasar ini di file 'layouts.app' atau terpisah --}}
@push('styles')
<style>
    /* Tambahkan style jika diperlukan */
    .table-foto {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }
    .index-footer-form {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
    }
    .form-download button {
        margin-right: 10px;
    }
    .btn-pdf { background-color: #dc3545; color: white; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; }
    .btn-excel { background-color: #28a745; color: white; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; }
    .btn-edit { background-color: #007bff; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; }
    .btn-hapus { background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; border: none; cursor: pointer; }

    /* Style Pagination */
    .pagination-links nav {
        display: flex;
        justify-content: flex-end;
    }
    .pagination-links .pagination {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        border-radius: 5px;
        overflow: hidden;
    }
    .pagination-links .page-item .page-link {
        padding: 8px 12px;
        text-decoration: none;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        margin-left: -1px;
    }
    .pagination-links .page-item.active .page-link {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }
</style>
@endpush