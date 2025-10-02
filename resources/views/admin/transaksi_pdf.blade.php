<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            width: 70px;
            height: auto;
            margin-bottom: 5px;
        }
        h2, h4 {
            text-align: center;
            margin: 0;
        }
        p {
            text-align: center;
            margin: 5px 0 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .text-center {
            text-align: center;
        }
        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        {{-- Kalau mau logo, aktifkan baris ini --}}
        {{-- <img src="{{ public_path('assets/images/logo.png') }}" alt="Logo"> --}}
        <h2>LAPORAN TRANSAKSI</h2>

        @php
            $namaBulan = [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember',
            ];
        @endphp

        <h4>
            Periode:
            {{ $bulan ? ($namaBulan[$bulan] ?? $bulan) : 'Semua Bulan' }}
            {{ $tahun ? $tahun : '' }}
        </h4>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">ID Transaksi</th>
                <th class="text-center">Nama Transaksi</th>
                <th class="text-center">Qty Orderan</th>
                <th class="text-center">Status Orderan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $no => $transaksi)
                <tr>
                    <td class="text-center">{{ $no + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('d-m-Y') }}</td>
                    <td>{{ $transaksi->IdTransaksi }}</td>
                    <td>{{ $transaksi->customer->f_name ?? '-' }}</td>
                    <td>{{ $transaksi->GrandTotal }}</td>
                    <td>{{ $transaksi->StatusPesanan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small">Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>

</body>
</html>
