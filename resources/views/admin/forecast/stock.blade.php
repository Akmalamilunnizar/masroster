@extends('admin.layouts.template')
@section('page_title')
    Stock Forecast - CIME
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Stock Forecast (Cached)</h4>
            <small class="text-muted">Forecast for {{ $month }} | Last Update: {{ $lastUpdate }}</small>
        </div>
        <div>
        <a href="{{ route('forecast.form') }}" class="btn btn-success">
            <i class="bx bx-chart me-1"></i> Forecast Spesifik Produk
        </a>
            
            @if($needsUpdate || !$hasForecasts)
                <button type="button" class="btn btn-warning btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#updateModal">
                    <i class="bx bx-refresh"></i> Update Required
                </button>
            @endif
        </div>
    </div>
    
    @if($needsUpdate)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-time fs-4 me-2"></i>
            <div>
                <strong>Forecasts are stale!</strong> Run <code>php artisan app:forecast-all --model=lstm</code> to update all product forecasts.
            </div>
        </div>
    @endif
    
    @if(!$hasForecasts)
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bx bx-info-circle fs-4 me-2"></i>
            <div>
                <strong>No forecasts yet!</strong> Run <code>php artisan app:forecast-all --model=lstm</code> to generate AI forecasts for all products.
            </div>
        </div>
    @endif
    
    <div class="row">
        <div class="col-md-12">
            <!-- Summary Cards -->
            <div class="row mb-4">
                @php
                    $lowStock = collect($forecastData)->where('status', 'low')->count();
                    $overStock = collect($forecastData)->where('status', 'overstock')->count();
                    $safeStock = collect($forecastData)->where('status', 'safe')->count();
                @endphp
                
                <div class="col-md-4">
                    <div class="card border-danger mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-error-circle text-danger" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Low Stock</h5>
                                    <h2 class="mb-0 text-danger">{{ $lowStock }}</h2>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-success mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-check-circle text-success" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Safe Stock</h5>
                                    <h2 class="mb-0 text-success">{{ $safeStock }}</h2>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-warning mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-info-circle text-warning" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Overstock</h5>
                                    <h2 class="mb-0 text-warning">{{ $overStock }}</h2>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Forecast Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                    <h5 class="mb-0 text-primary"><i class="bx bx-package me-1"></i> Product Stock Forecast</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="stockTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Produk</th>
                                    <th class="text-end">Stok Saat Ini</th>
                                    <th class="text-end">AI Forecast (Next Month)</th>
                                    <th class="text-end">Safety Stock</th>
                                    <th class="text-center">Model</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forecastData as $item)
                                    <tr>
                                        <td><code>{{ $item['id_roster'] }}</code></td>
                                        <td>{{ $item['nama_produk'] }}</td>
                                        <td class="text-end">
                                            <strong>{{ number_format($item['current_stock']) }}</strong>
                                        </td>
                                        <td class="text-end">{{ number_format($item['forecasted_demand'], 2) }}</td>
                                        <td class="text-end">{{ number_format($item['safety_stock']) }}</td>
                                        <td class="text-center">
                                            @if($item['forecast_model'] === 'LSTM')
                                                <span class="badge bg-primary">{{ $item['forecast_model'] }}</span>
                                            @elseif($item['forecast_model'] === 'PROPHET')
                                                <span class="badge bg-info">{{ $item['forecast_model'] }}</span>
                                            @elseif($item['forecast_model'] === 'SMA')
                                                <span class="badge bg-secondary">{{ $item['forecast_model'] }}</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $item['forecast_model'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item['status'] === 'critical')
                                                <span class="badge bg-danger">
                                                    <i class="bx bx-error-circle me-1"></i> Critical
                                                </span>
                                            @elseif($item['status'] === 'low')
                                                <span class="badge bg-warning">
                                                    <i class="bx bx-error me-1"></i> Low Stock
                                                </span>
                                            @elseif($item['status'] === 'overstock')
                                                <span class="badge bg-info">
                                                    <i class="bx bx-info-circle me-1"></i> Overstock
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bx bx-check-circle me-1"></i> Safe
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">{{ $item['last_forecast_at'] ?? 'Never' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bx bx-package" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="text-muted mb-0">Tidak ada data produk</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Info Alert -->
            <div class="alert alert-info border-left-info d-flex align-items-center mt-4" role="alert">
                <i class="bx bx-info-circle fs-4 me-2"></i>
                <div>
                    <strong>Safety Stock Logic (1 Batch = 70 pcs):</strong><br>
                    <strong class="text-danger">🔴 Critical:</strong> Stock < 70 (urgent restocking needed)<br>
                    <strong class="text-warning">🟡 Low Stock:</strong> Stock < (AI Forecast + 70) (order soon)<br>
                    <strong class="text-success">🟢 Safe:</strong> Optimal stock levels for predicted demand<br>
                    <strong class="text-info">🟠 Overstock:</strong> Stock > 3× (Forecast + 70) (reduce orders)
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-info {
        border-left: 5px solid #03c3ec;
    }
</style>

<script>
    // Initialize DataTable if available
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#stockTable').DataTable({
                pageLength: 25,
                order: [[5, 'asc']], // Sort by status
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 produk",
                    infoFiltered: "(difilter dari _MAX_ total produk)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        }
    });
</script>
@endsection
