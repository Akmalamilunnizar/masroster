@extends('admin.layouts.template')

@section('page_title')
    CIME | Daftar Tipe Produk
@endsection

@section('search')
<div class="col-md-4">
    <div class="position-relative search-box-wrapper">
        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
        <input type="text" id="tipeSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
            style="height: 42px;" placeholder="Cari nama tipe roster...">
    </div>
</div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4 g-3 align-items-center">
        <div class="col-md-7">
            <h4 class="mb-0 fw-bold text-dark">
                <span class="text-muted fw-light">Tipe Roster /</span> Daftar Tipe Produk
            </h4>
        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="text-muted small fw-bold">FILTER JENIS ROSTER</label>
                    <select id="filterJenisRoster" class="form-select border-0 bg-light">
                        <option value="">Semua Jenis Roster</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-secondary w-100 btn-sm" style="height: 38px;">
                        <i class="bx bx-undo me-1"></i> Reset
                    </button>
                </div>
                <div class="col-md-5">
                        <span class="text-muted small fw-bold">TOTAL DATA:</span>
                        <span class="text-primary fw-bold ms-2" id="tipeCountDisplay">0</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);">
            <h5 class="card-title mb-0 text-white fw-bold">
                <i class="fas fa-list me-2"></i>Daftar Tipe Produk
            </h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm" id="edit-selected" style="display: none; border-radius: 8px;">
                    <i class="fas fa-edit me-1"></i>Edit (<span id="selected-count">0</span>)
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="delete-selected" style="display: none; border-radius: 8px;">
                    <i class="fas fa-trash me-1"></i>Hapus (<span id="selected-count-3">0</span>)
                </button>
                <a href="{{ route('addtiperoster') }}" class="btn btn-light btn-sm fw-bold text-primary" style="border-radius: 8px;">
                    <i class="fas fa-plus me-1"></i>Tambah Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th class="text-center">ID</th>
                            <th class="text-center">Nama Tipe Roster</th>
                            <th class="text-center">Terhubung dengan Jenis Roster</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tipeRosters as $tipe)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input tipe-checkbox" value="{{ $tipe->IdTipe }}">
                            </td>
                            <td class="text-center">{{ $tipe->IdTipe }}</td>
                            <td class="text-center">{{ $tipe->namaTipe }}</td>
                            <td class="text-center">
                                @if($tipe->jenisRosters->count() > 0)
                                    @foreach($tipe->jenisRosters as $jenis)
                                        <span class="badge bg-primary me-1">{{ $jenis->JenisBarang }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Tidak terhubung</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('edittiperoster', $tipe->IdTipe) }}" 
                                        class="btn btn-sm btn-warning" 
                                        style="border-radius: 6px; min-width: 70px;">
                                         <i class="fas fa-edit me-1"></i> Edit
                                     </a>
                                    <button type="button" class="btn btn-sm btn-danger"  style="border-radius: 6px; min-width: 70px;" onclick="confirmDelete({{ $tipe->IdTipe }})">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data tipe roster</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Batch Delete Form -->
<form id="batch-delete-form" action="{{ route('batch.delete.tiperoster') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="tipe_ids" id="tipe-ids">
</form>

@endsection

@push('scripts')
<script>
let selectedTipes = [];

// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.tipe-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        if (this.checked) {
            selectedTipes.push(checkbox.value);
        } else {
            selectedTipes = [];
        }
    });
    updateSelectionState();
});

// Individual checkbox selection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('tipe-checkbox')) {
        if (e.target.checked) {
            selectedTipes.push(e.target.value);
        } else {
            selectedTipes = selectedTipes.filter(id => id !== e.target.value);
        }
        updateSelectionState();
    }
});

function updateSelectionState() {
    const count = selectedTipes.length;
    const editBtn = document.getElementById('edit-selected');
    const deleteBtn = document.getElementById('delete-selected');
    
    // Update count displays
    document.getElementById('selected-count').textContent = count;
    document.getElementById('selected-count-3').textContent = count;
    
    // Show/hide buttons based on selection
    if (count > 0) {
        editBtn.style.display = 'inline-block';
        deleteBtn.style.display = 'inline-block';
    } else {
        editBtn.style.display = 'none';
        deleteBtn.style.display = 'none';
    }
}

// Stats & Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('tbody tr:not(.empty-row)');
    const countDisplay = document.getElementById('tipeCountDisplay');
    const searchInput = document.getElementById('tipeSearchInput');
    const filterJenis = document.getElementById('filterJenisRoster');
    const resetBtn = document.getElementById('resetFilters');

    function updateCount() {
        const visible = Array.from(tableRows).filter(r => r.style.display !== 'none').length;
        countDisplay.textContent = visible;
    }
    updateCount();

    // Populate Dynamic Filter
    const jenisSet = new Set();
    tableRows.forEach(row => {
        const badges = row.querySelectorAll('.badge.bg-primary');
        badges.forEach(b => jenisSet.add(b.textContent.trim()));
    });
    jenisSet.forEach(v => filterJenis.add(new Option(v, v)));

    function applyFilters() {
        const term = searchInput.value.toLowerCase();
        const selJ = filterJenis.value;

        tableRows.forEach(row => {
            const name = row.cells[2].textContent.toLowerCase();
            const badges = Array.from(row.querySelectorAll('.badge.bg-primary')).map(b => b.textContent.trim());
            
            const matchesSearch = name.includes(term);
            const matchesJ = !selJ || badges.includes(selJ);

            row.style.display = (matchesSearch && matchesJ) ? '' : 'none';
        });
        updateCount();
    }

    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (filterJenis) filterJenis.addEventListener('change', applyFilters);
    if (resetBtn) resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        filterJenis.value = '';
        applyFilters();
    });
});

// Batch delete functionality
document.getElementById('delete-selected').addEventListener('click', function() {
    if (selectedTipes.length === 0) return;
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus ${selectedTipes.length} tipe roster yang dipilih?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('tipe-ids').value = JSON.stringify(selectedTipes);
            document.getElementById('batch-delete-form').submit();
        }
    });
});

// Individual delete confirmation
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus tipe roster ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/delete-tipe-roster/${id}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<style>
.disabled-bulk {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}

.disabled-bulk::after {
    content: "⛔";
    margin-left: 5px;
}
</style>
@endpush
