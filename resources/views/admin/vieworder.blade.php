@extends('admin.layouts.template')

@section('page_title')
CIME | Detail Pesanan
@endsection

@section('search')
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <form method="GET" action={{ route('searchorder') }}>
                <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2"
                    placeholder="Pencarian..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..." />
            </form>
        </div>
    </div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Detail</span> Pesanan
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

    <!-- Order Info Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class='bx bx-package me-2'></i>Informasi Pesanan
            </h5>
            <div>
                @if($orders->StatusPesanan == 'Menunggu Konfirmasi')
                    <form action="{{ route('terimaOrderan', $orders->IdTransaksi) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class='bx bx-check me-1'></i> Terima Pesanan
                        </button>
                    </form>
                    <form action="{{ route('tolakOrderan', $orders->IdTransaksi) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class='bx bx-x me-1'></i> Tolak Pesanan
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID Pesanan</label>
                        <p class="form-control-static">{{ $orders->IdTransaksi }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Pesanan</label>
                        <p class="form-control-static">{{ \Carbon\Carbon::parse($orders->tglTransaksi)->format('d M Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Pesanan</label>
                        <p class="form-control-static">
                            <span class="badge bg-label-{{ 
                                $orders->StatusPesanan == 'Diterima' ? 'success' : 
                                ($orders->StatusPesanan == 'Ditolak' ? 'danger' : 'warning') 
                            }}">
                                {{ $orders->StatusPesanan }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Pembayaran</label>
                        <p class="form-control-static">
                            <span class="badge bg-label-{{ $orders->StatusPembayaran == 'Lunas' ? 'success' : 'warning' }}">
                                {{ $orders->StatusPembayaran }}
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Pembayaran</label>
                        <p class="form-control-static">{{ $orders->shipping_method ?? '-' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Pembayaran</label>
                        <p class="form-control-static">Rp {{ number_format($orders->GrandTotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Dibayar</label>
                        <p class="form-control-static">Rp {{ number_format($orders->Bayar, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class='bx bx-user me-2'></i>Informasi Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Pelanggan</label>
                        <p class="form-control-static">{{ $orders->customer->f_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p class="form-control-static">{{ $orders->customer->email }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor Telepon</label>
                        <p class="form-control-static">{{ $orders->customer->nomor_telepon }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat Pengiriman</label>
                        <p class="form-control-static">{{ $orders->alamat_pengiriman }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class='bx bx-package me-2'></i>Detail Produk
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Ukuran</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>File Desain</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders->detailTransaksi as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('storage/' . $item->produk->Img) }}" 
                                             alt="{{ $item->produk->NamaProduk }}" 
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0">{{ $item->produk->NamaProduk }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->CustomUkuran ?? ($item->size ? $item->size->nama : '-') }}</td>
                                <td>{{ $item->QtyProduk }}</td>
                                <td>Rp {{ number_format($item->SubTotal / $item->QtyProduk, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->SubTotal, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->design_file)
                                        <a href="{{ asset('storage/' . $item->design_file) }}" 
                                           class="btn btn-sm btn-primary" 
                                           target="_blank">
                                            <i class='bx bx-download me-1'></i> Download
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes Card -->
    @if($orders->notes)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-note me-2'></i>Catatan Pesanan
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $orders->notes }}</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Add any JavaScript functionality here if needed
</script>
@endpush
@endsection
