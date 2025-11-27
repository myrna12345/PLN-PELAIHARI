<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Material Siaga Standby</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 3px;
            font-size: 18px;
            text-transform: uppercase;
        }

        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
            word-wrap: break-word;
        }

        th {
            background: #e6e6e6;
            font-weight: bold;
        }

        /* Rata kiri untuk teks panjang */
        .left {
            text-align: left;
            padding-left: 5px;
        }

        /* Rata kanan untuk angka */
        .right {
            text-align: right;
            padding-right: 5px;
        }

        @page {
            margin: 20px;
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
                <th style="width: 35px;">No</th>
                <th style="width: 150px;">Nama Material</th>
                <th style="width: 140px;">Nama Petugas</th>
                <th style="width: 90px;">Stand Meter</th>
                <th style="width: 110px;">Jumlah Material</th>
                <th style="width: 120px;">Tanggal</th>
                <th style="width: 90px;">Status</th> 
            </tr>
        </thead>

        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="left">{{ $item->nama_material }}</td>
                <td class="left">{{ $item->nama_petugas }}</td>
                <td class="right">{{ $item->stand_meter }}</td>
                <td class="right">{{ $item->jumlah_siaga_standby }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>