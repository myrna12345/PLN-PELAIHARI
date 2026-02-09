<!DOCTYPE html>
<html>
<head>
    <title>Laporan Material Keluar</title>
    <style>
        /* Ukuran font diperkecil sedikit agar muat di portrait */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; }
        h2 { text-align: center; margin-bottom: 5px; text-transform: uppercase; }
        p { text-align: center; font-size: 10px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; } /* Fixed layout membantu kerapian */
        th, td { border: 1px solid #000; padding: 4px; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }

        /* CSS agar foto tidak gepeng */
        .img-container {
            width: 50px;
            height: 50px;
            overflow: hidden;
            display: block;
            margin: 0 auto;
        }
        img {
            max-width: 50px;
            height: auto; /* Biarkan tinggi mengikuti proporsi asli */
            border-radius: 2px;
        }
    </style>
</head>
<body>

<h2>Laporan Material Keluar</h2>
<p>
    Periode: 
    {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} 
    s/d 
    {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
</p>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 15%;">Nama Material</th>
            <th style="width: 12%;">Petugas</th>
            <th style="width: 10%;">Jumlah</th>
            <th style="width: 10%;">Stok</th>
            <th style="width: 13%;">Keterangan</th>
            <th style="width: 15%;">Tanggal</th>
            <th style="width: 10%;">Foto Barang</th>
            <th style="width: 10%;">Foto Petugas</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->material->nama_material ?? '-' }}</td>
            <td>{{ $item->nama_petugas }}</td>
            <td class="text-center">{{ $item->jumlah_material }} {{ $item->satuan_material }}</td>
            <td class="text-center">{{ $item->stok_saat_ini }}</td>
            <td>{{ $item->keterangan }}</td>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d/m/y H:i') }}
            </td>
            <td class="text-center">
                @if($item->foto && file_exists(public_path($item->foto)))
                    <img src="{{ public_path($item->foto) }}">
                @else
                    -
                @endif
            </td>
            <td class="text-center">
                @if($item->foto_petugas && file_exists(public_path($item->foto_petugas)))
                    <img src="{{ public_path($item->foto_petugas) }}">
                @else
                    -
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center">Tidak ada data.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>