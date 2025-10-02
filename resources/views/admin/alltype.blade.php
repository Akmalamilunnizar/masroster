@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Daftar Jenis Barang
@endsection
@section('search')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* Enhanced styling for disabled action buttons */
    .btn-warning.disabled-bulk {
        opacity: 0.5 !important;
        pointer-events: none !important;
        cursor: not-allowed !important;
        position: relative;
    }
    
    .btn-warning.disabled-bulk::after {
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
    
    /* Tooltip for disabled buttons */
    .btn-warning.disabled-bulk:hover::before {
        content: "Disabled during bulk selection";
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        margin-bottom: 5px;
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

    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            {{-- <form method="GET" action={{ route('searchitem') }}> --}}
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..."
                style="width: 600px;" />
            </form>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Daftar Jenis</h4>
        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('addtype') }}" class="btn btn-outline-primary">
                + Tambah Jenis
            </a>
            <button id="selectAllBtn" class="btn btn-outline-secondary" style="border-radius: 8px;" 
                    title="Pilih semua item untuk operasi batch. Edit button akan dinonaktifkan.">
                <i class="fas fa-check-square me-1"></i> Pilih Semua
            </button>
            <button id="batchDeleteBtn" class="btn btn-danger" style="border-radius: 8px; display: none;">
                <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            
            <!-- Bulk Selection Notification -->
            <div id="bulkSelectionNotification" class="alert alert-info mt-3" style="display: none; border-radius: 8px;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Mode Seleksi Aktif:</strong> Edit button telah dinonaktifkan. 
                Hanya operasi Hapus yang tersedia untuk item yang dipilih.
                <button type="button" class="btn-close float-end" onclick="clearSelection()"></button>
            </div>
        </div>
        @if (session()->has('message'))
            @php
                $alertType = session('alert') ?? 'success'; // default success kalau nggak ada
            @endphp
            <div class="alert alert-{{ $alertType }} alert-dismissible fade show" role="alert">
                {{ session()->get('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <h5 class="card-header fw-bold">Jenis Yang Tersedia</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                     <thead class="table-primary">
                       <tr>
                           <th class="fw-bold" style="text-align: center;">
                               <input type="checkbox" id="selectAll" class="form-check-input">
                           </th>
                           <th class="fw-bold" style="text-align: center;">Id</th>
                            <th class="fw-bold" style="text-align: center;">Nama Jenis</th>
                            <th class="fw-bold" style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">

                        @foreach ($type as $item)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="form-check-input item-checkbox" value="{{ $item->IdJenisBarang }}">
                                </td>
                               <td style="text-align: center;">{{ $item->IdJenisBarang }}</td>
                                <td style="text-align: center;">{{ $item->JenisBarang }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ route('edittype', $item->IdJenisBarang) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('deletetype', $item->IdJenisBarang) }}" class="btn btn-danger" onclick="return confirm('Yakin ingin hapus data ini?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Bootstrap Table with Header - Light -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const batchDeleteBtn = document.getElementById('batchDeleteBtn');
            const selectedCountSpan = document.getElementById('selectedCount');
            const actionButtons = document.querySelectorAll('.btn-warning'); // Edit buttons

            // Function to toggle action buttons based on selection state
            function toggleActionButtons() {
                const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
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
                    if (itemCheckboxes[index] && itemCheckboxes[index].checked) {
                        row.classList.add('selection-active');
                    } else {
                        row.classList.remove('selection-active');
                    }
                });
            }
            
            // Function to clear all selections
            function clearSelection() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectAllCheckbox.checked = false;
                updateSelectedCount();
                updateBatchDeleteButton();
                toggleActionButtons();
            }

            // Select All functionality
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
                updateBatchDeleteButton();
                toggleActionButtons();
            });

            // Individual checkbox functionality
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllState();
                    updateSelectedCount();
                    updateBatchDeleteButton();
                    toggleActionButtons();
                });
            });

            // Select All button functionality
            selectAllBtn.addEventListener('click', function() {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = !allChecked;
                });
                selectAllCheckbox.checked = !allChecked;
                updateSelectedCount();
                updateBatchDeleteButton();
                toggleActionButtons();
            });

            // Batch Delete functionality
            batchDeleteBtn.addEventListener('click', function() {
                const selectedItems = Array.from(itemCheckboxes)
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
                    text: `Apakah Anda yakin ingin menghapus ${selectedItems.length} jenis barang yang dipilih?`,
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
                        form.action = '{{ route("batch.delete.types") }}';
                        
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
                            input.name = 'type_ids[]';
                            input.value = itemId;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            function updateSelectAllState() {
                const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
                const totalCount = itemCheckboxes.length;
                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            }

            function updateSelectedCount() {
                const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
                selectedCountSpan.textContent = checkedCount;
            }

            function updateBatchDeleteButton() {
                const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
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
                    const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
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
