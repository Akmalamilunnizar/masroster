@extends('admin.layouts.template')
@section('page_title')
    CIME | Detail Produk
@endsection

@section('search')
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <form method="GET" action="{{ route('searchusers') }}" class="d-inline-block ms-2">
                <input type="text" name="search" class="form-control border-0 shadow-none ps-2"
                    placeholder="Pencarian ID atau nama..." value="{{ isset($search) ? $search : '' }}" />
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12">
                    <img src="{{ asset('assets/images/banner.jpeg') }}" alt="Gambar Banner" class="img-fluid w-50 img-outline">
                </div>
                <div class="col-md-12 mt-3"> <!-- Tambahkan margin top untuk memberi jarak antar gambar -->
                    <img src="{{ asset('assets/images/banner.jpeg') }}" alt="Gambar Kedua" class="img-fluid w-2f0 img-outline">
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .img-outline {
        width: 100%; /* Gambar akan menyesuaikan lebar container */
        height: auto; /* Menjaga proporsi gambar */
        border: 10px solid white; /* Outline putih lebih tebal */
        padding: 2px; /* Opsional, memberikan ruang antara gambar dan outline */
        border-radius: 15px; /* Menambahkan border radius */
        object-fit: contain; /* Menjaga gambar tetap proporsional tanpa terpotong */
    }
</style>