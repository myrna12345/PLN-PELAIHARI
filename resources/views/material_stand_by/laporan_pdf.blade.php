<!DOCTYPE html>
<html>
<head>
    <title>Laporan Material Stand By</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; font-size: 11px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        td { vertical-align: middle; }
        .text-center { text-align: center; }
        .img-report { width: 80px; height: auto; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <h2>LAPORAN MATERIAL STAND BY</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Material</th>
                <th>Jumlah</th>
                <th>Tanggal (WITA)</th>
                <th>Foto Material</th>
                {{-- Kolom Foto Petugas DIHAPUS --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->jumlah }} {{ $item->satuan ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                
                {{-- Foto Material --}}
                <td class="text-center">
                    @if($item->foto_path && file_exists(public_path('uploads/material_stand_by/' . $item->foto_path)))
                        <img src="{{ public_path('uploads/material_stand_by/' . $item->foto_path) }}" class="img-report">
                    @else
                        <span>-</span>
                    @endif
                </td>

                {{-- Data Foto Petugas DIHAPUS --}}
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Data tidak ditemukan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>