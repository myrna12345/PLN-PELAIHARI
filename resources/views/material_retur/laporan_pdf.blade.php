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
    <p style="text-align: center;">Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_akhir }}</p>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Material</th>
                <th>Petugas</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Keterangan</th> {{-- Kolom Baru --}}
                <th>Foto Material</th>
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
                <td>{{ $item->keterangan }}</td> {{-- Data Keterangan --}}
                
                {{-- Cek apakah foto ada sebelum ditampilkan agar tidak error --}}
                <td>
                    @if($item->foto_path)
                        <img src="{{ public_path('storage/' . $item->foto_path) }}" width="50" style="display:block; margin:auto;">
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($item->foto_petugas)
                        <img src="{{ public_path('storage/' . $item->foto_petugas) }}" width="50" style="display:block; margin:auto;">
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>