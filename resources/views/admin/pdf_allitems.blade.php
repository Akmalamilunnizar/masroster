<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Barang</title>
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
        {{-- Kalau mau logo tinggal aktifkan baris ini --}}
        {{-- <img src="{{ public_path('assets/images/logo.png') }}" alt="Logo"> --}}
        <h2>LAPORAN DATA BARANG</h2>

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
                <th class="text-center">Nama Barang</th>
                <th class="text-center">Jenis</th>
                <th class="text-center">Harga Satuan</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Supplier</th>
                <th class="text-center">Waktu Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->NamaBarang ?? '-' }}</td>
                    <td>{{ $item->jenisBarang->JenisBarang ?? '-' }}</td>
                    <td>{{ $item->latestDetailMasuk?->HargaSatuan ? 'Rp ' . number_format($item->latestDetailMasuk->HargaSatuan, 0, ',', '.') : '-' }}</td>
                    <td >{{ $item->stock }}</td>
                    <td>{{ $item->latestDetailMasuk?->supplier?->NamaSupplier ?? '-' }}</td>
                    <td>{{ $item->latestDetailMasuk?->created_at ? \Carbon\Carbon::parse($item->latestDetailMasuk->created_at)->format('d-m-Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small">Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>

</body>
</html>
