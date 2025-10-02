<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('page_title', 'Detail Pesanan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4 fw-bold" style="color: #2B3674; font-size: 23px; letter-spacing: 1.2px;">
        Detail Pesanan #{{ $pesanan->IdTransaksi }}
    </h3>

    <div class="card shadow-sm border-0 rounded-4 p-4 mx-auto" style="max-width: 800px; background-color: #ffffff;">
        <div class="card-body px-4 py-3">

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Nama Pembeli</div>
                <div class="col-sm-8">{{ $pesanan->customer ? $pesanan->customer->f_name : '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Username</div>
                <div class="col-sm-8">{{ $pesanan->username }}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Alamat Pengiriman</div>
                <div class="col-sm-8">{{ $pesanan->alamat_pengiriman ?? '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Nomor Telepon</div>
                <div class="col-sm-8">{{ $pesanan->customer ? $pesanan->customer->nomor_telepon : '-' }}</div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Bayar</div>
                <div class="col-sm-8">Rp {{ number_format($pesanan->Bayar, 0, ',', '.') }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Grand Total</div>
                <div class="col-sm-8">Rp {{ number_format($pesanan->GrandTotal, 0, ',', '.') }}</div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Tanggal Transaksi</div>
                <div class="col-sm-8">{{ \Carbon\Carbon::parse($pesanan->tglTransaksi)->format('d M Y H:i') }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Status Pembayaran</div>
                <div class="col-sm-8">
                    @if ($pesanan->StatusPembayaran == 'Lunas')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-danger">Belum Lunas</span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Status Pesanan</div>
                <div class="col-sm-8">
                    @if ($pesanan->StatusPesanan == 'Menunggu Konfirmasi')
                        <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                    @elseif ($pesanan->StatusPesanan == 'Diproses')
                        <span class="badge bg-info text-dark">Diproses</span>
                    @elseif ($pesanan->StatusPesanan == 'Dikirim')
                        <span class="badge bg-primary">Dikirim</span>
                    @elseif ($pesanan->StatusPesanan == 'Selesai')
                        <span class="badge bg-success">Selesai</span>
                    @elseif ($pesanan->StatusPesanan == 'Dibatalkan')
                        <span class="badge bg-danger">Dibatalkan</span>
                    @else
                        <span class="badge bg-secondary">{{ $pesanan->StatusPesanan }}</span>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4 fw-semibold text-secondary">Tanggal Update</div>
                <div class="col-sm-8">{{ $pesanan->tglUpdate ? \Carbon\Carbon::parse($pesanan->tglUpdate)->format('d M Y H:i') : '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 fw-semibold text-secondary">Catatan</div>
                <div class="col-sm-8">{{ $pesanan->notes ?? '-' }}</div>
            </div>

            <div>
                <strong>Metode Pembelian:</strong> {{ $pesanan->shipping_method ?? '-' }}<br>
                <strong>Jenis Pengiriman:</strong> {{ $pesanan->shipping_type ?? '-' }}
            </div>

        </div>
    </div>

    <!-- Produk yang Dibeli -->
    <div class="card shadow-sm border-0 rounded-4 p-4 mx-auto mt-4" style="max-width: 800px; background-color: #ffffff;">
        <div class="card-body">
            <h5 class="fw-bold mb-4" style="color: #2B3674;">Produk yang Dibeli</h5>

            @forelse ($pesanan->detailTransaksi as $detail)
                <div class="row mb-4 align-items-center border-bottom pb-3">
                    <div class="col-3 text-center">
                        <img src="{{ asset('storage/' . ($detail->produk->Img ?? 'assets/images/poster1.jpeg')) }}"
                             alt="{{ $detail->produk->NamaProduk }}"
                             class="img-fluid rounded"
                             style="max-height: 100px; object-fit: cover;"
                             onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    </div>
                    <div class="col-6">
                        <h6 class="fw-bold">{{ $detail->produk->NamaProduk }}</h6>
                        <p class="mb-1 text-muted">Jumlah: {{ $detail->QtyProduk }}</p>
                        <p class="mb-1 text-muted">
                            Harga Satuan: Rp 
                            {{ number_format($detail->QtyProduk > 0 ? ($detail->SubTotal / $detail->QtyProduk) : 0, 0, ',', '.') }}
                        </p>
                        <p class="mb-0 fw-semibold">Subtotal: Rp {{ number_format($detail->SubTotal, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">Tidak ada produk yang dibeli</p>
            @endforelse

        </div>
    </div>

    <div class="d-flex justify-content-start mt-4">
        <a href="{{ url('/pesanan') }}" class="btn btn-outline-primary px-4">Kembali</a>
    </div>
</div>

</body>
</html>
