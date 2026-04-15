@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Forecasting
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-end align-items-center py-3 mb-4">
        <a href="{{ route('forecast.stock') }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-chevron-left"></i> Kembali ke Form
            </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                    <h5 class="mb-0 text-primary"><i class="bx bx-chart me-1"></i> Input Data Forecasting</h5>
                </div>
                <div class="card-body mt-3">
                    @if(session('error'))
                        <div class="alert alert-danger border-left-danger shadow-sm">
                            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4 align-items-end">
                        <div class="col-md-4">
                            <label for="id_roster" class="form-label fw-semibold">Pilih Produk</label>
                            <select id="id_roster" class="form-select select2" onchange="loadFromDatabase(this.value)">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->IdRoster }}">{{ $product->NamaProduk }} ({{ $product->IdRoster }})</option>
                                @endforeach
                            </select>
                            <div class="form-text mt-1 text-muted">Data penjualan produk akan otomatis dimuat setelah dipilih.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="model" class="form-label fw-semibold">Model Forecasting</label>
                            <select id="model" name="model" class="form-select" required>
                                <option value="prophet">Prophet</option>
                                <option value="lstm">LSTM</option>
                            </select>
                            <div class="form-text mt-1 text-muted">Pilih algoritma yang digunakan untuk prediksi.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="version_id" class="form-label fw-semibold">Versi Model</label>
                            <select id="version_id" name="version_id" form="forecastForm" class="form-select" required disabled>
                                <option value="">-- Pilih Produk dulu --</option>
                            </select>
                            <div class="form-text mt-1 text-muted">Pilih versi dari model history.</div>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <div class="btn-group shadow-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="generateSampleData()"><i class="bx bx-shuffle me-1"></i> Data Sampel</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearForm()"><i class="bx bx-trash me-1"></i> Bersihkan</button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('predict') }}" id="forecastForm">
                        @csrf
                        <input type="hidden" name="id_roster" id="form_id_roster" value="">
                        <input type="hidden" name="model" id="form_model" value="prophet">
                        <div class="table-responsive border rounded mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Bulan (YYYY-MM)</th>
                                        <th>Jumlah Terjual (Unit)</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTableBody">
                                    @for ($i = 0; $i < 12; $i++)
                                        <tr>
                                            <td class="ps-4">
                                                <input type="text" class="form-control" name="bulan[]" placeholder="YYYY-MM" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="terjual[]" required min="0">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bx bxs-magic-wand me-1"></i> Mulai Prediksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowCount = 12;

function addRow() {
    if (rowCount < 24) {
        const tbody = document.getElementById('dataTableBody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="ps-4"><input type="text" class="form-control" name="bulan[]" placeholder="YYYY-MM" required></td>
            <td><input type="number" class="form-control" name="terjual[]" required min="0"></td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
    } else {
        Swal.fire('Info', 'Maksimal 24 bulan data untuk akurasi terbaik.', 'info');
    }
}

function removeRow(button) {
    if (rowCount > 1) {
        button.closest('tr').remove();
        rowCount--;
    } else {
        alert('Minimal harus ada 1 baris data.');
    }
}

function clearForm() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    for (let i = 0; i < 12; i++) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="ps-4"><input type="text" class="form-control" name="bulan[]" placeholder="YYYY-MM" required></td>
            <td><input type="number" class="form-control" name="terjual[]" required min="0"></td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
    }
    rowCount = 12;
    document.getElementById('id_roster').value = '';
    document.getElementById('form_id_roster').value = '';
    const versionSelect = document.getElementById('version_id');
    if (versionSelect) {
        versionSelect.innerHTML = '<option value="">-- Pilih Produk dulu --</option>';
        versionSelect.disabled = true;
    }
    window.__forecastHistories = [];
}

function generateSampleData() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    rowCount = 0;

    const today = new Date();
    // Use last 12 months excluding current month
    for (let i = 12; i >= 1; i--) {
        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const sales = Math.floor(Math.random() * 100) + 50;

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="ps-4"><input type="text" class="form-control" name="bulan[]" value="${year}-${month}" required></td>
            <td><input type="number" class="form-control" name="terjual[]" value="${sales}" required min="0"></td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
    }
}

function loadFromDatabase(productId) {
    if (!productId) return;

    document.getElementById('form_id_roster').value = productId;

    // Show loading indicator in table
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><span class="mt-2 d-inline-block">Mengambil data dari database...</span></td></tr>';

    // Fetch data from your database using AJAX
    fetch(`/admin/forecast/get-sales-data?id_roster=${encodeURIComponent(productId)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(response => {
        if (response.status !== 'success') throw new Error(response.message || 'Unknown error');

        const data = response.data;
        window.__forecastHistories = response.histories || [];
        tbody.innerHTML = '';
        rowCount = 0;

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4">Data tidak ditemukan untuk produk ini.</td></tr>';
            return;
        }

        renderVersionOptions();

        data.forEach(item => {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="ps-4"><input type="text" class="form-control" name="bulan[]" value="${item.bulan}" required></td>
                <td><input type="number" class="form-control" name="terjual[]" value="${item.terjual}" required min="0"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
            rowCount++;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengambil data: ' + error.message);
        clearForm();
    });
}

function renderVersionOptions() {
    const model = document.getElementById('model').value;
    const versionSelect = document.getElementById('version_id');
    const histories = Array.isArray(window.__forecastHistories) ? window.__forecastHistories : [];
    const filtered = histories.filter(item => (item.model_type || '').toLowerCase() === model);

    if (!versionSelect) {
        return;
    }

    versionSelect.innerHTML = '';

    if (filtered.length === 0) {
        versionSelect.innerHTML = '<option value="">-- Tidak ada versi untuk model ini --</option>';
        versionSelect.disabled = true;
        return;
    }

    versionSelect.disabled = false;
    versionSelect.innerHTML = '<option value="">-- Pilih Versi Model --</option>';

    filtered.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.label || `${(item.model_type || '').toUpperCase()} - ${item.version_id}`;
        versionSelect.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const modelSelect = document.getElementById('model');
    const modelHidden = document.getElementById('form_model');
    const productSelect = document.getElementById('id_roster');
    const productHidden = document.getElementById('form_id_roster');
    const versionSelect = document.getElementById('version_id');

    window.__forecastHistories = [];

    if (modelSelect && modelHidden) {
        modelHidden.value = modelSelect.value;
        modelSelect.addEventListener('change', function () {
            modelHidden.value = this.value;
            renderVersionOptions();
        });
    }

    if (productSelect && productHidden) {
        productHidden.value = productSelect.value || '';
    }

    if (versionSelect) {
        versionSelect.disabled = true;
    }
});
</script>

<style>
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .border-left-danger {
        border-left: 5px solid #ff3e1d;
    }
</style>
@endsection
