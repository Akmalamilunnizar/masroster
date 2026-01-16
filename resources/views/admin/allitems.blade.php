@extends('admin.layouts.template')
@section('page_title')
    CIME | Halaman Daftar Barang
@endsection
@section('search')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Enhanced styling for disabled action buttons */
        .btn-info.disabled-bulk,
        .btn-warning.disabled-bulk {
            opacity: 0.5 !important;
            pointer-events: none !important;
            cursor: not-allowed !important;
            position: relative;
        }
        
        .btn-info.disabled-bulk::after,
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
        .btn-info.disabled-bulk:hover::before,
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
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama..." value="{{ isset($search) ? $search : '' }}"
                aria-label="Pencarian..." />
        </div>
    </div>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 py-1">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Semua Barang</h4>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('additems') }}" class="btn btn-primary" style="border-radius: 8px;">
                    + Tambah Barang
                </a>
                <a href="{{ route('exititems') }}" class="btn btn-danger" style="border-radius: 8px;">
                    + Barang Keluar
                </a>
                <button id="selectAllBtn" class="btn btn-outline-secondary" style="border-radius: 8px;" 
                        title="Pilih semua item untuk operasi batch. Detail dan Edit button akan dinonaktifkan.">
                    <i class="fas fa-check-square me-1"></i> Pilih Semua
                </button>
                <button id="batchDeleteBtn" class="btn btn-danger" style="border-radius: 8px; display: none;">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                </button>
                
                <!-- Bulk Selection Notification -->
                <div id="bulkSelectionNotification" class="alert alert-info mt-3" style="display: none; border-radius: 8px;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Mode Seleksi Aktif:</strong> Detail dan Edit button telah dinonaktifkan. 
                    Hanya operasi Hapus yang tersedia untuk item yang dipilih.
                    <button type="button" class="btn-close float-end" onclick="clearSelection()"></button>
                </div>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Filter Barang</h5>
            <div class="card-body">
                <form method="GET" action="{{ route('allitems') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    {{-- Filter Jenis Barang --}}
                    <div class="d-flex align-items-center gap-2">
                        <label for="jenis_barang" class="form-label mb-0">Jenis Barang:</label>
                        <select name="jenis_barang" id="jenis_barang" class="form-select" style="width: 160px; border-radius: 8px;">
                            <option value="">Semua Jenis</option>
                            @foreach($jenisBarang as $jenis)
                                <option value="{{ $jenis->IdJenisBarang }}" {{ request('jenis_barang') == $jenis->IdJenisBarang ? 'selected' : '' }}>
                                    {{ $jenis->JenisBarang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Bulan & Tahun --}}
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <label for="bulan" class="form-label mb-0">Bulan:</label>
                        <select name="bulan" id="bulan" class="form-select" style="width: 160px; border-radius: 8px;">
                            <option value="">Semua Bulan</option>
                            @foreach ([
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ] as $key => $value)
                                <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        <label for="tahun" class="form-label mb-0">Tahun:</label>
                        <select name="tahun" id="tahun" class="form-select" style="width: 120px; border-radius: 8px;">
                            <option value="">Semua Tahun</option>
                            @for ($year = 2020; $year <= date('Y'); $year++)
                                <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Search Input --}}
                    <div class="d-flex align-items-center gap-2">
                        <label for="search" class="form-label mb-0">Cari:</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="ID atau Nama Barang" value="{{ request('search') }}" style="width: 200px; border-radius: 8px;">
                    </div>

                    {{-- Filter Button --}}
                    <button type="submit" class="btn btn-outline-primary" style="border-radius: 8px; border-width: 2px; transition: all 0.3s ease;">
                        <i class='bx bx-filter-outline me-1'></i> Filter
                    </button>

                    {{-- Print Button --}}
                    <a href="{{ route('allitems.exportpdf', [
                        'bulan' => request('bulan'),
                        'tahun' => request('tahun'),
                        'jenis_barang' => request('jenis_barang'),
                        'search' => request('search')
                    ]) }}"
                        class="btn btn-danger"
                        style="background: linear-gradient(45deg, #dc3545, #ff6b6b); border-radius: 8px;"
                        target="_blank">
                        <i class='bx bxs-printer me-2'></i> Print Laporan
                    </a>
                </form>
            </div>
        </div>
        {{-- Session messages are automatically displayed via CustomModal --}}
        <div class="card">
            <h5 class="card-header">Barang Yang Tersedia</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th class="fw-bold text-center" style="width: 50px;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th class="fw-bold text-center">Id Masuk </th>
                            <th class="fw-bold text-center">Tanggal Masuk</th>
                            <th class="fw-bold text-center">Nama Supplier </th>
                            <th class="fw-bold text-center">Qty Masuk </th>
                            <th class="fw-bold text-center">Harga Satuan </th>
                            <th class="fw-bold text-center">Sub Total </th>
                            <th class="fw-bold text-center">Nama Barang</th>
                            <th class="fw-bold text-center">JenisBarang</th>
                            <th class="fw-bold text-center">Jumlah Stok</th>
                            <th class="fw-bold text-center">Id Keluar </th>
                            <th class="fw-bold text-center">Tanggal Keluar</th>
                            <th class="fw-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">

                        @foreach ($items as $item)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input item-checkbox" value="{{ $item->IdRoster }}">
                                </td>
                                <td class="text-center">{{ $item->latestDetailMasuk?->IdMasuk ?? '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailMasuk ? \Carbon\Carbon::parse($item->latestDetailMasuk->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailMasuk?->supplier?->NamaSupplier ?? '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailMasuk?->QtyMasuk ?? '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailMasuk?->HargaSatuan ?? '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailMasuk?->SubTotal ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#tambahQtyModal{{ $item->IdRoster }}">
                                        {{ $item->NamaBarang }}
                                    </a>

                                    <div class="modal fade" id="tambahQtyModal{{ $item->IdRoster }}" tabindex="-1"
                                        aria-labelledby="tambahQtyLabel{{ $item->IdRoster }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('barang.tambahQty') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="IdRoster" value="{{ $item->IdRoster }}">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="tambahQtyLabel{{ $item->IdRoster }}">Tambah
                                                            Qty - {{ $item->NamaBarang }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Tutup"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="QtyMasuk" class="form-label">Qty Masuk</label>
                                                            <input type="number" name="QtyMasuk" class="form-control" min="1"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Tambah</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->jenisBarang->JenisBarang }}</td>
                                <td class="text-center">{{ $item->stock }}</td>
                                <td class="text-center">{{ $item->latestDetailKeluar?->IdKeluar ?? '-' }}</td>
                                <td class="text-center">{{ $item->latestDetailKeluar ? \Carbon\Carbon::parse($item->latestDetailKeluar->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                <td class="text-center">
                                <a href="{{ route('admin.detail_allitems', $item->IdRoster) }}" class="btn btn-info" style="border-radius: 8px;">
                                        <i class="fas fa-info-circle me-1"></i> Detail
                                    </a>
                                    <a href="{{ route('edititem', $item->IdRoster) }}" class="btn btn-warning" style="border-radius: 8px;">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('deleteitem', $item->IdRoster) }}" class="btn btn-danger"
                                        onclick="return confirm('Yakin ingin hapus data ini?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
        {{-- Start Riwayat Penambahan Stok --}}
        <h5 class="mt-5 mb-3">Riwayat Penambahan Stok</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th>ID Masuk</th>
                        <th>Nama Barang</th>
                        <th>Qty Masuk</th>
                        <th>Tanggal Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatStok as $log)
                        <tr>
                            <td>{{ $log->IdMasuk }}</td>
                            <td>{{ $log->NamaBarang }}</td>
                            <td>{{ $log->QtyMasuk }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->tanggal_masuk)->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- End Riwayat Penambahan Stok --}}

        {{-- Start Riwayat Pengeluaran Stok --}}
        <h5 class="mt-5 mb-3">Riwayat Pengeluaran Stok</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th>ID Keluar</th>
                        <th>Nama Barang</th>
                        <th>Qty Keluar</th>
                        <th>Tanggal Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatKeluar as $log)
                        <tr>
                            <td>{{ $log->IdKeluar }}</td>
                            <td>{{ $log->NamaBarang }}</td>
                            <td>{{ $log->QtyKeluar }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->tanggal_keluar)->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada riwayat pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- End Riwayat Pengeluaran Stok --}}
    </div>
    {{-- Pastikan ini tetap ada jika diperlukan oleh Bootstrap atau komponen lain --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const batchDeleteBtn = document.getElementById('batchDeleteBtn');
            const selectedCountSpan = document.getElementById('selectedCount');
            const actionButtons = document.querySelectorAll('.btn-info, .btn-warning'); // Detail and Edit buttons

            // Function to toggle action buttons based on selection state
            function toggleActionButtons() {
                const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
                const isAnySelected = checkedCount > 0;
                const notification = document.getElementById('bulkSelectionNotification');
                
                actionButtons.forEach(btn => {
                    if (isAnySelected) {
                        // Disable Detail and Edit buttons when items are selected
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
                        form.action = '{{ route("batch.delete.items") }}';
                        
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
                            input.name = 'item_ids[]';
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