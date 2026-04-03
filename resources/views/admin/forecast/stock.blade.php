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
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runForecastModal">
                <i class="bx bx-bot me-1"></i> Run Batch Forecast
            </button>
            <a href="{{ route('forecast.form') }}" class="btn btn-success">
                <i class="bx bx-chart me-1"></i> Forecast Spesifik Produk
            </a>

            @if($needsUpdate || !$hasForecasts)
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#runForecastModal">
                    <i class="bx bx-refresh"></i> Update Required
                </button>
            @endif
        </div>
    </div>

    @if($needsUpdate)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-time fs-4 me-2"></i>
            <div>
                <strong>Forecasts are stale!</strong> Click <strong>"Run Batch Forecast"</strong> above to update all product forecasts using AI.
            </div>
        </div>
    @endif

    @if(!$hasForecasts)
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bx bx-info-circle fs-4 me-2"></i>
            <div>
                <strong>No forecasts yet!</strong> Click <strong>"Run Batch Forecast"</strong> above to generate AI forecasts for all products.
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <!-- Summary Cards -->
            <div class="row mb-4">
                @php
                    $criticalStock = collect($forecastData)->where('status', 'critical')->count();
                    $lowStock = collect($forecastData)->where('status', 'low')->count();
                    $overStock = collect($forecastData)->where('status', 'overstock')->count();
                    $safeStock = collect($forecastData)->where('status', 'safe')->count();
                @endphp

                <div class="col-md-3">
                    <div class="card border-danger mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-error text-danger" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Critical</h5>
                                    <h2 class="mb-0 text-danger">{{ $criticalStock }}</h2>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-warning mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-error-circle text-warning" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Low Stock</h5>
                                    <h2 class="mb-0 text-warning">{{ $lowStock }}</h2>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
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

                <div class="col-md-3">
                    <div class="card border-info mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-info-circle text-info" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Overstock</h5>
                                    <h2 class="mb-0 text-info">{{ $overStock }}</h2>
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

<!-- Run Batch Forecast Modal -->
<div class="modal fade" id="runForecastModal" tabindex="-1" aria-labelledby="runForecastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="runForecastModalLabel">
                    <i class="bx bx-bot me-1"></i> Run Batch Forecast
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Flask Server Status -->
                <div id="flaskStatus" class="alert alert-secondary d-flex align-items-center mb-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Checking Flask AI server status...</span>
                </div>

                <!-- Model Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Pilih Model AI</label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline flex-fill">
                            <input class="form-check-input" type="radio" name="forecastModel" id="modelProphet" value="prophet" checked>
                            <label class="form-check-label d-block p-3 border rounded text-center cursor-pointer" for="modelProphet" style="cursor:pointer;">
                                <i class="bx bx-trending-up d-block mb-1" style="font-size: 1.8rem; color: #03c3ec;"></i>
                                <strong>Prophet</strong>
                                <small class="d-block text-muted">Facebook/Meta</small>
                            </label>
                        </div>
                        <div class="form-check form-check-inline flex-fill">
                            <input class="form-check-input" type="radio" name="forecastModel" id="modelLstm" value="lstm">
                            <label class="form-check-label d-block p-3 border rounded text-center cursor-pointer" for="modelLstm" style="cursor:pointer;">
                                <i class="bx bx-brain d-block mb-1" style="font-size: 1.8rem; color: #696cff;"></i>
                                <strong>LSTM</strong>
                                <small class="d-block text-muted">Deep Learning</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Force Option -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="forceRecalculate">
                        <label class="form-check-label" for="forceRecalculate">
                            <strong>Force Recalculate</strong>
                            <small class="d-block text-muted">Recalculate all products, even recently updated ones</small>
                        </label>
                    </div>
                </div>

                <!-- Progress Area (hidden by default) -->
                <div id="forecastProgress" class="d-none">
                    <hr>
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                        <h6 class="mb-1" id="progressTitle">Training model & forecasting...</h6>
                        <p class="text-muted mb-0" id="progressSubtext">This may take a few minutes depending on product count.</p>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Result Area (hidden by default) -->
                <div id="forecastResult" class="d-none">
                    <hr>
                    <div id="resultContent"></div>
                </div>
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnRunForecast" onclick="runBatchForecast()">
                    <i class="bx bx-play me-1"></i> Jalankan Forecast
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-info {
        border-left: 5px solid #03c3ec;
    }
    .form-check-inline .form-check-input {
        display: none;
    }
    .form-check-inline .form-check-input:checked + .form-check-label {
        border-color: #696cff !important;
        background-color: rgba(105, 108, 255, 0.08);
        box-shadow: 0 0 0 2px rgba(105, 108, 255, 0.3);
    }
</style>

<script>
    // Check Flask health when modal opens
    document.getElementById('runForecastModal').addEventListener('show.bs.modal', function () {
        checkFlaskHealth();
        // Reset state
        document.getElementById('forecastProgress').classList.add('d-none');
        document.getElementById('forecastResult').classList.add('d-none');
        document.getElementById('btnRunForecast').disabled = false;
        document.getElementById('modalFooter').classList.remove('d-none');
    });

    function checkFlaskHealth() {
        const statusEl = document.getElementById('flaskStatus');
        statusEl.className = 'alert alert-secondary d-flex align-items-center mb-4';
        statusEl.innerHTML = `
            <div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></div>
            <span>Checking Flask AI server status...</span>
        `;

        fetch('{{ route("forecast.flask-health") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'online') {
                let modelsInfo = '';
                if (data.models) {
                    modelsInfo = `<br><small class="ms-4">LSTM: <strong>${data.models.lstm}</strong> | Prophet: <strong>${data.models.prophet}</strong></small>`;
                }
                statusEl.className = 'alert alert-success d-flex align-items-start mb-4';
                statusEl.innerHTML = `
                    <i class="bx bx-check-circle fs-4 me-2 mt-1"></i>
                    <div>
                        <strong>Flask AI Server is Online</strong> — LSTM & Prophet models available.
                        ${modelsInfo}
                    </div>
                `;
            } else {
                statusEl.className = 'alert alert-warning d-flex align-items-start mb-4';
                statusEl.innerHTML = `
                    <i class="bx bx-error fs-4 me-2 mt-1"></i>
                    <div>
                        <strong>Flask AI Server is Offline</strong><br>
                        <small>Forecast will use <strong>Simple Moving Average (SMA)</strong> as fallback.</small>
                    </div>
                `;
            }
        })
        .catch(() => {
            statusEl.className = 'alert alert-warning d-flex align-items-start mb-4';
            statusEl.innerHTML = `
                <i class="bx bx-error fs-4 me-2 mt-1"></i>
                <div>
                    <strong>Could not check server status</strong><br>
                    <small>Forecast will use <strong>SMA fallback</strong> if Flask is unreachable.</small>
                </div>
            `;
        });
    }

    function runBatchForecast() {
        const model = document.querySelector('input[name="forecastModel"]:checked').value;
        const force = document.getElementById('forceRecalculate').checked;

        // Show progress
        document.getElementById('forecastProgress').classList.remove('d-none');
        document.getElementById('forecastResult').classList.add('d-none');
        document.getElementById('btnRunForecast').disabled = true;
        document.getElementById('progressTitle').textContent = `Training ${model.toUpperCase()} model & forecasting all products...`;

        fetch('{{ route("forecast.run-batch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ model: model, force: force })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            document.getElementById('forecastProgress').classList.add('d-none');
            document.getElementById('forecastResult').classList.remove('d-none');

            const resultEl = document.getElementById('resultContent');

            if (ok && data.status === 'success') {
                resultEl.innerHTML = `
                    <div class="text-center text-success mb-3">
                        <i class="bx bx-check-circle" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-center text-success mb-2">Forecast Berhasil!</h6>
                    <p class="text-center text-muted mb-3">${data.message}</p>
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <span class="badge bg-label-primary px-3 py-2">Model: ${model.toUpperCase()}</span>
                        <span class="badge bg-label-info px-3 py-2">Durasi: ${data.duration}</span>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="bx bx-refresh me-1"></i> Refresh Data
                        </button>
                    </div>
                `;
                document.getElementById('modalFooter').classList.add('d-none');
            } else {
                resultEl.innerHTML = `
                    <div class="text-center text-danger mb-3">
                        <i class="bx bx-x-circle" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-center text-danger mb-2">Forecast Gagal</h6>
                    <p class="text-center text-muted">${data.message || 'Unknown error occurred.'}</p>
                    ${data.output ? `<pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">${data.output}</pre>` : ''}
                `;
                document.getElementById('btnRunForecast').disabled = false;
            }
        })
        .catch(err => {
            document.getElementById('forecastProgress').classList.add('d-none');
            document.getElementById('forecastResult').classList.remove('d-none');
            document.getElementById('resultContent').innerHTML = `
                <div class="text-center text-danger mb-3">
                    <i class="bx bx-x-circle" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center text-danger">Connection Error</h6>
                <p class="text-center text-muted">${err.message}</p>
            `;
            document.getElementById('btnRunForecast').disabled = false;
        });
    }

    // Initialize DataTable if available
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#stockTable').DataTable({
                pageLength: 25,
                order: [[6, 'asc']], // Sort by status column
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

