<!DOCTYPE html>
<html>
<head>
    <title>Laporan Material Keluar</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; font-size: 11px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        td { vertical-align: middle; }
        .text-center { text-align: center; }

        img {
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h2>LAPORAN MATERIAL KELUAR</h2>
<p>
    Periode:
    {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }}
    s/d
    {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}
</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Material</th>
            <th>Nama Petugas</th>
            <th>Jumlah</th>
            <th>Stok</th>
            <th>Keterangan</th>
            <th>Tanggal (WITA)</th>
            <th>Foto Material</th>
            <th>Foto Petugas</th>
        </tr>
    </thead>

    <tbody>
    @forelse ($items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>

            <td>{{ $item->material->nama_material ?? '-' }}</td>

            <td>{{ $item->nama_petugas }}</td>

            <td class="text-center">
                {{ $item->jumlah_material }} {{ $item->satuan_material }}
            </td>

            <td class="text-center">{{ $item->stok_saat_ini }}</td>

            <td>{{ $item->keterangan }}</td>

            <td class="text-center">
                {{ \Carbon\Carbon::parse($item->tanggal)
                    ->setTimezone('Asia/Makassar')
                    ->format('d M Y, H:i') }}
            </td>

           {{-- FOTO MATERIAL --}}
            <td class="text-center">
                @if($item->foto && file_exists(public_path($item->foto)))
                    <img src="{{ public_path($item->foto) }}" width="60" height="60">
                @else
                    -
                @endif
            </td>

            {{-- FOTO PETUGAS --}}
            <td class="text-center">
                @if($item->foto_petugas && file_exists(public_path($item->foto_petugas)))
                    <img src="{{ public_path($item->foto_petugas) }}" width="60" height="60">
                @else
                    -
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center">
                Tidak ada data pada periode ini.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
