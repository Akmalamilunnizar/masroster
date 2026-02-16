@extends('admin.layouts.template')
@section('page_title')
    CIME | Halaman Daftar Ukuran
@endsection
@section('search')
<div class="col-md-4">
    <div class="position-relative search-box-wrapper">
        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
        <input type="text" id="ukuranSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
            style="height: 42px;" placeholder="Cari nama atau dimensi...">
    </div>
</div>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-2"><span class="text-muted fw-light">Dashboard /</span> Daftar Ukuran</h4>
        
        <!-- Advanced Filter Section -->
        <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Filter Satuan</label>
                        <select id="filterSatuan" class="form-select border-0 bg-light">
                            <option value="">Semua Satuan</option>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('addukuran') }}" class="btn btn-primary shadow-sm px-4" style="border-radius: 10px;">
                <i class="fas fa-plus me-2"></i>Tambah Ukuran Roster
            </a>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 10px;">
                <i class="fas fa-check-circle me-2"></i>{{ session()->get('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #ffab00 0%, #ff8f00 100%);">
                <h5 class="mb-0 text-white fw-bold"><i class="fas fa-ruler-combined me-2"></i>Daftar Ukuran Tersedia</h5>
                <span class="badge bg-white text-warning rounded-pill px-3 py-2" id="ukuranCountDisplay">0 Ukuran</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold text-center align-middle" style="width: 60px; min-width: 60px;">No</th>
                            <th class="fw-bold text-center align-middle">Nama Ukuran</th>
                            <th class="fw-bold text-center align-middle" style="width: 100px;">Panjang</th>
                            <th class="fw-bold text-center align-middle" style="width: 100px;">Lebar</th>
                            <th class="fw-bold text-center align-middle" style="width: 80px;">Satuan</th>
                            <th class="fw-bold text-center align-middle" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sizes as $index => $size)
                            <tr class="align-middle">
                                <td class="text-center fw-semibold">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $size->nama }}</td>
                                <td class="text-center">{{ $size->panjang }}</td>
                                <td class="text-center">{{ $size->lebar }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">Cm</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('editukuran', $size->id_ukuran) }}" 
                                           class="btn btn-sm btn-warning" 
                                           style="border-radius: 6px; min-width: 70px;">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="{{ route('deleteukuran', $size->id_ukuran) }}" 
                                            class="btn btn-sm btn-danger" 
                                            style="border-radius: 6px; min-width: 70px;"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus ukuran ini?')">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('ukuranSearchInput');
        const filterSatuan = document.getElementById('filterSatuan');
        const resetBtn = document.getElementById('resetFilters');
        const tableRows = document.querySelectorAll('tbody tr'); // Select all rows initially
        const countDisplay = document.getElementById('ukuranCountDisplay');

        function updateCount() {
            const visible = Array.from(tableRows).filter(r => r.style.display !== 'none').length;
            if(countDisplay) countDisplay.textContent = visible + ' Ukuran';
        }

        // Populate dynamic filters
        const satuanSet = new Set();
        tableRows.forEach(row => {
            // Ensure cell index 4 exists before accessing textContent
            if (row.cells.length > 4) {
                const s = row.cells[4]?.textContent.trim();
                if (s && s !== '-') satuanSet.add(s);
            }
        });
        satuanSet.forEach(v => filterSatuan.add(new Option(v, v)));

        function applyFilters() {
            const term = searchInput.value.toLowerCase();
            const selS = filterSatuan.value;

            tableRows.forEach(row => {
                // Ensure cells exist before accessing
                if (row.cells.length > 4) {
                    const rowText = Array.from(row.cells)
                        .slice(1, 4) // Name, Panjang, Lebar
                        .map(cell => cell.textContent.trim())
                        .join(' ')
                        .toLowerCase();
                    
                    const satuan = row.cells[4]?.textContent.trim();

                    const matchesSearch = rowText.includes(term);
                    const matchesS = !selS || satuan === selS;

                    row.style.display = (matchesSearch && matchesS) ? '' : 'none';
                } else {
                    row.style.display = 'none'; // Hide rows that don't conform to expected structure
                }
            });
            updateCount();
        }

        if (searchInput) searchInput.addEventListener('keyup', applyFilters);
        if (filterSatuan) filterSatuan.addEventListener('change', applyFilters);
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                searchInput.value = '';
                filterSatuan.value = '';
                applyFilters();
            });
        }
        updateCount(); // Initial count update on page load
    });
    </script>
@endsection
