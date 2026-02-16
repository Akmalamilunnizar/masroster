@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Detail Harga
@endsection

@section('search')
<div class="col-md-4">
                    <div class="position-relative search-box-wrapper">
                        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
                        <input type="text" id="priceSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
                            style="height: 42px;" placeholder="Cari data harga...">
                    </div>
                </div>

@endsection

@section('content')
<style>
    /* Default row layout for tablets (769px to 1199px) where sidebar is hidden */
    .action-buttons-cell {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }

    /* Stack buttons vertically on Desktop (sidebar open) and Mobile */
    @media (min-width: 1200px), (max-width: 768px) {
        .action-buttons-cell {
            flex-direction: column !important;
            gap: 0.35rem !important;
        }
        .btn-action {
            width: 40px !important;
            height: 36px !important;
            padding: 0 !important;
            border-radius: 6px !important;
        }
        /* Hide labels on desktop/mobile to maximize table space */
        .btn-action span {
            display: none !important;
        }
        .btn-action i {
            margin-right: 0 !important;
            font-size: 1.1rem;
        }
    }

    /* Horizontal layout with labels only for tablets */
    @media (min-width: 769px) and (max-width: 1199.98px) {
        .btn-action {
            width: 130px !important;
            display: inline-flex !important;
        }
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Advanced Filter Section -->
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Filter Jenis</label>
                    <select id="filterJenis" class="form-select border-0 bg-light">
                        <option value="">Semua Jenis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Filter Tipe</label>
                    <select id="filterTipe" class="form-select border-0 bg-light">
                        <option value="">Semua Tipe</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Filter Ukuran</label>
                    <select id="filterUkuran" class="form-select border-0 bg-light">
                        <option value="">Semua Ukuran</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Filter Motif</label>
                    <select id="filterMotif" class="form-select border-0 bg-light">
                        <option value="">Semua Motif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Filter Pembeli</label>
                    <select id="filterPembeli" class="form-select border-0 bg-light">
                        <option value="">Semua Pembeli</option>
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
    <!-- Enhanced Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title">Detail Harga</h4>
                <p class="page-subtitle">Kelola harga roster per pembeli dan ukuran</p>
            </div>
            <div class="bulk-actions">
                <button id="selectAllBtn" class="btn btn-outline-secondary btn-enhanced" 
                        title="Pilih semua item untuk operasi batch. Detail dan Edit button akan dinonaktifkan.">
                    <i class="fas fa-check-square me-1"></i> Pilih Semua
                </button>
                <button id="batchDeleteBtn" class="btn btn-danger btn-enhanced" style="display: none;">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                </button>
                <a href="{{ route('detailharga.create') }}" class="btn btn-primary btn-enhanced">
                    <i class="fas fa-plus me-2"></i>Tambah Harga
                </a>
            </div>
        </div>
    </div>

    <!-- Bulk Selection Notification -->
    <div id="bulkSelectionNotification" class="selection-notification" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Mode Seleksi Aktif:</strong> Detail dan Edit button telah dinonaktifkan.
        Hanya operasi Hapus yang tersedia untuk item yang dipilih.
        <button type="button" class="btn-close float-end" onclick="clearSelection()"></button>
    </div>

    @if (session('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Enhanced Price Table -->
    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);">
            <h5 class="card-title mb-0 text-white">
                <i class="fas fa-list me-2"></i>Data Harga Roster
            </h5>
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill" id="itemCounter">0 Konten</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-enhanced">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>ID Roster</th>
                        <th>Jenis</th>
                        <th>Tipe</th>
                        <th>Motif</th>
                        <th>Ukuran</th>
                        <th>Pembeli</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailHarga as $harga)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input harga-checkbox"
                                value="{{ $harga->id_roster }}_{{ $harga->id_user }}_{{ $harga->id_ukuran }}">
                        </td>
                        <td>
                            <strong>{{ $harga->id_roster }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary status-badge">{{ $harga->roster && $harga->roster->jenisRoster ? $harga->roster->jenisRoster->JenisBarang : '-' }}</span>
                        </td>
                        <td>{{ $harga->roster && $harga->roster->tipeRoster ? $harga->roster->tipeRoster->namaTipe : '-' }}</td>
                        <td>
                            @if($harga->roster && $harga->roster->motif)
                            <span class="badge bg-info status-badge">{{ $harga->roster->motif->nama_motif }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($harga->size)
                            <span class="badge bg-warning status-badge">{{ $harga->size->nama }} ({{ $harga->size->panjang }}×{{ $harga->size->lebar }} cm)</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $harga->user->f_name ?? '-' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-success status-badge">Rp {{ number_format($harga->harga, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <div class="action-buttons-cell">
                                <a href="{{ route('detailharga.edit', [$harga->id_roster, $harga->id_user, $harga->id_ukuran]) }}"
                                    class="btn btn-warning btn-action action-btn" title="Edit Harga">
                                    <i class="bx bxs-edit me-1"></i> <span>Edit Harga</span>
                                </a>
                                <form id="deleteForm{{ $harga->id_roster }}_{{ $harga->id_user }}_{{ $harga->id_ukuran }}" 
                                      action="{{ route('detailharga.destroy', [$harga->id_roster, $harga->id_user, $harga->id_ukuran]) }}"
                                    method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-action"
                                        onclick="confirmDelete('{{ $harga->id_roster }}_{{ $harga->id_user }}_{{ $harga->id_ukuran }}')" title="Hapus Harga">
                                        <i class="bx bxs-trash me-1"></i> <span>Hapus Harga</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Tidak ada data harga</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const hargaCheckboxes = document.querySelectorAll('.harga-checkbox');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const batchDeleteBtn = document.getElementById('batchDeleteBtn');
        const selectedCountSpan = document.getElementById('selectedCount');
        const actionButtons = document.querySelectorAll('.action-btn'); // Edit buttons

        // Function to toggle action buttons based on selection state
        function toggleActionButtons() {
            const checkedCount = Array.from(hargaCheckboxes).filter(cb => cb.checked).length;
            const isAnySelected = checkedCount > 0;
            const notification = document.getElementById('bulkSelectionNotification');

            actionButtons.forEach(btn => {
                if (isAnySelected) {
                    // Disable Edit buttons when items are selected
                    btn.classList.add('disabled-bulk');
                    btn.title = 'Disabled when items are selected for bulk operations';
                } else {
                    // Re-enable all buttons when no items are selected
                    btn.classList.remove('disabled-bulk');
                    btn.title = '';
                }
            });

            // Show/hide notification
            if (notification) {
                notification.style.display = isAnySelected ? 'block' : 'none';
            }

            // Add visual indicator to table rows when items are selected
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                if (hargaCheckboxes[index] && hargaCheckboxes[index].checked) {
                    row.classList.add('selection-active');
                } else {
                    row.classList.remove('selection-active');
                }
            });
        }

        // Function to clear all selections
        function clearSelection() {
            hargaCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
            updateSelectedCount();
            updateBatchDeleteButton();
            toggleActionButtons();
        }

        // Select All functionality
        selectAllCheckbox.addEventListener('change', function() {
            hargaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
            updateBatchDeleteButton();
            toggleActionButtons();
        });

        // Individual checkbox functionality
        hargaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateSelectedCount();
                updateBatchDeleteButton();
                toggleActionButtons();
            });
        });

        // Select All button functionality
        selectAllBtn.addEventListener('click', function() {
            const allChecked = Array.from(hargaCheckboxes).every(cb => cb.checked);
            hargaCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            selectAllCheckbox.checked = !allChecked;
            updateSelectedCount();
            updateBatchDeleteButton();
            toggleActionButtons();
        });

        // Batch Delete functionality
        batchDeleteBtn.addEventListener('click', function() {
            const selectedItems = Array.from(hargaCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada item yang dipilih',
                    text: 'Silakan pilih item yang ingin dihapus terlebih dahulu.'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Hapus Batch',
                text: `Apakah Anda yakin ingin menghapus ${selectedItems.length} item yang dipilih?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("detailharga.batch-delete") }}';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);

                    selectedItems.forEach(itemId => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'harga_ids[]';
                        input.value = itemId;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        function updateSelectAllState() {
            const checkedCount = Array.from(hargaCheckboxes).filter(cb => cb.checked).length;
            const totalCount = hargaCheckboxes.length;
            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        function updateSelectedCount() {
            const checkedCount = Array.from(hargaCheckboxes).filter(cb => cb.checked).length;
            selectedCountSpan.textContent = checkedCount;
        }

        function updateBatchDeleteButton() {
            const checkedCount = Array.from(hargaCheckboxes).filter(cb => cb.checked).length;
            if (checkedCount > 0) {
                batchDeleteBtn.style.display = 'inline-block';
            } else {
                batchDeleteBtn.style.display = 'none';
            }
        }

        // Initialize on page load
        toggleActionButtons();

        // Keyboard shortcut: Escape key to clear selection
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const checkedCount = Array.from(hargaCheckboxes).filter(cb => cb.checked).length;
                if (checkedCount > 0) {
                    clearSelection();
                    // Show a brief notification
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="toast show" role="alert">
                            <div class="toast-header">
                                <strong class="me-auto">Selection Cleared</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                            </div>
                            <div class="toast-body">
                                All selections have been cleared (ESC key pressed)
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            }
        });

        // Individual row delete confirmation
        window.confirmDelete = function(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus Harga',
                text: 'Apakah Anda yakin ingin menghapus data harga ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`deleteForm${id}`).submit();
                }
            });
        };

        // Search & Filter functionality
        const searchInput = document.getElementById('priceSearchInput');
        const filterJenis = document.getElementById('filterJenis');
        const filterTipe = document.getElementById('filterTipe');
        const filterUkuran = document.getElementById('filterUkuran');
        const filterMotif = document.getElementById('filterMotif');
        const filterPembeli = document.getElementById('filterPembeli');
        const resetBtn = document.getElementById('resetFilters');
        const tableRows = document.querySelectorAll('.table-enhanced tbody tr:not(.empty-row)');
        const counter = document.getElementById('itemCounter');

        function updateCount() {
            const visible = Array.from(tableRows).filter(r => r.style.display !== 'none').length;
            counter.textContent = visible + ' Konten';
        }
        updateCount();

        // Populate dynamic filters
        const jenisSet = new Set();
        const tipeSet = new Set();
        const ukuranSet = new Set();
        const motifSet = new Set();
        const pembeliSet = new Set();
        tableRows.forEach(row => {
            const j = row.cells[2]?.textContent.trim();
            const t = row.cells[3]?.textContent.trim();
            const m = row.cells[4]?.textContent.trim();
            const u = row.cells[5]?.textContent.trim();
            const p = row.cells[6]?.textContent.trim();
            if (j && j !== '-') jenisSet.add(j);
            if (t && t !== '-') tipeSet.add(t);
            if (m && m !== '-') motifSet.add(m);
            if (u && u !== '-') ukuranSet.add(u);
            if (p && p !== '-') pembeliSet.add(p);
        });
        jenisSet.forEach(v => filterJenis.add(new Option(v, v)));
        tipeSet.forEach(v => filterTipe.add(new Option(v, v)));
        ukuranSet.forEach(v => filterUkuran.add(new Option(v, v)));
        motifSet.forEach(v => filterMotif.add(new Option(v, v)));
        pembeliSet.forEach(v => filterPembeli.add(new Option(v, v)));

        function applyAdvancedFilters() {
            const term = searchInput.value.toLowerCase();
            const selJ = filterJenis.value;
            const selT = filterTipe.value;
            const selU = filterUkuran.value;
            const selM = filterMotif.value;
            const selP = filterPembeli.value;

            tableRows.forEach(row => {
                const jenis = row.cells[2]?.textContent.trim();
                const tipe = row.cells[3]?.textContent.trim();
                const motif = row.cells[4]?.textContent.trim();
                const ukuran = row.cells[5]?.textContent.trim();
                const pembeli = row.cells[6]?.textContent.trim();
                
                // Get all text content from data cells (index 1 to 7) for the general search
                const searchContent = Array.from(row.cells)
                    .slice(1, 8)
                    .map(cell => cell.textContent.trim())
                    .join(' ')
                    .toLowerCase();

                const matchesSearch = searchContent.includes(term);
                const matchesJ = !selJ || jenis === selJ;
                const matchesT = !selT || tipe === selT;
                const matchesU = !selU || ukuran === selU;
                const matchesM = !selM || motif === selM;
                const matchesP = !selP || pembeli === selP;

                row.style.display = (matchesSearch && matchesJ && matchesT && matchesU && matchesM && matchesP) ? '' : 'none';
            });
            updateCount();
        }

        if (searchInput) searchInput.addEventListener('keyup', applyAdvancedFilters);
        filterJenis.addEventListener('change', applyAdvancedFilters);
        filterTipe.addEventListener('change', applyAdvancedFilters);
        filterUkuran.addEventListener('change', applyAdvancedFilters);
        filterMotif.addEventListener('change', applyAdvancedFilters);
        filterPembeli.addEventListener('change', applyAdvancedFilters);
        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            filterJenis.value = '';
            filterTipe.value = '';
            filterUkuran.value = '';
            filterMotif.value = '';
            filterPembeli.value = '';
            applyAdvancedFilters();
        });
    });
</script>
@endsection