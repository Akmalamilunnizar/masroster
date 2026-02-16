@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Daftar Customer
@endsection

@section('search')
<div class="col-md-4">
    <div class="position-relative search-box-wrapper">
        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
        <input type="text" id="customerSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
            style="height: 42px;" placeholder="Cari nama, email, atau telepon...">
    </div>
</div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-2"><span class="text-muted fw-light">Dashboard /</span> Daftar Customer</h4>
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session()->get('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Mini Stats Overlay -->
    <!-- Advanced Filter Section -->
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filter Alamat</label>
                    <select id="filterAlamat" class="form-select border-0 bg-light">
                        <option value="">Semua Customer</option>
                        <option value="punya">Punya Alamat</option>
                        <option value="tidak">Tidak Punya Alamat</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-secondary w-100 btn-sm" style="height: 38px;">
                        <i class="bx bx-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

<div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
    <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #696cff 0%, #4338ca 100%);">
        <h5 class="mb-0 text-white fw-bold"><i class="fas fa-users me-2"></i>Customer Terdaftar</h5>
    </div>
    <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                     <thead class="table-primary">
                <tr>
                  <th style="text-align: center; font-weight: bold;">Id</th>
                  <th style="text-align: center; font-weight: bold;">Nama Customer</th>
                  <th style="text-align: center; font-weight: bold;">Nomor Telepon</th>
                  <th style="text-align: center; font-weight: bold;">Email</th>
                  <th style="text-align: center; font-weight: bold;">Alamat Utama</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">

                @foreach ($customer->where('user', 'User') as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->id }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('customerDetails', $item->id) }}" class="text-primary fw-bold">
                                {{ $item->f_name }}
                            </a>
                        </td>
                        <td style="text-align: center;">{{ $item->nomor_telepon }}</td>
                        <td style="text-align: center;">{{ $item->email }}</td>
                        <td style="text-align: center;">
                            {{ $item->is_default ? $item->defaultAddress->full_address : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Bootstrap Table with Header - Light -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearchInput');
    const filterAlamat = document.getElementById('filterAlamat');
    const resetBtn = document.getElementById('resetFilters');
    const tableRows = document.querySelectorAll('tbody tr');
    let customerCountDisplay = document.getElementById('customerCountDisplay'); // Changed to let

    function updateStats() {
        const visible = Array.from(tableRows).filter(r => r.style.display !== 'none').length;
        if(customerCountDisplay) customerCountDisplay.textContent = visible + ' Customer'; // Updated text
    }

    function applyFilters() {
        const term = searchInput.value.toLowerCase();
        const selAlamat = filterAlamat.value;
        
        tableRows.forEach(row => {
            const rowText = Array.from(row.cells)
                .slice(1, 5)
                .map(cell => cell.textContent.trim())
                .join(' ')
                .toLowerCase();
            
            const address = row.cells[4].textContent.trim();
            
            const matchesSearch = rowText.includes(term);
            let matchesAlamat = true;
            if (selAlamat === 'punya') matchesAlamat = (address !== '-');
            if (selAlamat === 'tidak') matchesAlamat = (address === '-');
            
            row.style.display = (matchesSearch && matchesAlamat) ? '' : 'none';
        });
        updateStats();
    }

    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (filterAlamat) filterAlamat.addEventListener('change', applyFilters);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            filterAlamat.value = '';
            applyFilters();
        });
    }

    // Initial count update
    // Check if we need to add a total count somewhere else if display was removed from stats card
    // But since display was in a row we removed, let's add a badge to card header
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader && !customerCountDisplay) { // Only create if it doesn't exist
        const badge = document.createElement('span');
        badge.className = 'badge bg-white text-primary px-3 py-2 rounded-pill';
        badge.id = 'customerCountDisplay';
        badge.textContent = '0 Customer';
        cardHeader.appendChild(badge);
        customerCountDisplay = badge; // Assign the newly created badge to the variable
    }
    updateStats();
});
</script>
@endpush