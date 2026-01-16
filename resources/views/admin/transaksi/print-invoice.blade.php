    <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Penjualan - {{ $orders->IdTransaksi }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            background: white;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 9px;
            line-height: 1.1;
            margin-bottom: 2px;
        }

        .company-phone {
            font-size: 9px;
        }

        .invoice-details {
            flex: 1;
            text-align: right;
        }

        .invoice-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .invoice-info {
            font-size: 9px;
        }

        .invoice-info div {
            margin-bottom: 1px;
        }

        .customer-section {
            margin-bottom: 10px;
            border: 1px solid #000;
            padding: 5px;
        }

        .customer-title {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .customer-info {
            font-size: 9px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: left;
            font-size: 9px;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .items-table td {
            vertical-align: top;
        }

        .items-table .text-center {
            text-align: center;
        }

        .items-table .text-right {
            text-align: right;
        }

        .summary-section {
            margin-top: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            font-size: 9px;
        }

        .summary-table .label {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 30%;
        }

        .summary-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .payment-section {
            margin-top: 10px;
            border: 1px solid #000;
            padding: 5px;
        }

        .payment-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .payment-info {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .signature-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 120px;
            text-align: center;
            border: 1px solid #000;
            padding: 5px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 2px;
        }

        .notes-section {
            margin-top: 10px;
            border: 1px solid #000;
            padding: 5px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .notes-content {
            font-size: 9px;
            line-height: 1.2;
        }

        .footer-section {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 9px;
            }

            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .no-print {
                display: none;
            }

            /* Continuous form paper specific settings */
            @page {
                size: A4;
                margin: 0.5in 0.25in;
            }

            /* Ensure proper spacing for continuous form */
            .invoice-header {
                page-break-inside: avoid;
            }

            .items-table {
                page-break-inside: avoid;
            }

            .summary-section {
                page-break-inside: avoid;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Faktur
    </button>

    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">TIB Talangsuko</div>
                <div class="company-address">
                    Jl. Raya Talangsuko, Sebelah SPBU Talangsuko<br>
                    Turen, Malang Jawa Timur
                </div>
                <div class="company-phone">
                    0851-0005-4043 / 0812-3003-2363
                </div>
            </div>

            <div class="invoice-details">
                <div class="invoice-title">FAKTUR PENJUALAN</div>
                <div class="invoice-info">
                    <div><strong>Tanggal Faktur:</strong> {{ \Carbon\Carbon::parse($orders->tglTransaksi)->format('d M Y') }}</div>
                    <div><strong>Tanggal Kirim:</strong> {{ \Carbon\Carbon::parse($orders->tglTransaksi)->format('d M Y') }}</div>
                    <div><strong>Nomor Faktur:</strong> {{ $orders->IdTransaksi }}</div>
                    <div><strong>Syarat Pembayaran:</strong> {{ $orders->StatusPembayaran == 'Lunas' ? 'C.O.D' : 'Hutang' }}</div>
                    <div><strong>Penjual:</strong> {{ $orders->admin->f_name ?? 'Admin' }}</div>
                    <div><strong>Pengirim:</strong> {{ $orders->admin->f_name ?? 'Admin' }}</div>
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="customer-title">Tagihan ke:</div>
            <div class="customer-info">
                <strong>{{ $orders->customer->f_name }}</strong><br>
                {{ $orders->address->full_address ?? 'Alamat tidak tersedia' }}<br>
                {{ $orders->address->city ?? '' }}, {{ $orders->address->postal_code ?? '' }}<br>
                Telp: {{ $orders->customer->nomor_telepon ?? '-' }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Kode Brg</th>
                    <th style="width: 30%;">Nama Barang</th>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 15%;">Harga Satuan</th>
                    <th style="width: 10%;">DO No.</th>
                    <th style="width: 12%;">Kode Gdg</th>
                    <th style="width: 15%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders->detailTransaksi as $item)
                <tr>
                    <td class="text-center">{{ $item->produk->IdRoster ?? '-' }}</td>
                    <td>{{ $item->produk->NamaProduk ?? '-' }}</td>
                    <td class="text-center">{{ $item->QtyProduk }}</td>
                    <td class="text-center">Pcs</td>
                    <td class="text-right">Rp {{ number_format($item->SubTotal / $item->QtyProduk, 0, ',', '.') }}</td>
                    <td class="text-center">-</td>
                    <td class="text-center">Gudang TIBTLG</td>
                    <td class="text-right">Rp {{ number_format($item->SubTotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="label">Sub Total</td>
                    <td class="amount">Rp {{ number_format($orders->detailTransaksi->sum('SubTotal'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Diskon</td>
                    <td class="amount">Rp 0</td>
                </tr>
                <tr>
                    <td class="label">PPN 10%</td>
                    <td class="amount">Rp 0</td>
                </tr>
                <tr>
                    <td class="label">Biaya Angkut</td>
                    <td class="amount">Rp {{ number_format($orders->ongkir ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-top: 2px solid #000;">
                    <td class="label"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($orders->GrandTotal, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Pembayaran</td>
                    <td class="amount">Rp {{ number_format($orders->Bayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Uang noblen</td>
                    <td class="amount">Rp {{ number_format($orders->Bayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Kurang</td>
                    <td class="amount">
                        @php
                            $kurang = $orders->GrandTotal - $orders->Bayar;
                        @endphp
                        @if($kurang > 0)
                            Rp {{ number_format($kurang, 0, ',', '.') }}
                        @else
                            Rp 0
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <div class="payment-title">Catatan:</div>
            <div class="payment-info">
                Barang yang belum di bayar masih milik Turen Indah
            </div>
            <div class="payment-info">
                Pembayaran transfer harap dilakukan ke rek BCA - 317-111-2221 - INDRI ARIANI
            </div>
            <div class="payment-info">
                Bukti Transfer Wajib Di Kirim Ke Whatsapp Toko
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Disiapkan Oleh</div>
                <div class="signature-line"></div>
                <div style="font-size: 10px;">({{ $orders->admin->f_name ?? 'Admin' }})</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Diketahui Oleh</div>
                <div class="signature-line"></div>
                <div style="font-size: 10px;">(........................)</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Disetujui Oleh</div>
                <div class="signature-line"></div>
                <div style="font-size: 10px;">(........................)</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Diantar Oleh</div>
                <div class="signature-line"></div>
                <div style="font-size: 10px;">(........................)</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Diterima Oleh</div>
                <div class="signature-line"></div>
                <div style="font-size: 10px;">({{ $orders->customer->f_name }})</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div><strong>Kritik & Saran</strong></div>
            <div>Email: tipusat.turen@gmail.com</div>
            <div>ADMIN TOKO TL, {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }

        // Continuous form paper specific settings
        window.addEventListener('beforeprint', function() {
            // Ensure proper formatting for continuous form paper
            document.body.style.fontSize = '9px';
            document.body.style.lineHeight = '1.1';
        });
    </script>
</body>
</html>
