<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; vertical-align: middle; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">LAPORAN MATERIAL RETUR</h2>
    <p style="text-align: center;">Periode: {{ $tanggal_mulai->format('d M Y') }} s/d {{ $tanggal_akhir->format('d M Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Material</th>
                <th>Petugas</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Tanggal (WITA)</th> <th>Foto Material</th>
                <th>Foto Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->material->nama_material }}</td>
                <td>{{ $item->nama_petugas }}</td>
                <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->keterangan }}</td>
                
                {{-- TAMBAHAN: Data Tanggal Format WITA --}}
                <td>
                    {{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}
                </td>

                {{-- Foto Material --}}
                <td>
                    @if($item->foto_path && file_exists(public_path('uploads/material_retur/' . $item->foto_path)))
                        <img src="{{ public_path('uploads/material_retur/' . $item->foto_path) }}" width="50" style="display:block; margin:auto;">
                    @else
                        -
                    @endif
                </td>

                {{-- Foto Petugas --}}
                <td>
                    @if($item->foto_petugas && file_exists(public_path('uploads/material_retur/' . $item->foto_petugas)))
                        <img src="{{ public_path('uploads/material_retur/' . $item->foto_petugas) }}" width="50" style="display:block; margin:auto;">
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html