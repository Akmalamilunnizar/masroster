@extends('admin.layouts.template')

@section('page_title')
CIME | Detail Pelanggan
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Detail</span> Pelanggan
        </h4>
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
            <i class='bx bx-arrow-back me-1'></i> Kembali
        </a>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Customer Info Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class='bx bx-user me-2'></i>Informasi Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID Pelanggan</label>
                        <p class="form-control-static">{{ $customer->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <p class="form-control-static">{{ $customer->f_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p class="form-control-static">{{ $customer->email }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor Telepon</label>
                        <p class="form-control-static">{{ $customer->nomor_telepon }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <p class="form-control-static">
                            <span class="badge bg-label-primary">{{ $customer->user }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Addresses Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class='bx bx-map me-2'></i>Daftar Alamat
            </h5>
        </div>
        <div class="card-body">
            @if($addresses && $addresses->isNotEmpty())
                <div class="row">
                    @foreach($addresses as $address)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $address->label ?? 'Alamat ' . $loop->iteration }}</h6>
                                        @if($address->is_default)
                                            <span class="badge bg-label-success">Default</span>
                                        @endif
                                    </div>
                                    <p class="card-text mb-1">
                                        <strong>{{ $address->recipient_name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $address->phone_number ?? '-' }}</small>
                                    </p>
                                    <p class="card-text">
                                        {{ $address->full_address ?? '-' }}<br>
                                        {{ $address->city ?? '-' }}, {{ $address->postal_code ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class='bx bx-info-circle me-2'></i>Tidak ada alamat yang terdaftar
                </div>
            @endif
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class='bx bx-history me-2'></i>Riwayat Transaksi
            </h5>
        </div>
        <div class="card-body">
            @if($transaksis->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="text-align: center;">ID Transaksi</th>
                                <th style="text-align: center;">Tanggal</th>
                                <th style="text-align: center;">Total</th>
                                <th style="text-align: center;">Status Pembayaran</th>
                                <th style="text-align: center;">Status Pesanan</th>
                                <th style="text-align: center;">Jenis Pengiriman</th>
                                <th style="text-align: center;">Biaya Ongkir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksis as $transaksi)
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ route('vieworder', $transaksi->IdTransaksi) }}" class="text-primary fw-bold">
                                            {{ $transaksi->IdTransaksi }}
                                        </a>
                                    </td>
                                    <td style="text-align: center;">{{ $transaksi->tglTransaksi ? \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('d M Y') : '-' }}</td>
                                    <td style="text-align: center;">Rp {{ number_format($transaksi->GrandTotal, 0, ',', '.') }}</td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-label-{{ $transaksi->StatusPembayaran == 'Lunas' ? 'success' : 'warning' }}">
                                            {{ $transaksi->StatusPembayaran }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-label-{{ 
                                            $transaksi->StatusPesanan == 'Diterima' ? 'success' : 
                                            ($transaksi->StatusPesanan == 'Ditolak' ? 'danger' : 'warning') 
                                        }}">
                                            {{ $transaksi->StatusPesanan }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">{{ $transaksi->shipping_type ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $transaksi->ongkir > 0 ? 'Rp. ' . number_format($transaksi->ongkir, 0, ',', '.') : 'Free' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class='bx bx-info-circle me-2'></i>Belum ada transaksi untuk customer ini
                </div>
            @endif
        </div>
    </div>

    <!-- Transaction Notes -->
    @if($transaksis->isNotEmpty())
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class='bx bx-note me-2'></i>Catatan Transaksi
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksis as $transaksi)
                                <tr>
                                    <td>{{ $transaksi->IdTransaksi }}</td>
                                    <td>{{ $transaksi->tglTransaksi ? \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('d M Y') : '-' }}</td>
                                    <td>{{ $transaksi->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
