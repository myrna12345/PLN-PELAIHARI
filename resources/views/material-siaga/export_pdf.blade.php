<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Material Siaga Standby</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            margin: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16px;
            text-transform: uppercase;
        }

        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px 5px;
            word-wrap: break-word;
            vertical-align: middle;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
            padding-left: 8px;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .foto-pdf {
            width: 80px; /* Ukuran sedikit diperbesar agar jelas */
            height: auto;
            max-height: 100px;
            border: 0.5px solid #ccc;
        }

        @page {
            margin: 30px;
        }
    </style>
</head>
<body>

    <h2>LAPORAN MATERIAL SIAGA STANDBY</h2> 

    <p>
        Periode: 
        {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} 
        s/d 
        {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }} 
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th style="width: 180px;">NAMA MATERIAL & NOMOR METER</th>
                <th style="width: 70px;">STAND METER</th>
                <th style="width: 110px;">TANGGAL(WITA)</th>
                <th style="width: 90px;">FOTO</th>
                <th style="width: 70px;">STATUS</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>

                <td class="left">
                    <span class="uppercase">{{ $item->nama_material }}</span>
                    - {{ $item->nomor_meter }} 
                </td>

                <td class="center">{{ $item->stand_meter }} </td>

                <td class="center">
                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }} 
                </td>

                <td class="center">
                    @if($item->foto_base64)
                        <img src="{{ $item->foto_base64 }}" class="foto-pdf">
                    @else
                        <span style="color: #999;">Tidak Ada Foto</span>
                    @endif
                </td>

                <td class="center uppercase">
                    <strong>{{ $item->status }}</strong> 
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>