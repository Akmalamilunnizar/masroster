@extends('admin.layouts.template')

@section('page_title')
Detail Produk - Citra Media
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Judul & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Semua Produk /</span> Detail Produk</h4>
            {{-- <button type="button" onclick="window.print()" class="btn btn-danger d-flex align-items-center"
                style="background: linear-gradient(45deg, #dc3545, #ff6b6b);">
                <i class='bx bxs-printer me-2'></i> Print
            </button> --}}
    </div>

    <!-- Card Gabungan -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header text-white" style="background-color: rgb(123, 171, 254);">
            <strong class="fs-4">Detail Produk & Riwayat</strong>
        </div>
        <div class="card-body pt-3">

            <!-- Detail Produk -->
            <h5 class="fw-semibold mb-3">📦 Detail Produk</h5>
            <div class="row py-2 border-bottom">
                <div class="col-md-4 fw-semibold">Nama Produk:</div>
                <div class="col-md-8">{{ $produk->NamaProduk ?? '-' }}</div>
            </div>
            <div class="row py-2 border-bottom">
                <div class="col-md-4 fw-semibold">Jenis Produk:</div>
                <div class="col-md-8">{{ optional($produk->jenisRoster)->JenisBarang ?? 'N/A' }}</div>
            </div>
            <div class="row py-2 border-bottom mb-4">
                <div class="col-md-4 fw-semibold">Jumlah Stok:</div>
                <div class="col-md-8">{{ $produk->stock ?? 0 }}</div>
            </div>
            <div class="row py-2 border-bottom">
                <div class="col-md-4 fw-semibold">Tipe Produk:</div>
                <div class="col-md-8">{{ optional($produk->tipeRoster)->namaTipe ?? 'N/A' }}</div>
            </div>
            <div class="row py-2 border-bottom mb-4">
                <div class="col-md-4 fw-semibold">Motif Produk:</div>
                <div class="col-md-8">{{ optional($produk->motif)->nama_motif ?? 'N/A' }}</div>
            </div>
            <div class="row py-2 border-bottom mb-4">
                <div class="col-md-4 fw-semibold">Ukuran Tersedia:</div>
                <div class="col-md-8">
                    @forelse($produk->sizes as $size)
                        <span class="badge bg-primary me-1 mb-1">
                            {{ $size->nama_ukuran ?? $size->id_ukuran }}
                            @if(isset($size->pivot) && $size->pivot->harga !== null)
                                - Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                            @endif
                        </span>
                    @empty
                        <span class="text-muted">Belum ada ukuran</span>
                    @endforelse
                </div>
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
