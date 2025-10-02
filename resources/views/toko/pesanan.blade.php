@extends('toko.layouts.template')

@section('page_title')
    CIME | Citra Media
@endsection

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 fw-bold" style="color: #2B3674; font-size: 30px;">History Pemesanan</h3>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="semua-tab" data-bs-toggle="pill" data-bs-target="#semua" type="button" role="tab">Semua</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="belum-lunas-tab" data-bs-toggle="pill" data-bs-target="#belum-lunas" type="button" role="tab">Belum Lunas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="selesai-tab" data-bs-toggle="pill" data-bs-target="#selesai" type="button" role="tab">Selesai</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- Semua Tab -->
        <div class="tab-pane fade show active" id="semua" role="tabpanel">
            <div class="row">
                @forelse ($transaksi as $item)
                    @include('toko.partials._card_pesanan', ['item' => $item])
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Belum ada pesanan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Belum Lunas Tab -->
        <div class="tab-pane fade" id="belum-lunas" role="tabpanel">
            @php
                $belumLunas = $transaksi->filter(fn($t) => $t->StatusPembayaran != 'Lunas');
            @endphp
            <div class="row">
                @forelse ($belumLunas as $item)
                    @include('toko.partials._card_pesanan', ['item' => $item])
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Tidak ada pesanan yang belum dibayar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        
        <!-- Selesai Tab -->
        <div class="tab-pane fade" id="selesai" role="tabpanel">
            @php
                $selesai = $transaksi->filter(fn($t) => $t->StatusPembayaran == 'Lunas' && $t->StatusPesanan == 'Selesai');
            @endphp
            <div class="row">
                @forelse ($selesai as $item)
                    @include('toko.partials._card_pesanan', ['item' => $item])
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Belum ada pesanan yang selesai.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
<style>
.nav-pills .nav-link.active {
    color: #fff !important; /* atau #000 kalau kamu pakai background terang */
    background-color: #0D6EFD; /* boleh ganti juga kalau mau tone beda */
}

</style>

