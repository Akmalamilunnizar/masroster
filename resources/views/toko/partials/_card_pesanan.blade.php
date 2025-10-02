<div class="col-md-6 mb-4">
    <div class="card h-100 shadow border-0 rounded-4 p-4" style="background-color: #ffffff;">
        <div class="card-body px-4 py-3">
            <div class="row">
                <!-- Kolom kiri: informasi transaksi -->
                <div class="col-8">
                    <h4 class="fw-bold mb-3" style="color: #2B3674; font-size: 23px; letter-spacing: 1.2px;">
                        ID Transaksi: <span class="text-primary">{{ $item->IdTransaksi }}</span>
                    </h4>
                    <p class="mb-1"><strong>Nama:</strong> {{ $item->user->f_name ?? '-' }}</p>
                    <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($item->tglTransaksi)->format('d M Y H:i') }}</p>
                    <p class="mb-1"><strong>Grand Total:</strong> Rp {{ number_format($item->GrandTotal, 0, ',', '.') }}</p>
                    <p class="mb-1"><strong>Status Pembayaran:</strong>
                        @if ($item->StatusPembayaran == 'Lunas')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-danger">Belum Lunas</span>
                        @endif
                    </p>
                    <p class="mb-1"><strong>Status Pesanan:</strong>
                        @if ($item->StatusPesanan == 'Menunggu Konfirmasi')
                            <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                        @elseif ($item->StatusPesanan == 'Diproses')
                            <span class="badge bg-info text-dark">Diproses</span>
                        @elseif ($item->StatusPesanan == 'Dikirim')
                            <span class="badge bg-primary">Dikirim</span>
                        @elseif ($item->StatusPesanan == 'Selesai')
                            <span class="badge bg-success">Selesai</span>
                        @elseif ($item->StatusPesanan == 'Dibatalkan')
                            <span class="badge bg-danger">Dibatalkan</span>
                        @endif
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('pesanan.detail', $item->IdTransaksi) }}" class="btn btn-primary rounded-pill px-4 py-2">
                            Detail
                        </a>
                    </div>
                </div>

                <!-- Kolom kanan: gambar produk -->
                <div class="col-4 d-flex flex-column align-items-center justify-content-center text-center">
                    <img src="{{ asset('storage/' . ($item->produk->first()->Img ?? 'assets/images/poster1.jpeg')) }}"
                         alt="Foto Produk"
                         class="img-fluid rounded"
                         style="max-height: 120px; object-fit: cover;"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    @if ($item->produk->count() > 1)
                        <p class="text-muted small mt-2">+{{ $item->produk->count() - 1 }} produk lainnya</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
