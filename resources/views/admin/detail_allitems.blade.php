@extends('admin.layouts.template')

@section('page_title')
Detail Barang - Citra Media
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Judul & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Semua Barang /</span> Detail Barang</h4>
            <a href="{{ route('allitems.exportpdf.detail', $item->IdBarang) }}" target="_blank" class="btn btn-danger d-flex align-items-center"
                style="background: linear-gradient(45deg, #dc3545, #ff6b6b);">
                <i class='bx bxs-printer me-2'></i> Print
            </a>
    </div>

    <!-- Card Gabungan -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header text-white" style="background-color: rgb(123, 171, 254);">
            <strong class="fs-4">Detail Barang & Riwayat</strong>
        </div>
        <div class="card-body pt-3">

            <!-- Detail Barang -->
            <h5 class="fw-semibold mb-3">📦 Detail Barang</h5>
            <div class="row py-2 border-bottom">
                <div class="col-md-4 fw-semibold">Nama Barang:</div>
                <div class="col-md-8">{{ $item->NamaBarang }}</div>
            </div>
            <div class="row py-2 border-bottom">
                <div class="col-md-4 fw-semibold">Jenis Barang:</div>
                <div class="col-md-8">{{ $item->jenisBarang->JenisBarang ?? 'N/A' }}</div>
            </div>            
            <div class="row py-2 border-bottom mb-4">
                <div class="col-md-4 fw-semibold">Jumlah Stok:</div>
                <div class="col-md-8">{{ $item->JumlahStok ?? 0 }}</div>
            </div>

            <!-- Riwayat Barang Masuk -->
            <h5 class="fw-semibold mb-3">📥 Riwayat Barang Masuk</h5>
            @if($historiMasuk->count())
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light text-center">
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
                                    <td class="text-center">{{ $masuk->IdMasuk }}</td>
                                    <td class="text-center">{{ $masuk->supplier->NamaSupplier ?? '-' }}</td>
                                    <td class="text-center">{{ $masuk->QtyMasuk }}</td>
                                    <td class="text-center">Rp {{ number_format($masuk->HargaSatuan, 0, ',', '.') }}</td>
                                    <td class="text-center">Rp {{ number_format($masuk->SubTotal, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($masuk->created_at)->format('d-m-Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center mb-4">Tidak ada riwayat masuk</p>
            @endif

            <!-- Riwayat Barang Keluar -->
            <h5 class="fw-semibold mb-3">📤 Riwayat Barang Keluar</h5>
            @if($historiKeluar->count())
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ID Keluar</th>
                                <th>Qty Keluar</th>
                                <th>Tanggal Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historiKeluar as $keluar)
                                <tr>
                                    <td class="text-center">{{ $keluar->IdKeluar }}</td>
                                    <td class="text-center">{{ $keluar->QtyKeluar }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($keluar->tglKeluar)->format('d-m-Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center">Tidak ada riwayat keluar</p>
            @endif

        </div>
    </div>

</div>
@endsection
