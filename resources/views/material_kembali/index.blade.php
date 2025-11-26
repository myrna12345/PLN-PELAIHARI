@extends('layouts.app')

@section('title', 'Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-new">

    <div class="index-header">
        <h2>MATERIAL KEMBALI</h2>

        <!-- 🔍 FORM SEARCH -->
        <form action="{{ route('material_kembali.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material_kembali.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <!-- 📋 TABEL DATA -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah/Unit</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialKembali as $item)
                    <tr>
                        <td>{{ $materialKembali->firstItem() + $loop->index }}</td>
                        {{-- Asumsi: $item->nama_material berasal dari relasi atau accessor --}}
                        <td>{{ $item->material->nama_material ?? $item->nama_material }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah_material }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                        <!-- FOTO + DOWNLOAD -->
                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto)
                                {{-- SOLUSI FOTO PERMANEN --}}
                                @php
                                    $fotoUrl = '/storage/' . ltrim($item->foto, '/');
                                @endphp
                                
                                <img src="{{ $fotoUrl }}" 
                                    alt="Foto Material" 
                                    class="table-foto"
                                    style="cursor: pointer;"
                                    title="Klik untuk memperbesar">

                                <a href="{{ $fotoUrl }}" 
                                    download 
                                    class="btn-foto-download" 
                                    title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material_kembali.edit', $item->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('material_kembali.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6c757d; padding:50px 0;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div style="margin-top: 20px;">
        {{ $materialKembali->appends(request()->query())->links() }}
    </div>

    <!-- 📥 FOOTER: UNDUH PDF / EXCEL -->
    <div class="index-footer-form">
        <form action="{{ route('material_kembali.download') }}" method="POST" class="form-download">
            @csrf
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>

</div>
@endsection