@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Forecasting
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Forecasting</h4>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Input Data Forecasting</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="btn-group" role="group">
                            <!-- <button type="button" class="btn btn-primary" onclick="loadFromDatabase()">Load from Database</button> -->
                            <button type="button" class="btn btn-outline-primary" onclick="generateSampleData()">Klik Disini Sebelum Mengisi</button>
                            <button type="button" class="btn btn-outline-primary" onclick="clearForm()">Bersihkan Form</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('predict') }}" id="forecastForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bulan (YYYY-MM)</th>
                                        <th>Jumlah Terjual</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTableBody">
                                    @for ($i = 0; $i < 12; $i++)
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="bulan[]" placeholder="YYYY-MM" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="terjual[]" required>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>                        
                        <button type="submit" class="btn btn-outline-primary">Prediksi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowCount = 1;

function addRow() {
    if (rowCount < 12) {
        const tbody = document.getElementById('dataTableBody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="bulan[]" placeholder="YYYY-MM" required></td>
            <td><input type="number" class="form-control" name="terjual[]" required></td>
            <td><button type="button" class="btn btn-outline-danger mx-1 delete-confirm" onclick="removeRow(this)">Hapus</button></td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
    }
}

function removeRow(button) {
    if (rowCount > 1) {
        button.closest('tr').remove();
        rowCount--;
    }
}

function clearForm() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = `
        <tr>
            <td><input type="text" class="form-control" name="bulan[]" placeholder="2024-01" required></td>
            <td><input type="number" class="form-control" name="terjual[]" required></td>
            <td><button type="button" class="btn btn-outline-danger mx-1 delete-confirm" onclick="removeRow(this)">Hapus</button></td>
        </tr>
    `;
    rowCount = 1;
}

function generateSampleData() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    rowCount = 0;
    
    const today = new Date();
    for (let i = 0; i < 12; i++) {
        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const sales = Math.floor(Math.random() * 100) + 50; // Random sales between 50-150
        
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="bulan[]" value="${year}-${month}" required></td>
            <td><input type="number" class="form-control" name="terjual[]" value="${sales}" required></td>
            <td><button type="button" class="btn btn-outline-danger mx-1 delete-confirm" onclick="removeRow(this)">Hapus</button></td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
    }
}

function loadFromDatabase() {
    // Show loading state
    const loadButton = event.target;
    const originalText = loadButton.innerHTML;
    loadButton.innerHTML = 'Loading...';
    loadButton.disabled = true;

    // Fetch data from your database using AJAX
    fetch('/admin/forecast/get-sales-data', {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(response => {
        if (response.status !== 'success') {
            throw new Error(response.message || 'Unknown error');
        }
        const data = response.data;
        const tbody = document.getElementById('dataTableBody');
        tbody.innerHTML = '';
        rowCount = 0;

        data.forEach(item => {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" class="form-control" name="bulan[]" value="${item.bulan}" required></td>
                <td><input type="number" class="form-control" name="terjual[]" value="${item.terjual}" required></td>
                <td><button type="button" class="btn btn-outline-danger mx-1 delete-confirm" onclick="removeRow(this)">Hapus</button></td>
            `;
            tbody.appendChild(newRow);
            rowCount++;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading data from database: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        loadButton.innerHTML = originalText;
        loadButton.disabled = false;
    });
}
</script>
@endsection 