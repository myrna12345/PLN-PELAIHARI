<!DOCTYPE html>
<html>
<head>
    <title>Laporan Material Kembali</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; } /* Ukuran font disesuaikan untuk portrait */
        h2 { text-align: center; margin-bottom: 5px; text-transform: uppercase; }
        p { text-align: center; font-size: 10px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }

        /* Style agar foto tidak gepeng */
        img {
            max-width: 50px; /* Batasi lebar */
            height: auto;    /* Biarkan tinggi mengikuti proporsi asli */
            border-radius: 2px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<h2>LAPORAN MATERIAL KEMBALI</h2>
<p>
    Periode:
    {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }}
    s/d
    {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}
</p>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 20%;">Nama Material</th>
            <th style="width: 15%;">Nama Petugas</th>
            <th style="width: 10%;">Jumlah</th>
            <th style="width: 10%;">Stok</th>
            <th style="width: 15%;">Tanggal (WITA)</th>
            <th style="width: 12%;">Foto Material</th>
            <th style="width: 13%;">Foto Petugas</th>
        </tr>
    </thead>

    <tbody>
    @forelse ($items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $item->material->nama_material ?? '-' }}</td>
            <td>{{ $item->nama_petugas }}</td>
            <td class="text-center">
                {{ $item->jumlah_material }} {{ $item->satuan }}
            </td>
            <td class="text-center">{{ $item->stok_saat_ini }}</td>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($item->tanggal)
                    ->setTimezone('Asia/Makassar')
                    ->format('d/m/y H:i') }}
            </td>

            {{-- FOTO MATERIAL --}}
            <td class="text-center">
                @if($item->foto && file_exists(public_path($item->foto)))
                    <img src="{{ public_path($item->foto) }}">
                @else
                    -
                @endif
            </td>

            {{-- FOTO PETUGAS --}}
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
            <td colspan="8" class="text-center">
                Tidak ada data pada periode ini.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>