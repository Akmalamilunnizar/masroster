@extends('admin.layouts.template')

@section('page_title')
CIME | Daftar Transaksi
@endsection

@section('content')
<style>
    /* Enhanced styling for better visual hierarchy and alignment */
    .filter-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-group label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    .filter-group select,
    .filter-group input {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .table-enhanced {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .table-enhanced thead th {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border: none;
        padding: 0.75rem 0.5rem !important;
        text-align: center;
        vertical-align: middle;
        font-size: 0.875rem !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
    }

    .table-enhanced tbody td {
        padding: 0.75rem 0.5rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.875rem;
        color: #212529 !important;
        background-color: #ffffff;
    }

    .table-enhanced tbody tr {
        transition: background-color 0.2s ease;
    }

    .table-enhanced tbody tr:hover {
        background-color: #f0f4f8 !important;
    }

    .table-enhanced tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table-enhanced tbody tr:nth-child(even):hover {
        background-color: #e9ecef !important;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600 !important;
        font-size: 0.75rem;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    /* Ensure badge text is always white for better contrast */
    .status-badge.bg-warning {
        color: #000000 !important;
        font-weight: 700 !important;
        background-color: #ffc107 !important;
    }
    
    .status-badge.bg-success {
        background-color: #198754 !important;
        color: #ffffff !important;
    }
    
    .status-badge.bg-danger {
        background-color: #dc3545 !important;
        color: #ffffff !important;
    }
    
    .status-badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #ffffff !important;
    }
    
    .status-badge.bg-primary {
        background-color: #0d6efd !important;
        color: #ffffff !important;
    }
    
    .status-badge.bg-secondary {
        background-color: #6c757d !important;
        color: #ffffff !important;
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

    .transaction-link {
        color: #0d6efd !important;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .transaction-link:hover {
        color: #0056b3 !important;
        text-decoration: underline;
    }

    .amount-cell {
        font-weight: 700 !important;
        color: #198754 !important;
    }
    
    .table-enhanced tbody td strong {
        color: #212529 !important;
        font-weight: 600;
    }

    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 0.5rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid #dee2e6;
    }

    .page-title {
        color: #495057;
        font-weight: 700;
        margin: 0;
        font-size: 1.25rem;
    }

    .page-subtitle {
        color: #6c757d;
        font-size: 0.875rem;
        margin: 0.25rem 0 0 0;
    }

    /* Reduce spacing between cards */
    .filter-card {
        margin-bottom: 0.5rem !important;
    }

    .table-card {
        margin-bottom: 0.5rem !important;
    }

    /* Reduce padding in cards */
    .card-body {
        padding: 0.5rem !important;
    }

    .card-header {
        padding: 0.5rem 0.75rem !important;
    }

    /* Reduce spacing in filter form */
    .filter-form {
        gap: 0.5rem !important;
    }

    .filter-group {
        gap: 0.25rem !important;
    }

    /* Reduce spacing between alerts and content */
    .alert {
        margin-bottom: 0.5rem !important;
    }

    /* Fix table responsiveness */
    .table-responsive {
        overflow-x: auto;
        border-radius: 6px;
        margin: 0;
        padding: 0;
    }

    /* Ensure table fits properly */
    .table-enhanced {
        min-width: auto;
        width: 100%;
        margin: 0;
        table-layout: auto;
    }

    /* Flexible table cell widths - allow content to determine width */
    .table-enhanced th,
    .table-enhanced td {
        min-width: auto;
        width: auto;
        max-width: none;
    }

    /* Set minimum widths for better layout */
    .table-enhanced th:nth-child(1),
    .table-enhanced td:nth-child(1) {
        min-width: 100px;
    }

    /* ID Transaksi */
    .table-enhanced th:nth-child(2),
    .table-enhanced td:nth-child(2) {
        min-width: 120px;
    }

    /* Tanggal */
    .table-enhanced th:nth-child(3),
    .table-enhanced td:nth-child(3) {
        min-width: 140px;
    }

    /* Nama Customer */
    .table-enhanced th:nth-child(4),
    .table-enhanced td:nth-child(4) {
        min-width: 120px;
    }

    /* Total Grand */
    .table-enhanced th:nth-child(5),
    .table-enhanced td:nth-child(5) {
        min-width: 120px;
    }

    /* Jumlah Dibayar */
    .table-enhanced th:nth-child(6),
    .table-enhanced td:nth-child(6) {
        min-width: 100px;
    }

    /* Status Pesanan */
    .table-enhanced th:nth-child(7),
    .table-enhanced td:nth-child(7) {
        min-width: 100px;
    }

    /* Jenis Pengiriman */
    .table-enhanced th:nth-child(8),
    .table-enhanced td:nth-child(8) {
        min-width: 100px;
    }

    /* Biaya Ongkir */
    .table-enhanced th:nth-child(9),
    .table-enhanced td:nth-child(9) {
        min-width: 100px;
    }

    /* Status Pembayaran */
    .table-enhanced th:nth-child(10),
    .table-enhanced td:nth-child(10) {
        min-width: 80px;
    }

    /* Aksi */

    /* Fix text overflow in table cells - REMOVE TRUNCATION */
    .table-enhanced td {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: unset !important;
        max-width: none !important;
        word-wrap: break-word;
    }

    /* Ensure proper text display for all columns */
    .table-enhanced td.text-center {
        text-align: center !important;
    }

    /* Allow monetary values to display fully */
    .table-enhanced td.amount-cell {
        white-space: nowrap;
        overflow: visible;
        text-overflow: unset;
    }

    /* Reduce overall spacing */
    .mb-4 {
        margin-bottom: 0.75rem !important;
    }

    .mb-3 {
        margin-bottom: 0.5rem !important;
    }

    .py-3 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    /* Fix card header spacing */
    .card-header h5 {
        margin: 0 !important;
        font-size: 1rem !important;
    }

    /* Fix filter card spacing */
    .filter-card .card-body {
        padding: 0.5rem !important;
    }

    /* Fix table card spacing */
    .card .table-responsive {
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Enhanced Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title">Daftar Transaksi</h4>
                <p class="page-subtitle">Kelola dan monitor semua transaksi pelanggan</p>
            </div>
            <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-enhanced">
                <i class="fas fa-plus me-2"></i>Tambah Transaksi Manual
            </a>
        </div>
    </div>

    {{-- Export Buttons --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-download me-2 text-primary"></i>Export Dataset Forecasting
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('export.lstm') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-file-csv me-1"></i>Export LSTM CSV
                    </a>
                    <a href="{{ route('export.prophet') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv me-1"></i>Export Prophet CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Pesan sukses atau error --}}
    {{-- Session messages are automatically displayed via CustomModal --}}

    <!-- Enhanced Filter Card -->
    <div class="card filter-card mb-4">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Transaksi
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('alltransaksi') }}" class="filter-form">
                {{-- Filter Status --}}
                <div class="filter-group">
                    <label for="status_pesanan">Status Pesanan</label>
                    <select name="status_pesanan" id="status_pesanan" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Pending" {{ request('status_pesanan') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="MENUNGGU KONFIRMASI" {{ request('status_pesanan') == 'MENUNGGU KONFIRMASI' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Diterima" {{ request('status_pesanan') == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="Ditolak" {{ request('status_pesanan') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                {{-- Filter Bulan --}}
                <div class="filter-group">
                    <label for="bulan">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach ([
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ] as $key => $value)
                        <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tahun --}}
                <div class="filter-group">
                    <label for="tahun">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @for ($year = 2020; $year <= date('Y'); $year++)
                            <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                    </select>
                </div>

                {{-- Search Input --}}
                <div class="filter-group">
                    <label for="search">Cari Customer</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="ID atau Nama Customer" value="{{ request('search') }}">
                </div>

                {{-- Action Buttons --}}
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary btn-enhanced">
                        <i class='bx bx-filter-outline me-1'></i> Filter
                    </button>

                    <a href="{{ route('alltransaksi.exportpdf', [
                        'bulan' => request('bulan'),
                        'tahun' => request('tahun'),
                        'status_pesanan' => request('status_pesanan'),
                        'search' => request('search')
                    ]) }}"
                        class="btn btn-danger btn-enhanced"
                        target="_blank">
                        <i class='bx bxs-printer me-1'></i> Print Laporan
                    </a>

                    <button type="button" class="btn btn-success btn-enhanced" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
                        <i class='bx bxs-file-export me-1'></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Transaction Table -->
    <div class="card">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Daftar Transaksi
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-enhanced">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal Transaksi</th>
                        <th>Nama Customer</th>
                        <th>Total Grand</th>
                        <th>Jumlah Dibayar</th>
                        <th>Status Pesanan</th>
                        <th>Jenis Pengiriman</th>
                        <th>Biaya Ongkir</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi as $item)
                    <tr>
                        <td>
                            <a href="{{ route('vieworder', $item->IdTransaksi) }}" class="transaction-link">
                                {{ $item->IdTransaksi }}
                            </a>
                        </td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($item->tglTransaksi)->format('d-m-Y H:i') }}
                        </td>
                        <td class="text-center">
                            <strong>{{ $item->customer->f_name ?? 'N/A' }}</strong>
                        </td>
                        <td class="text-center amount-cell">
                            Rp {{ number_format($item->GrandTotal, 0, ',', '.') }}
                        </td>
                        <td class="text-center amount-cell">
                            Rp {{ number_format($item->Bayar, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if ($item->StatusPesanan == 'Pending' || strtoupper($item->StatusPesanan) == 'MENUNGGU KONFIRMASI')
                            <span class="badge bg-warning status-badge">{{ $item->StatusPesanan }}</span>
                            @elseif ($item->StatusPesanan == 'Diterima')
                            <span class="badge bg-success status-badge">{{ $item->StatusPesanan }}</span>
                            @elseif ($item->StatusPesanan == 'Ditolak')
                            <span class="badge bg-danger status-badge">{{ $item->StatusPesanan }}</span>
                            @else
                            <span class="badge bg-secondary status-badge">{{ $item->StatusPesanan }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info status-badge">{{ $item->shipping_type ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->ongkir > 0)
                            <span class="badge bg-secondary status-badge">Rp {{ number_format($item->ongkir, 0, ',', '.') }}</span>
                            @else
                            <span class="badge bg-success status-badge">Free</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary status-badge">{{ $item->StatusPembayaran ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="action-buttons-cell">
                                {{-- KONDISIONAL UNTUK TOMBOL AKSI --}}
                                @if (strtoupper($item->StatusPesanan) == 'MENUNGGU KONFIRMASI')
                                <form id="terimaForm{{ $item->IdTransaksi }}" action="{{ route('terimaOrderan', $item->IdTransaksi) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="button" class="btn btn-success btn-action"
                                        onclick="confirmAction('terima', 'terimaForm{{ $item->IdTransaksi }}');" title="Terima Order">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                <form id="tolakForm{{ $item->IdTransaksi }}" action="{{ route('tolakOrderan', $item->IdTransaksi) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="button" class="btn btn-danger btn-action"
                                        onclick="confirmAction('tolak', 'tolakForm{{ $item->IdTransaksi }}');" title="Tolak Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Manual CRUD Actions --}}
                                <a href="{{ route('transaksi.edit', $item->IdTransaksi) }}" class="btn btn-warning btn-action">
                                    <i class="bx bxs-edit me-1"></i> Edit Transaksi
                                </a>
                                <form id="deleteForm{{ $item->IdTransaksi }}" action="{{ route('transaksi.destroy', $item->IdTransaksi) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-action"
                                        onclick="confirmDelete('{{ $item->IdTransaksi }}')">
                                        <i class="bx bxs-trash me-1"></i>Hapus Transaksi
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmAction(type, formId) { // Mengubah parameter menjadi formId
        let title, text, confirmButtonText, iconType;

        if (type === 'terima') {
            title = 'Konfirmasi Penerimaan Orderan';
            text = 'Apakah Anda yakin akan menerima orderan ini? Status pesanan akan berubah menjadi "Diterima".';
            confirmButtonText = 'Ya, Terima!';
            iconType = 'question';
        } else if (type === 'tolak') {
            title = 'Konfirmasi Penolakan Orderan';
            text = 'Apakah Anda yakin akan menolak orderan ini? Status pesanan akan berubah menjadi "Ditolak".';
            confirmButtonText = 'Ya, Tolak!';
            iconType = 'warning';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: iconType,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Tidak',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form yang sesuai
                document.getElementById(formId).submit();
            }
        });
    }

    function cancelEditInvoice(id) {
        const displaySpan = document.getElementById(`invoice-display-${id}`);
        const editForm = document.getElementById(`invoice-edit-${id}`);

        displaySpan.style.display = 'block';
        editForm.style.display = 'none';
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus Transaksi',
            text: 'Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.',
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
    }

    // Export Excel with custom biaya operasional
    function exportExcelWithOperasional() {
        const biayaOperasional = document.getElementById('biaya_operasional').value.replace(/[^\d]/g, '');
        const returBarang = document.getElementById('retur_barang').value.replace(/[^\d]/g, '');

        let url = "{{ route('alltransaksi.exportexcel') }}?";
        url += "bulan={{ request('bulan') }}&";
        url += "tahun={{ request('tahun') }}&";
        url += "status_pesanan={{ request('status_pesanan') }}&";
        url += "search={{ request('search') }}&";
        url += "biaya_operasional=" + biayaOperasional + "&";
        url += "retur=" + returBarang;

        window.open(url, '_blank');
        $('#exportExcelModal').modal('hide');
    }
</script>
@endpush

<!-- Export Excel Modal -->
<div class="modal fade" id="exportExcelModal" tabindex="-1" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportExcelModalLabel">Export Excel dengan Biaya Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="biaya_operasional" class="form-label">Biaya Operasional (Rp)</label>
                    <input type="text" class="form-control" id="biaya_operasional"
                        placeholder="Masukkan biaya operasional (contoh: 1,500,000)"
                        value="1,500,000"
                        oninput="formatNumber(this)">
                    <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                </div>
                <div class="mb-3">
                    <label for="retur_barang" class="form-label">Retur Barang (Rp)</label>
                    <input type="text" class="form-control" id="retur_barang"
                        placeholder="Masukkan retur barang (contoh: 0)"
                        value="0"
                        oninput="formatNumber(this)">
                    <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="exportExcelWithOperasional()">
                    <i class='bx bxs-file-export me-2'></i>Export Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function formatNumber(input) {
        // Remove all non-digit characters
        let value = input.value.replace(/[^\d]/g, '');

        // Format with commas for display
        if (value !== '') {
            value = parseInt(value).toLocaleString('id-ID');
        }

        // Update display
        input.value = value;
    }
</script>
@endsection
