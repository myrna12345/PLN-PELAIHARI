@extends('layouts.app')

@section('title', 'Saldo Material Siaga - SIMAS-PLN')

@section('content')

<div class="card-new">
    
    <div class="index-header">
        <h2 style="text-align: left; font-weight: bold;">LAPORAN MATERIAL SIAGA STAND BY</h2>

        <form action="{{ route('material-siaga.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material/Nomor meter" value="{{ request('search') }}">
            </div>
            {{-- Perbaikan: Gunakan start_date dan end_date sesuai controller --}}
            <div class="form-group-tanggal-filter">
                <input type="date" name="start_date" class="form-control-tanggal" value="{{ request('start_date') }}" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="end_date" class="form-control-tanggal" value="{{ request('end_date') }}" title="Tanggal Akhir">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-siaga.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table" style="width: 100%; border-collapse: collapse; background-color: white;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="width: 5%; text-align: center; border: 1px solid #dee2e6; padding: 12px;">No</th>
                    <th style="width: 30%; text-align: left; border: 1px solid #dee2e6; padding-left: 20px;">Nama Material & Nomor Meter</th> 
                    <th style="width: 10%; text-align: center; border: 1px solid #dee2e6;">Stand Meter</th>
                    <th style="width: 15%; text-align: center; border: 1px solid #dee2e6;">Tanggal Input</th>
                    <th style="width: 15%; text-align: center; border: 1px solid #dee2e6;">Foto & Download</th>
                    <th style="width: 10%; text-align: center; border: 1px solid #dee2e6;">Status</th>
                    <th style="width: 15%; text-align: center; border: 1px solid #dee2e6;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataSiaga as $data)
                <tr>
                    <td style="text-align: center; border: 1px solid #dee2e6; vertical-align: middle;">{{ $loop->iteration + ($dataSiaga->firstItem() - 1) }}</td>
                    
                    {{-- Nama Material & Nomor Meter Sejajar Kiri --}}
                    <td style="vertical-align: middle; text-align: left; border: 1px solid #dee2e6; padding-left: 20px;">
                        <div style="text-transform: uppercase; color: #333; font-weight: normal; letter-spacing: 0.5px;">
                            {{ $data->nama_material }} - {{ $data->nomor_meter }}
                        </div>
                    </td>

                    <td style="text-align: center; border: 1px solid #dee2e6; vertical-align: middle;">{{ $data->stand_meter }}</td>

                    <td style="text-align: center; border: 1px solid #dee2e6; vertical-align: middle;">
                        {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y, H:i') }}
                    </td>
                    
                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 10px;">
                        @if ($data->unggah_foto)
                            <div class="img-wrapper" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                <img src="{{ asset('storage/' . $data->unggah_foto) }}" 
                                     class="table-foto zoomable" 
                                     style="width: 85px; height: auto; border-radius: 4px; cursor: pointer; border: 1px solid #ddd;"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                
                                <a href="{{ asset('storage/' . $data->unggah_foto) }}" 
                                   download="foto_{{ $data->nomor_meter }}" 
                                   style="background-color: #5bc0de; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 11px; display: inline-flex; align-items: center; gap: 5px; font-weight: bold; width: fit-content;">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>

                                <div class="modal-image" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); justify-content:center; align-items:center; z-index:9999;">
                                    <span class="close-modal" style="position:absolute; top:20px; right:30px; font-size:45px; color:white; cursor:pointer;">&times;</span>
                                    <img src="{{ asset('storage/' . $data->unggah_foto) }}" style="max-width:90%; max-height:90%; border-radius:8px; border: 4px solid white;">
                                </div>
                            </div>
                        @else
                            <span class="text-muted" style="font-size: 11px;">Tidak ada foto</span>
                        @endif
                    </td>

                    <td style="vertical-align: middle; text-align: center; border: 1px solid #dee2e6; text-transform: capitalize;">
                        {{ $data->status }}
                    </td>

                    <td style="vertical-align: middle; border: 1px solid #dee2e6;">
                        <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                            <a href="{{ route('material-siaga.edit', $data->id) }}" 
                               style="background-color: #82c49e; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 11px; width: 65px; text-align: center; font-weight: normal;">
                                Edit
                            </a>

                            <form action="{{ route('material-siaga.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        style="background-color: #e57373; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; width: 65px; text-align: center;">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 30px; color: #999; border: 1px solid #dee2e6;">Data tidak ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer: Tombol Export dengan posisi tulisan di atas --}}
    <div class="index-footer-form" style="margin-top: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
        <form action="{{ route('material-siaga.export') }}" method="GET" style="display:flex; align-items: flex-end; gap:15px;">
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-size: 13px; font-weight: bold; color: #444; margin-bottom: 0;">Dari Tanggal:</label>
                <input type="date" name="start_date" class="form-control" style="width: 160px; padding: 6px; border: 1px solid #ced4da; border-radius: 4px;" value="{{ request('start_date') }}" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-size: 13px; font-weight: bold; color: #444; margin-bottom: 0;">Sampai Tanggal:</label>
                <input type="date" name="end_date" class="form-control" style="width: 160px; padding: 6px; border: 1px solid #ced4da; border-radius: 4px;" value="{{ request('end_date') }}" required>
            </div>

            <div style="display: flex; gap: 10px;">
                {{-- name="export" value="pdf" disesuaikan dengan Controller anda --}}
                <button type="submit" name="export" value="pdf" style="background-color: #fce4e4; color: #c62828; border: 1px solid #f9cccc; padding: 8px 18px; border-radius: 4px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 13px; height: 38px;">
                    <i class="fas fa-file-pdf" style="color: #e53935;"></i> Unduh Pdf
                </button>
                <button type="submit" name="export" value="excel" style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 8px 18px; border-radius: 4px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 13px; height: 38px;">
                    <i class="fas fa-file-excel" style="color: #43a047;"></i> Unduh Excel
                </button>
            </div>
        </form>

        <div class="pagination-wrapper">
            {{ $dataSiaga->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".zoomable").forEach((img) => {
        img.addEventListener("click", function() {
            const wrapper = this.closest('.img-wrapper');
            if(wrapper) {
                const modal = wrapper.querySelector(".modal-image");
                modal.style.display = "flex";
            }
        });
    });

    document.querySelectorAll(".close-modal").forEach(closeBtn => {
        closeBtn.addEventListener("click", function() {
            this.closest(".modal-image").style.display = "none";
        });
    });

    document.querySelectorAll(".modal-image").forEach(modal => {
        modal.addEventListener("click", function(e) {
            if (e.target === this) {
                this.style.display = "none";
            }
        });
    });
});
</script>
@endpush