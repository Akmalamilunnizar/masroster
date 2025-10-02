@extends('admin.layouts.template')
@section('page_title')
Laporan - Parameter Ikan Koi
@endsection
@section('search')
<div class="navbar-nav align-items-center">
    <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <form method="GET" action={{ route('searchusers') }}>
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2"
                placeholder="Pencarian Id atau nama..." value="{{ isset($search) ? $search : '' }}"
                aria-label="Pencarian..." />
        </form>
    </div>
</div>
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light"></span>Laporan Parameter</h4>
    <div class="btn-group mb-3">
        <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false">
            Kolam
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0);">Kolam 1</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Kolam 2</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Kolam 3</a></li>
        </ul>
    </div>
    <div class="d-flex mb-3">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false">
                Minggu
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);">Minggu 1</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Minggu 2</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Minggu 3</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Minggu 4</a></li>
            </ul>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false">
                Bulan
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);">Januari</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Februari</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Maret</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">April</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Mei</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Juni</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Juli</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Agustus</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">September</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Oktober</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">November</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Desember</a></li>
            </ul>
        </div>
        <button type="button" class="btn btn-danger ms-auto">
            <i class="fas fa-file-pdf me-1"></i> Export PDF
        </button>
    </div>
    <div class="card">
        <h5 class="card-header">Daftar Data Parameter Ikan Koi</h5>
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Parameter</th>
                        <th>Rentang Normal</th>
                        <th>Status Mingguan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>1</td>
                        <td>pH</td>
                        <td>6.8 - 7.5</td>
                        <td>Stabil</td>
                        <td>Tingkat keasaman dalam rentang normal</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Suhu</td>
                        <td>20°C - 25°C</td>
                        <td>Optimal</td>
                        <td>Suhu sesuai dengan kebutuhan ikan koi</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Oksigen Terlarut</td>
                        <td>5 - 7 ppm</td>
                        <td>Baik</td>
                        <td>Kadar oksigen mencukupi untuk ikan</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Amonia (NH3)</td>
                        <td>
                            < 0.02 ppm</td>
                        <td>Rendah</td>
                        <td>Level amonia aman</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Karbon Dioksida (CO2)</td>
                        <td>
                            < 10 ppm</td>
                        <td>Stabil</td>
                        <td>Tidak ada peningkatan signifikan</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Nitrit (NO2)</td>
                        <td>
                            < 0.5 ppm</td>
                        <td>Rendah</td>
                        <td>Kadar nitrit aman untuk ikan</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Kesadahan (GH)</td>
                        <td>50 - 150 ppm</td>
                        <td>Normal</td>
                        <td>Kesadahan air dalam batas normal</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Alkalinitas (KH)</td>
                        <td>70 - 100 ppm</td>
                        <td>Stabil</td>
                        <td>Alkalinitas mendukung stabilitas pH</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Nitrat (NO3)</td>
                        <td>
                            < 50 ppm</td>
                        <td>Aman</td>
                        <td>Nitrat dalam kadar rendah</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Salinitas</td>
                        <td>0 - 0.3%</td>
                        <td>Optimal</td>
                        <td>Salinitas cocok untuk ikan air tawar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection