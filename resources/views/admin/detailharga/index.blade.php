@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Detail Harga
@endsection

@section('content')
<style>
    /* Enhanced styling for better visual hierarchy and alignment */
    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    
    .page-title {
        color: #495057;
        font-weight: 700;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6c757d;
        font-size: 0.875rem;
        margin: 0.25rem 0 0 0;
    }

    .table-enhanced {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .table-enhanced thead th {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem 0.75rem;
        text-align: center;
        vertical-align: middle;
    }
    
    .table-enhanced tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .table-enhanced tbody tr:hover {
        background-color: #f8f9fa;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .action-buttons-cell {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        border: none;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: scale(1.05);
    }

    .btn-enhanced {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-enhanced:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .selection-notification {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 1px solid #2196f3;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    /* Enhanced styling for disabled action buttons */
    .btn-warning.disabled-bulk,
    .btn-info.disabled-bulk {
        opacity: 0.5 !important;
        pointer-events: none !important;
        cursor: not-allowed !important;
        position: relative;
    }

    .btn-warning.disabled-bulk::after,
    .btn-info.disabled-bulk::after {
        content: "⛔";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 12px;
        color: #dc3545;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Enhanced batch delete button */
    #batchDeleteBtn {
        transition: all 0.3s ease;
        position: relative;
    }

    #batchDeleteBtn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    /* Selection indicator */
    .selection-active {
        background-color: rgba(13, 110, 253, 0.1) !important;
        border-left: 4px solid #0d6efd !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
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
    <div class="card">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Data Harga Roster
            </h5>
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
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('detailharga.destroy', [$harga->id_roster, $harga->id_user, $harga->id_ukuran]) }}"
                                    method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-action"
                                        onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Harga">
                                        <i class="fas fa-trash-alt"></i>
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
    });
</script>
@endsection