@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Daftar Roster
@endsection
@section('search')
<div class="col-md-4">
    <div class="position-relative search-box-wrapper">
        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
        <input type="text" id="produkSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
            style="height: 42px;" placeholder="Cari motif atau tipe produk...">
    </div>
</div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-2 mb-3"><span class="text-muted fw-light">Data Roster /</span> Daftar Roster</h4>
    <a href="{{ route('addproduk') }}" class="btn btn-outline-primary mb-3">
        + Tambah Produk Roster
    </a>

    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Advanced Filter Section -->
    <div class="card shadow-sm mb-4 border-0 rounded-3">
        <div class="card-body p-3 rounded-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted">Filter Jenis</label>
                    <select id="filterJenis" class="form-select border-0 bg-light rounded-2">
                        <option value="">Semua Jenis</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted">Filter Tipe</label>
                    <select id="filterTipe" class="form-select border-0 bg-light rounded-2">
                        <option value="">Semua Tipe</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted">Filter Motif</label>
                    <select id="filterMotif" class="form-select border-0 bg-light rounded-2">
                        <option value="">Semua Motif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100 btn-sm rounded-2" style="height: 38px;">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);">
            <h5 class="card-title mb-0 text-white fw-bold">
                <i class="fas fa-box me-2"></i>Roster Terdaftar
            </h5>
            <span class="badge bg-white text-primary rounded-pill px-3 py-2" id="produkCountDisplay">0 Produk</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="fw-bold text-center align-middle" style="width: 80px; min-width: 80px;">ID</th>
                        <th class="fw-bold text-center align-middle" style="width: 120px; min-width: 120px;">Gambar</th>
                        <th class="fw-bold text-center align-middle">Jenis Roster</th>
                        <th class="fw-bold text-center align-middle" style="width: 150px;">Tipe Roster</th>
                        <th class="fw-bold text-center align-middle" style="width: 150px;">Motif</th>
                        <th class="fw-bold text-center align-middle" style="width: 120px;">Harga</th>
                        <th class="fw-bold text-center align-middle" style="width: 180px;">Ukuran</th>
                        <th class="fw-bold text-center align-middle" style="width: 100px;">Stok</th>
                        <th class="fw-bold text-center align-middle" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataProduk as $produk)
                    <tr class="align-middle">
                        <td class="text-center fw-semibold">{{ $produk->IdRoster }}</td>

                        <td class="text-center">
                            @if ($produk->Img)
                            <img src="{{ asset('storage/' . $produk->Img) }}" class="img-thumbnail" style="max-width: 60px; height: 60px; object-fit: cover;">
                            @else
                            <span class="text-muted small">No Image</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $produk->jenisRoster->JenisBarang ?? '-' }}</span>
                        </td>
                        <td class="text-center fw-medium">{{ $produk->tipeRoster->namaTipe ?? '-' }}</td>
                        <td class="text-center">
                            @if($produk->motif)
                            <span class="badge bg-info">{{ $produk->motif->nama_motif }}</span>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($produk->sizes->count())
                            @foreach ($produk->sizes as $size)
                            <span class="badge bg-success text-white mb-1 d-block">
                                Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                            </span>
                            @endforeach
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($produk->sizes->count())
                            @foreach ($produk->sizes as $size)
                            <span class="badge bg-info text-white mb-1 d-block">
                                {{ $size->nama }} ({{ $size->panjang }}×{{ $size->lebar }} Cm)
                            </span>
                            @endforeach
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($produk->stock > 0)
                                @if($produk->stock < 10)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $produk->stock }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>{{ $produk->stock }}
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>0
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.detail_allitems', $produk->IdRoster) }}" class="btn btn-info" style="border-radius: 6px; width: 80px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">
                                    <i class="fas fa-info-circle me-1"></i> Detail
                                </a>
                                <a href="{{ route('editproduk', $produk->IdRoster) }}" class="btn btn-sm btn-warning" style="border-radius: 6px; width: 80px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route('deleteproduk', $produk->IdRoster) }}" method="POST" style="display:inline;" id="delete-form-{{ $produk->IdRoster }}">
                                    @csrf
                                    @method('DELETE')
                                    <a href="#" class="btn btn-sm btn-danger" style="border-radius: 6px; width: 80px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;" onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus produk ini?')) document.getElementById('delete-form-{{ $produk->IdRoster }}').submit();">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
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
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('produkSearchInput');
        const filterJenis = document.getElementById('filterJenis');
        const filterTipe = document.getElementById('filterTipe');
        const filterMotif = document.getElementById('filterMotif');
        const resetBtn = document.getElementById('resetFilters');
        const tableRows = document.querySelectorAll('tbody tr');
        const countDisplay = document.getElementById('produkCountDisplay');

        function updateCount() {
            const visible = Array.from(tableRows).filter(r => r.style.display !== 'none').length;
            if(countDisplay) countDisplay.textContent = visible + ' Produk';
        }

        // Populate filters dynamically
        const jenisSet = new Set();
        const tipeSet = new Set();
        const motifSet = new Set();

        tableRows.forEach(row => {
            const jenis = row.cells[2]?.textContent.trim();
            const tipe = row.cells[3]?.textContent.trim();
            const motif = row.cells[4]?.textContent.trim();

            if (jenis && jenis !== '-') jenisSet.add(jenis);
            if (tipe && tipe !== '-') tipeSet.add(tipe);
            if (motif && motif !== '-') motifSet.add(motif);
        });

        jenisSet.forEach(val => filterJenis.add(new Option(val, val)));
        tipeSet.forEach(val => filterTipe.add(new Option(val, val)));
        motifSet.forEach(val => filterMotif.add(new Option(val, val)));

        function applyFilters() {
            const term = searchInput.value.toLowerCase();
            const selJenis = filterJenis.value;
            const selTipe = filterTipe.value;
            const selMotif = filterMotif.value;

            tableRows.forEach(row => {
                const rowText = Array.from(row.cells)
                    .slice(0, 8)
                    .map(cell => cell.textContent.trim())
                    .join(' ')
                    .toLowerCase();
                
                const jenis = row.cells[2]?.textContent.trim();
                const tipe = row.cells[3]?.textContent.trim();
                const motif = row.cells[4]?.textContent.trim();
                
                const matchesSearch = rowText.includes(term);
                const matchesJenis = !selJenis || jenis === selJenis;
                const matchesTipe = !selTipe || tipe === selTipe;
                const matchesMotif = !selMotif || motif === selMotif;

                row.style.display = (matchesSearch && matchesJenis && matchesTipe && matchesMotif) ? '' : 'none';
            });
            updateCount();
        }

        if (searchInput) searchInput.addEventListener('keyup', applyFilters);
        if (filterJenis) filterJenis.addEventListener('change', applyFilters);
        if (filterTipe) filterTipe.addEventListener('change', applyFilters);
        if (filterMotif) filterMotif.addEventListener('change', applyFilters);

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterJenis.value = '';
                filterTipe.value = '';
                filterMotif.value = '';
                applyFilters();
            });
        }
        updateCount();
    });
</script>