@extends('layouts.app')

@section('content')
<style>
/* ===== FOOTER DOWNLOAD SAJA ===== */
.index-footer-form {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.form-download {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}

.form-group-tanggal label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.form-control-tanggal {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
}

.btn-pdf {
    background: #fde2e2;
    color: #dc2626;
    border: none;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
}

.btn-excel {
    background: #dcfce7;
    color: #166534;
    border: none;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
}

/* PERBAIKAN: CSS Khusus untuk memposisikan konten foto & tombol ke tengah */
.wrapper-foto-tengah {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}
</style>

<div class="card-new">

    {{-- HEADER --}}
    <div class="index-header">
        <h2>LAPORAN MATERIAL STAND BY</h2>

        <form action="{{ route('material-stand-by.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search"
                       placeholder="Cari Nama Material"
                       value="{{ request('search') }}">
            </div>

            <input type="date" name="tanggal_mulai"
                   class="form-control-tanggal"
                   value="{{ request('tanggal_mulai') }}">

            <input type="date" name="tanggal_akhir"
                   class="form-control-tanggal"
                   value="{{ request('tanggal_akhir') }}">

            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-stand-by.index') }}"
               class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Jumlah</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>
                    <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                    <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td>{{ $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                    <td class="text-center">
                        @if($item->foto_path)
                            {{-- Perbaikan Posisi Tombol Download: Menggunakan wrapper foto tengah --}}
                            <div class="wrapper-foto-tengah">
                                <img src="{{ asset('uploads/material_stand_by/' . $item->foto_path) }}"
                                     style="max-width:80px; display:block; margin-bottom: 6px;">
                                
                                <a href="{{ route('material-stand-by.download-foto', $item->id) }}"
                                   class="btn-foto-download">
                                   <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        <div class="table-actions">
                            <a href="{{ route('material-stand-by.edit', $item->id) }}"
                               class="btn btn-edit">Edit</a>

                            <form action="{{ route('material-stand-by.destroy', $item->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-hapus">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
            <tr>
                {{-- PERBAIKAN: Menambahkan text-center dan padding agar benar-benar di tengah --}}
                <td colspan="6" class="text-center" style="text-align: center; vertical-align: middle; padding: 40px 0; font-weight: 500; color: #6b7280;">
                    Data tidak ditemukan.
                </td>
            </tr>
        @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER DOWNLOAD --}}
    <div class="index-footer-form">
        <form action="{{ route('material-stand-by.pdf') }}"
              method="POST"
              class="form-download">
            @csrf

            <div class="form-group-tanggal">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai"
                       class="form-control-tanggal" required>
            </div>

            <div class="form-group-tanggal">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir"
                       class="form-control-tanggal" required>
            </div>

            <button type="submit" class="btn-pdf">
                Unduh PDF
            </button>

            <button type="submit"
                    formaction="{{ route('material-stand-by.excel') }}"
                    class="btn-excel">
                Unduh Excel
            </button>
        </form>
    </div>

</div>
@endsection