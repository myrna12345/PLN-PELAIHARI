@extends('layouts.app')

@section('title', 'Saldo Material Siaga - SIMAS-PLN')

@section('content')

<div class="card-new">
    
    <div class="index-header">
        <h2>SALDO MATERIAL SIAGA STAND BY</h2>
        
        <form action="{{ route('material-siaga.index') }}" method="GET">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Stand Meter</th>
                    <th>Jumlah Siaga standby</th>
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataSiaga as $data)
                <tr>
                    <td>{{ $loop->iteration + ($dataSiaga->firstItem() - 1) }}</td>
                    
                    <td>{{ $data->nama_material }}</td>
                    <td>{{ $data->nama_petugas }}</td>
                    <td>{{ $data->stand_meter }}</td>
                    <td>{{ $data->jumlah_siaga_standby }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y H:i') }}</td>
                    
                    <td>
                        @if ($data->foto)
                            <!-- Thumbnail -->
                            <img src="{{ asset('storage/'.$data->foto) }}" 
                                 class="table-foto zoomable"
                                 alt="Foto" 
                                 style="width:60px; height:60px; object-fit:cover; border-radius:6px; cursor:pointer;">

                            <br>
                            <a href="{{ asset('storage/'.$data->foto) }}" download class="btn-foto-download" style="font-size:12px;">
                                <i class="fas fa-download"></i> Unduh
                            </a>

                            <!-- Modal -->
                            <div class="modal-image" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
                                <span class="close-modal" style="position:absolute; top:20px; right:30px; font-size:35px; color:white; cursor:pointer;">&times;</span>
                                <img src="{{ asset('storage/'.$data->foto) }}"
                                     style="max-width:90%; max-height:90%; border-radius:10px;">
                            </div>

                        @else
                            -
                        @endif
                    </td>

                    <td>
                        <form action="{{ route('material-siaga.update-status', $data->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="form-control" style="padding:6px; border-radius:6px;">
                                <option value="Ready" {{ $data->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                                <option value="Terpakai" {{ $data->status == 'Terpakai' ? 'selected' : '' }}>Terpakai</option>
                            </select>
                        </form>
                    </td>

                    <td>
                        <div class="table-actions">
                            <a href="{{ route('material-siaga.edit', $data->id) }}" class="btn-edit">Edit</a>

                            <form action="{{ route('material-siaga.destroy', $data->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-hapus">Hapus</button>
                            </form>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data ditemukan.</td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <div class="index-footer-form">
        <form action="{{ route('material-siaga.export') }}" method="GET" class="form-download" style="display:flex; align-items:center; gap:14px;">

            <input type="date" name="start_date" class="form-control" style="padding:6px; border-radius:6px;" required>
            <input type="date" name="end_date" class="form-control" style="padding:6px; border-radius:6px;" required>

            <button type="submit" name="export" value="pdf" class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>

            <button type="submit" name="export" value="excel" class="btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>

        </form>

        <div>
            {{ $dataSiaga->appends(['search' => request('search')])->links() }}
        </div>
    </div>

</div>

@endsection


<!-- SCRIPT ZOOM GAMBAR -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".zoomable").forEach((img) => {
        img.addEventListener("click", function() {
            let modal = this.nextElementSibling.nextElementSibling;
            modal.style.display = "flex";
        });
    });

    document.querySelectorAll(".close-modal").forEach(closeBtn => {
        closeBtn.addEventListener("click", function() {
            this.parentElement.style.display = "none";
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
