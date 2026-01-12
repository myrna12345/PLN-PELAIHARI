@extends('layouts.app')

@section('title', 'Saldo Material Siaga - SIMAS-PLN')

@section('content')

<style>
    .modal-overlay-custom {
        display: none;
        position: absolute;
        z-index: 50;
        inset: 0;
        background: rgba(0,0,0,0.7);
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(4px);
    }

    .modal-content-custom {
        max-width: 85%;
        max-height: 85%;
        border: 4px solid white;
        border-radius: 12px;
        box-shadow: 0 15px 50px rgba(0,0,0,.7);
        animation: zoomIn .3s ease-out;
    }

    @keyframes zoomIn {
        from { transform: scale(.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .close-btn-custom {
        position: absolute;
        top: 25px;
        right: 35px;
        font-size: 45px;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-download-custom {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #89c6d3;
        color: #1a3a4a;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        text-decoration: none;
        margin-top: 5px;
    }

    .card-new { position: relative; }
</style>

<div class="card-new">

    {{-- HEADER + FILTER --}}
    <div class="index-header">
        <h2 style="font-weight:bold;">LAPORAN MATERIAL SIAGA STAND BY</h2>

        <form action="{{ route('material-siaga.index') }}" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cari Nama / Nomor Meter" value="{{ request('search') }}">
            <input type="date" name="start_date" value="{{ request('start_date') }}">
            <input type="date" name="end_date" value="{{ request('end_date') }}">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-siaga.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="table-container">
        <table class="table" style="width:100%; background:white;">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Nama Material & Nomor Meter</th>
                    <th width="10%">Stand Meter</th>
                    <th width="15%">Tanggal(WITA)</th>
                    <th width="10%">Status</th>
                    <th width="15%">Foto</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($dataSiaga as $data)
                <tr>
                    <td align="center">{{ $loop->iteration + ($dataSiaga->firstItem() - 1) }}</td>

                    <td>{{ strtoupper($data->nama_material) }} - {{ $data->nomor_meter }}</td>

                    <td align="center">{{ $data->stand_meter }}</td>

                    <td align="center">
                        {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y H:i') }}
                    </td>

                    {{-- STATUS --}}
                    <td align="center">
                        @if($data->status === 'Ready')
                            <span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:bold;">
                                READY
                            </span>
                        @elseif($data->status === 'Terpakai')
                            <span style="background:#ffebee;color:#c62828;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:bold;">
                                TERPAKAI
                            </span>
                        @endif
                    </td>

                    {{-- FOTO --}}
                    <td align="center">
                        @if($data->unggah_foto && Storage::disk('public')->exists($data->unggah_foto))
                            <img src="{{ route('material-siaga.show-foto', $data->id) }}"
                                 width="70"
                                 style="cursor:pointer;border-radius:4px;border:1px solid #ddd"
                                 onclick="bukaFotoModal(this.src)">
                            <br>
                            <a href="{{ route('material-siaga.download-foto', $data->id) }}" class="btn-download-custom">
                                Download
                            </a>
                        @else
                            <span style="font-size:11px;color:#aaa;">Tidak ada foto</span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td align="center">
                        <a href="{{ route('material-siaga.edit', $data->id) }}"
                           style="background:#82c49e;color:white;padding:5px 10px;border-radius:4px;font-size:12px;">
                           Edit
                        </a>

                        <form action="{{ route('material-siaga.destroy', $data->id) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus data?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="background:#e57373;color:white;border:none;padding:5px 10px;border-radius:4px;font-size:12px;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" align="center">Data tidak ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

   {{-- FOOTER EXPORT --}}
<div
    class="index-footer-form"
    style="margin-top:35px; display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:20px;"
>
    <form
        action="{{ route('material-siaga.export') }}"
        method="GET"
        style="display:flex; align-items:flex-end; gap:15px;"
    >
        <div style="display:flex; flex-direction:column; gap:5px;">
            <label style="font-size:13px; font-weight:bold; color:#444;">
                Dari Tanggal:
            </label>
            <input
                type="date"
                name="start_date"
                class="form-control"
                style="width:160px;"
                value="{{ request('start_date') }}"
                required
            >
        </div>

        <div style="display:flex; flex-direction:column; gap:5px;">
            <label style="font-size:13px; font-weight:bold; color:#444;">
                Sampai Tanggal:
            </label>
            <input
                type="date"
                name="end_date"
                class="form-control"
                style="width:160px;"
                value="{{ request('end_date') }}"
                required
            >
        </div>

        <div style="display:flex; gap:10px;">
            <button
                type="submit"
                name="export"
                value="pdf"
                style="background-color:#fce4e4;color:#c62828;border:1px solid #f9cccc;
                       padding:8px 18px;border-radius:4px;font-weight:bold;
                       display:flex;align-items:center;gap:8px;font-size:13px;height:38px;"
            >
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>

            <button
                type="submit"
                name="export"
                value="excel"
                style="background-color:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;
                       padding:8px 18px;border-radius:4px;font-weight:bold;
                       display:flex;align-items:center;gap:8px;font-size:13px;height:38px;"
            >
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </div>
    </form>
</div>


{{-- MODAL FOTO --}}
<div id="modalContainer" class="modal-overlay-custom" onclick="tutupFotoModal()">
    <span class="close-btn-custom">&times;</span>
    <img id="fotoBesar" class="modal-content-custom" onclick="event.stopPropagation()">
</div>

<script>
    function bukaFotoModal(src) {
        document.getElementById('fotoBesar').src = src;
        document.getElementById('modalContainer').style.display = 'flex';
    }
    function tutupFotoModal() {
        document.getElementById('modalContainer').style.display = 'none';
    }
</script>

@endsection
