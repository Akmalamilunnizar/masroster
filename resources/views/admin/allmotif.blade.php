@extends('admin.layouts.template')
@section('page_title')
Daftar Motif Roster
@endsection

@section('search')
<div class="col-md-4">
    <div class="position-relative search-box-wrapper">
        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" style="z-index: 5;"></i>
        <input type="text" id="motifSearchInput" class="form-control border-0 bg-light ps-5 rounded-3" 
            style="height: 42px;" placeholder="Cari nama motif...">
    </div>
</div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Daftar Motif Roster</h4>

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Advanced Filter Section -->
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filter Tipe Roster</label>
                    <select id="filterTipe" class="form-select border-0 bg-light">
                        <option value="">Semua Tipe Roster</option>
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

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('addmotif') }}" class="btn btn-outline-primary" style="border-radius:8px;">+ Tambah Motif</a>
        <button id="batchDeleteBtn" class="btn btn-danger" style="display:none; border-radius:8px;">
            <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
        </button>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #696cff 0%, #4338ca 100%);">
            <h5 class="mb-0 text-white fw-bold"><i class="fas fa-th-large me-2"></i>Data Motif Roster</h5>
            <span class="badge bg-white text-primary rounded-pill px-3" id="totalItemsDisplay">0 Item</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-striped">
                <thead class="table-primary">
                    <tr>
                        <th class="fw-bold text-center" style="width:50px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th class="fw-bold text-center">ID</th>
                        <th class="fw-bold text-center">Nama Motif</th>
                        <th class="fw-bold text-center">Terhubung dengan Tipe Roster</th>
                        <th class="fw-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($motifs as $m)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input motif-checkbox" value="{{ $m->IdMotif }}">
                            </td>
                            <td class="text-center">{{ $m->IdMotif }}</td>
                            <td class="text-center">{{ $m->nama_motif }}</td>
                            <td class="text-center">
                                @if($m->tipeRosters->count() > 0)
                                    @foreach($m->tipeRosters as $tipe)
                                        <span class="badge bg-info me-1">{{ $tipe->namaTipe }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Tidak terhubung</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('editmotif', $m->IdMotif) }}" class="btn btn-warning">Edit</a>
                                <form action="{{ route('deletemotif', $m->IdMotif) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" onclick="return confirm('Hapus motif ini?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada motif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.motif-checkbox');
    const batchBtn = document.getElementById('batchDeleteBtn');
    const countSpan = document.getElementById('selectedCount');
    const filterTipe = document.getElementById('filterTipe');
    const resetBtn = document.getElementById('resetFilters');
    const tableRows = document.querySelectorAll('tbody tr:not(.empty-row)');
    const totalItemsDisplay = document.getElementById('totalItemsDisplay');

    function updateItemCount() {
        const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none').length;
        totalItemsDisplay.textContent = visibleRows + ' Item';
    }
    updateItemCount();

    // Populate Tipe Filter
    const tipeSet = new Set();
    tableRows.forEach(row => {
        const badges = row.querySelectorAll('.badge.bg-info');
        badges.forEach(b => tipeSet.add(b.textContent.trim()));
    });
    tipeSet.forEach(val => filterTipe.add(new Option(val, val)));

    function applyFilters() {
        const selTipe = filterTipe.value;
        const searchTerm = searchInput.value.toLowerCase();

        tableRows.forEach(row => {
            const name = row.cells[2].textContent.toLowerCase();
            const badges = Array.from(row.querySelectorAll('.badge.bg-info')).map(b => b.textContent.trim());
            
            const matchesSearch = name.includes(searchTerm);
            const matchesTipe = !selTipe || badges.includes(selTipe);

            row.style.display = (matchesSearch && matchesTipe) ? '' : 'none';
        });
        updateItemCount();
    }

    if (filterTipe) filterTipe.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            filterTipe.value = '';
            searchInput.value = '';
            applyFilters();
        });
    }

    function updateUI() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked).length;
        countSpan.textContent = selected;
        batchBtn.style.display = selected > 0 ? 'inline-block' : 'none';
    }

    if (selectAll) {
        selectAll.addEventListener('change', function(){
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateUI();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

    // Search functionality
    const searchInput = document.getElementById('motifSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', applyFilters);
    }

    batchBtn.addEventListener('click', function(){
        const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        if (ids.length === 0) return;
        
        Swal.fire({
            title: 'Hapus Terpilih?',
            text: `Anda akan menghapus ${ids.length} motif. Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('batch.delete.motif') }}';
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
                form.appendChild(method);
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'motif_ids[]'; input.value = id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection


