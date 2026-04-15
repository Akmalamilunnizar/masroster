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
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#trainModelModal">
                <i class="bx bx-refresh me-1"></i> Latih Ulang AI
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runForecastModal">
                <i class="bx bx-flash me-1"></i> Jalankan Prediksi Cepat
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

    @if(session('batch_results'))
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="mb-0 text-primary">
                    <i class="bx bx-table me-1"></i> Detail Data Table - Hasil Batch Forecast
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Produk</th>
                                <th>Nama Roster</th>
                                <th class="text-end">Tebakan Penjualan (Bulan Depan)</th>
                                <th class="text-center">WMAPE (%)</th>
                                <th class="text-end">MAE</th>
                                <th class="text-end">RMSE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('batch_results') as $row)
                                @php
                                    $wmape = $row['wmape_score'] ?? null;
                                    $wmapeBadgeClass = 'bg-danger';
                                    $wmapeLabel = 'Perlu Perhatian / Kurang Data';

                                    if (!is_null($wmape) && (float) $wmape < 15) {
                                        $wmapeBadgeClass = 'bg-success';
                                        $wmapeLabel = 'Sangat Akurat';
                                    } elseif (!is_null($wmape) && (float) $wmape >= 16 && (float) $wmape <= 30) {
                                        $wmapeBadgeClass = 'bg-warning text-dark';
                                        $wmapeLabel = 'Cukup Akurat';
                                    }

                                    $wmapeText = is_null($wmape)
                                        ? 'N/A'
                                        : rtrim(rtrim(number_format((float) $wmape, 2, '.', ''), '0'), '.');
                                @endphp
                                <tr>
                                    <td><code>{{ $row['id_roster'] ?? '-' }}</code></td>
                                    <td>{{ $row['nama_produk'] ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ is_null($row['forecasted_demand'] ?? null) ? '-' : number_format((float) $row['forecasted_demand'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $wmapeBadgeClass }}">
                                            {{ $wmapeText }}{{ is_null($wmape) ? '' : '%' }} - {{ $wmapeLabel }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        {{ is_null($row['mae_score'] ?? null) ? '-' : number_format((float) $row['mae_score'], 2) }}
                                    </td>
                                    <td class="text-end">
                                        {{ is_null($row['rmse_score'] ?? null) ? '-' : number_format((float) $row['rmse_score'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                                    <th class="text-center">Versi Aktif</th>
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
                                            <small class="d-block">LSTM: <code>{{ $item['active_lstm_version'] ?? '-' }}</code></small>
                                            <small class="d-block">Prophet: <code>{{ $item['active_prophet_version'] ?? '-' }}</code></small>
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
                                        <td colspan="9" class="text-center py-4">
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

            <div class="card shadow-sm mt-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                    <h5 class="mb-0 text-primary"><i class="bx bx-health me-1"></i> AI Health Dashboard</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Algoritma Aktif</th>
                                    <th>ID Versi</th>
                                    <th>WMAPE</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($healthRows ?? [] as $row)
                                    @php
                                        $wmape = $row['wmape_score'];
                                        $wmapeClass = is_null($wmape) || $wmape > 50
                                            ? 'bg-danger'
                                            : ($wmape >= 21 ? 'bg-warning text-dark' : 'bg-success');
                                        $wmapeText = is_null($wmape) ? 'Belum dilatih' : rtrim(rtrim(number_format((float) $wmape, 2, '.', ''), '0'), '.') . '%';
                                    @endphp
                                    <tr>
                                        <td>{{ $row['nama_produk'] }}</td>
                                        <td>
                                            <span class="badge {{ $row['is_active'] ? 'bg-success' : 'bg-dark' }}">
                                                {{ $row['is_active'] ? '[AKTIF] ' : '[LAMA] ' }}{{ $row['algoritma_aktif'] }}
                                            </span>
                                        </td>
                                        <td><code>{{ $row['version_id'] }}</code></td>
                                        <td><span class="badge {{ $wmapeClass }}">{{ $wmapeText }}</span></td>
                                        <td class="text-center">
                                            @if(!$row['is_active'])
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setActiveModel({{ $row['id'] }})">
                                                    Jadikan Model Utama
                                                </button>
                                            @else
                                                <span class="badge bg-success">Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada model aktif di registry.</td>
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
                    <i class="bx bx-flash me-1"></i> Jalankan Prediksi Cepat
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
                        <h6 class="mb-1" id="progressTitle">Running quick inference forecast...</h6>
                        <p class="text-muted mb-0" id="progressSubtext">This should finish quickly if active model versions are available.</p>
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
                    <i class="bx bx-play me-1"></i> Jalankan Prediksi Cepat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Train Model Modal -->
<div class="modal fade" id="trainModelModal" tabindex="-1" aria-labelledby="trainModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="trainModelModalLabel">
                    <i class="bx bx-refresh me-1"></i> Latih Ulang AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Training akan membuat versi model baru. Versi hanya dipromosikan jika WMAPE lebih baik.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Model untuk Training</label>
                    <select id="trainModelSelect" class="form-select">
                        <option value="prophet">PROPHET</option>
                        <option value="lstm">LSTM</option>
                    </select>
                </div>
                <div id="trainProgress" class="d-none text-center py-3">
                    <div class="spinner-border text-warning mb-3" role="status"></div>
                    <h6 class="mb-1">Training sedang berjalan...</h6>
                    <p class="text-muted mb-0">Mohon tunggu sampai proses selesai.</p>
                </div>
                <div id="trainResult" class="d-none"></div>
            </div>
            <div class="modal-footer" id="trainModalFooter">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btnTrainModel" onclick="runModelTraining()">
                    <i class="bx bx-play me-1"></i> Mulai Training
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

    document.getElementById('trainModelModal').addEventListener('show.bs.modal', function () {
        document.getElementById('trainProgress').classList.add('d-none');
        document.getElementById('trainResult').classList.add('d-none');
        document.getElementById('btnTrainModel').disabled = false;
        document.getElementById('trainModalFooter').classList.remove('d-none');
    });

        function setActiveModel(historyId) {
            if (!confirm('Jadikan versi ini sebagai model utama?')) {
                return;
            }

            fetch(`{{ url('/admin/forecast/model') }}/${historyId}/set-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.status === 'success') {
                    location.reload();
                    return;
                }

                alert(data.message || 'Gagal mengubah model aktif.');
            })
            .catch(err => alert(err.message));
        }

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
                        <small>Fast inference requires the AI server to be online.</small>
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
                    <small>Flask AI Server is not reachable. Fast inference is unavailable.</small>
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
        document.getElementById('progressTitle').textContent = `Menjalankan prediksi cepat ${model.toUpperCase()}...`;

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
                setTimeout(() => {
                    location.reload();
                }, 700);
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

    function runModelTraining() {
        const model = document.getElementById('trainModelSelect').value;

        document.getElementById('trainProgress').classList.remove('d-none');
        document.getElementById('trainResult').classList.add('d-none');
        document.getElementById('btnTrainModel').disabled = true;

        fetch('{{ route("forecast.train-model") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ model: model })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            document.getElementById('trainProgress').classList.add('d-none');
            const resultEl = document.getElementById('trainResult');
            resultEl.classList.remove('d-none');

            if (ok && data.status === 'success') {
                const summary = data.summary || {};
                const metrics = summary.metrics || {};
                resultEl.innerHTML = `
                    <div class="alert alert-success mb-0">
                        <strong>Training selesai.</strong><br>
                        Model: ${summary.model || model.toUpperCase()}<br>
                        Versi Baru: <code>${summary.model_version || '-'}</code><br>
                        Promoted: ${summary.promoted ?? 0}, Rejected: ${summary.rejected ?? 0}<br>
                        MAE: ${metrics.mae ?? '-'} | WMAPE: ${metrics.wmape ?? '-'}
                    </div>
                `;
            } else {
                resultEl.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <strong>Training gagal.</strong><br>
                        ${data.message || 'Unknown error'}
                    </div>
                `;
                document.getElementById('btnTrainModel').disabled = false;
            }
        })
        .catch(err => {
            document.getElementById('trainProgress').classList.add('d-none');
            const resultEl = document.getElementById('trainResult');
            resultEl.classList.remove('d-none');
            resultEl.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <strong>Connection error.</strong><br>
                    ${err.message}
                </div>
            `;
            document.getElementById('btnTrainModel').disabled = false;
        });
    }

    // Initialize DataTable if available
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#stockTable').DataTable({
                pageLength: 25,
                order: [[7, 'asc']], // Sort by status column
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

