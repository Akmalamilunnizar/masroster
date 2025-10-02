@extends('admin.layouts.template')

@section('page_title')
CIME | Detail Transaksi
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Detail</span> Transaksi</h4>
    <div class="card">
        <h5 class="card-header fw-bold">Informasi Transaksi</h5>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>ID Transaksi:</strong> {{ $transaksi->IdTransaksi }}</p>
                    <p><strong>Tanggal Transaksi:</strong> {{ $transaksi->tglTransaksi ? \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('d M Y H:i') : '-' }}</p>
                    <p><strong>Status Pembayaran:</strong> {{ $transaksi->StatusPembayaran }}</p>
                    <p><strong>Status Pesanan:</strong> {{ $transaksi->StatusPesanan }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Pembayaran:</strong> Rp {{ number_format($transaksi->GrandTotal, 0, ',', '.') }}</p>
                    <p><strong>Metode Pengiriman:</strong> {{ $transaksi->shipping_method ?? '-' }}</p>
                    <p><strong>Tipe Pengiriman:</strong> {{ $transaksi->shipping_type ?? '-' }}</p>
                    <p><strong>Catatan:</strong> {{ $transaksi->notes ?? '-' }}</p>
                </div>
            </div>

            <h6 class="mt-4"><strong>Informasi Customer:</strong></h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Nama:</strong> {{ $transaksi->customer->f_name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $transaksi->customer->email ?? '-' }}</p>
                    <p><strong>Nomor Telepon:</strong> {{ $transaksi->customer->nomor_telepon ?? '-' }}</p>
                </div>
            </div>

            <h6 class="mt-4"><strong>Alamat Pengiriman:</strong></h6>
            <div class="row mb-4">
                <div class="col-md-12">
                    <p>{{ $transaksi->alamat_pengiriman ?? 'Alamat tidak tersedia' }}</p>
                </div>
            </div>

            <h6 class="mt-4"><strong>Detail Produk:</strong></h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Produk</th>
                            <th style="text-align: center;">Ukuran</th>
                            <th style="text-align: center;">Jumlah</th>
                            <th style="text-align: center;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksi->detailTransaksi as $detail)
                            <tr>
                                <td style="text-align: center;">{{ $detail->produk->NamaProduk ?? '-' }}</td>
                                <td style="text-align: center;">
                                    @if($detail->id_ukuran)
                                        {{ $detail->size->nama ?? '-' }}
                                    @else
                                        {{ $detail->CustomUkuran ?? '-' }}
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $detail->QtyProduk }}</td>
                                <td style="text-align: center;">Rp {{ number_format($detail->SubTotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Kembali</a>
                @if($transaksi->StatusPesanan == 'Menunggu Konfirmasi')
                    <a href="{{ route('terimaOrderan', $transaksi->IdTransaksi) }}" class="btn btn-success">Terima Pesanan</a>
                    <a href="{{ route('tolakOrderan', $transaksi->IdTransaksi) }}" class="btn btn-danger">Tolak Pesanan</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 