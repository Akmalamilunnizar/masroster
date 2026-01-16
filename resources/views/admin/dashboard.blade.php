@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Dashboard
@endsection
@section('js')
<script src="{{ asset('assets/apexcharts/dist/apexcharts.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/apexcharts/dist/apexcharts.css') }}" />

<!-- Favicon -->
  <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    /* Dashboard Custom Styles */
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
        transform: translateY(-2px);
    }

    .avatar {
        width: 3rem;
        height: 3rem;
    }

    .avatar-xl {
        width: 4rem;
        height: 4rem;
    }

    .bg-label-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
        color: #0d6efd !important;
    }

    .bg-label-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
        color: #ffc107 !important;
    }

    .bg-label-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
        color: #0dcaf0 !important;
    }

    .bg-label-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
        color: #198754 !important;
    }

    .text-success {
        color: #198754 !important;
    }

    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.5rem;
    }

    .card-title {
        font-weight: 600;
        color: #5a5c69;
    }

    .h4 {
        font-weight: 700;
        color: #5a5c69;
    }

    .h2 {
        font-weight: 700;
        color: #5a5c69;
    }

    .text-muted {
        color: #858796 !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="fw-bold py-3 mb-2">Dashboard Overview</h4>
                <p class="text-muted">Ringkasan data dan statistik sistem</p>
            </div>
        </div>

        <!-- Summary Cards Row 1 -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-package"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small fw-semibold text-muted">Total Produk</div>
                            <div class="h4 mb-0">{{ $totalItems }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-error-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small fw-semibold text-muted">Stok Rendah</div>
                            <div class="h4 mb-0">{{ $lowStockItems->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-category"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small fw-semibold text-muted">Jenis Produk</div>
                            <div class="h4 mb-0">{{ $itemsByType->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-shopping-bag"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small fw-semibold text-muted">Total Pesanan</div>
                            <div class="h4 mb-0">{{ $totalOrders }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-xl">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-dollar-circle"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-4 text-start">
                                <div class="small fw-semibold text-muted">Total Pendapatan</div>
                                <div class="h2 mb-0 text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Top 5 Produk dengan Stok Tertinggi</h5>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="topStockChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Distribusi Jenis Produk</h5>
                    </div>
                    <div class="card-body">
                        <div id="itemsByTypeChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Pendapatan per Bulan (6 Bulan Terakhir)</h5>
                    </div>
                    <div class="card-body">
                        <div id="revenueByMonthChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Top 5 Produk Terlaris</h5>
                    </div>
                    <div class="card-body">
                        <div id="topSellingChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Top Stock Items Chart - Using Real Data Only
    var topStockData = {!! json_encode($topStockRoster->pluck('stock')) !!};
    var topStockCategories = {!! json_encode($topStockRoster->pluck('IdRoster')) !!};

    var topStockOptions = {
        series: [{
            name: 'Stok',
            data: topStockData
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: false,
                distributed: true,
                columnWidth: '60%'
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 'bold'
            }
        },
        xaxis: {
            categories: topStockCategories,
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1'],
        legend: {
            show: false
        },
        grid: {
            borderColor: '#e3e6f0',
            strokeDashArray: 4
        }
    };

    var topStockChart = new ApexCharts(document.querySelector("#topStockChart"), topStockOptions);
    topStockChart.render();

    // Items by Type Chart
    var itemsByTypeOptions = {
        series: {!! json_encode($itemsByType->pluck('total')) !!},
        chart: {
            type: 'pie',
            height: 350,
            toolbar: {
                show: true
            }
        },
        labels: {!! json_encode($itemsByType->pluck('JenisBarang')) !!},
        colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'],
        legend: {
            position: 'bottom',
            fontSize: '12px',
            fontWeight: 500
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 'bold'
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%'
                }
            }
        }
    };

    var itemsByTypeChart = new ApexCharts(document.querySelector("#itemsByTypeChart"), itemsByTypeOptions);
    itemsByTypeChart.render();

    // Revenue By Month Chart
    var revenueSeries = [{
        name: 'Pendapatan',
        data: {!! json_encode($revenueByMonth->pluck('total')) !!}
    }];
    var revenueCategories = {!! json_encode($revenueByMonth->pluck('ym')) !!};
    var revenueOptions = {
        series: revenueSeries,
        chart: { type: 'line', height: 350 },
        xaxis: { categories: revenueCategories },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#33C1FF'],
        yaxis: {
            min: 0,
            forceNiceScale: true
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "Rp " + val.toLocaleString('id-ID');
                }
            }
        }
    };
    var revenueChart = new ApexCharts(document.querySelector('#revenueByMonthChart'), revenueOptions);
    revenueChart.render();

    // Top Selling Products Chart
    var topSellingOptions = {
        series: [{
            name: 'Qty',
            data: {!! json_encode($topSelling->pluck('total_qty')) !!}
        }],
        chart: { type: 'bar', height: 350 },
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: false },
        xaxis: { categories: {!! json_encode($topSelling->pluck('IdRoster')) !!} },
        colors: ['#75FF33']
    };
    var topSellingChart = new ApexCharts(document.querySelector('#topSellingChart'), topSellingOptions);
    topSellingChart.render();
</script>
@endpush


@endsection
