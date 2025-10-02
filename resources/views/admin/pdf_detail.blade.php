<!DOCTYPE html>
<html>
<head>
    <title>Detail Barang - {{ $item->NamaBarang }}</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .detail-row {
            display: flex;
            padding: 6px 0;
            border-bottom: 1px solid #ddd;
        }
        .detail-label {
            width: 30%;
            font-weight: 600;
        }
        .detail-value {
            width: 70%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }
        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: right;
            color: #555;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <h2>Detail Barang</h2>

    <!-- Detail Barang -->
    <div class="section-title">Detail Barang</div>
    <div class="detail-row">
        <div class="detail-label">Nama Barang:</div>
        <div class="detail-value">{{ $item->NamaBarang }}</div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Jenis Barang:</div>
        <div class="detail-value">{{ $item->jenisBarang->JenisBarang ?? 'N/A' }}</div>
    </div>    
    <div class="detail-row">
        <div class="detail-label">Jumlah Stok:</div>
        <div class="detail-value">{{ $item->JumlahStok ?? 0 }}</div>
    </div>

    <!-- Riwayat Barang Masuk -->
    <div class="section-title">Riwayat Barang Masuk</div>
    @if($historiMasuk->count())
    <table>
        <thead>
            <tr>
                <th>ID Masuk</th>
                <th>Supplier</th>
                <th>Qty Masuk</th>
                <th>Harga Satuan</th>
                <th>Sub Total</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historiMasuk as $masuk)
            <tr>
                <td>{{ $masuk->IdMasuk }}</td>
                <td>{{ $masuk->supplier->NamaSupplier ?? '-' }}</td>
                <td>{{ $masuk->QtyMasuk }}</td>
                <td>Rp {{ number_format($masuk->HargaSatuan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($masuk->SubTotal, 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($masuk->created_at)->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="no-data">Tidak ada riwayat barang masuk.</p>
    @endif

    <!-- Riwayat Barang Keluar -->
    <div class="section-title">Riwayat Barang Keluar</div>
    @if($historiKeluar->count())
    <table>
        <thead>
            <tr>
                <th>ID Keluar</th>
                <th>Qty Keluar</th>
                <th>Tanggal Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historiKeluar as $keluar)
            <tr>
                <td>{{ $keluar->IdKeluar }}</td>
                <td>{{ $keluar->QtyKeluar }}</td>
                <td>{{ \Carbon\Carbon::parse($keluar->tglKeluar)->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="no-data">Tidak ada riwayat barang keluar.</p>
    @endif

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y') }}
    </div>

</body>
</html>
