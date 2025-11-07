@extends('layouts.app')

@section('title', 'Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>MATERIAL KELUAR</h2>

        <!-- 🔍 FORM SEARCH -->
        <form action="{{ route('material_keluar.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material_keluar.index') }}" class="btn btn-secondary btn-sm">Reset</a>
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
                    <th style="width: 200px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialKeluar as $item)
                    <tr>
                        <td>{{ $materialKeluar->firstItem() + $loop->index }}</td>
                        <td>{{ $item->nama_material }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah_material }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                        <!-- FOTO + DOWNLOAD -->
                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto)
                                <img src="{{ asset('storage/' . $item->foto) }}" 
                                     alt="Foto Material" 
                                     class="table-foto" 
                                     style="width:70px; height:70px; object-fit:cover; border-radius:6px; display:block; margin:0 auto 8px; cursor:pointer;" 
                                     title="Klik untuk memperbesar">

                                <a href="{{ asset('storage/' . $item->foto) }}" 
                                   download 
                                   class="btn-foto-download" 
                                   title="Download Foto"
                                   style="display:inline-flex; align-items:center; gap:5px; padding:5px 8px; background:#3498db; color:#fff; border-radius:5px; font-size:0.85rem; text-decoration:none;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td style="text-align:center;">
                            <div class="table-actions" style="display:flex; justify-content:center; gap:8px; flex-wrap:wrap;">
                                <!-- Tombol Lihat -->
                                <a href="{{ route('material_keluar.lihat', $item->id) }}" 
                                   class="btn-lihat" 
                                   style="background:#17a2b8; color:#fff; padding:6px 12px; border-radius:6px; font-size:0.9rem; text-decoration:none;">
                                   👁 Lihat
                                </a>
                                
                                <a href="{{ route('material_keluar.edit', $item->id) }}" 
                                   class="btn-edit" 
                                   style="background:#ffc107; color:#000; padding:6px 12px; border-radius:6px; font-size:0.9rem; text-decoration:none;">
                                   ✏ Edit
                                </a>

                                <form action="{{ route('material_keluar.destroy', $item->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?')" 
                                      style="display:inline;">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-hapus" 
                                            style="background:#e74c3c; color:#fff; padding:6px 12px; border:none; border-radius:6px; font-size:0.9rem; cursor:pointer;">
                                            🗑 Hapus
                                    </button>
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
        {{ $materialKeluar->appends(request()->query())->links() }}
    </div>

    <!-- FOOTER BUTTON -->
    <div class="index-footer-form" style="text-align: right;">
        <button class="btn-pdf">📄 Unduh PDF</button>
        <button class="btn-excel">📊 Unduh Excel</button>
    </div>
</div>
@endsection