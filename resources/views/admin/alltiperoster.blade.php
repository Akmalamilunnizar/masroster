@extends('admin.layouts.template')

@section('page_title')
    CIME | Daftar Tipe Roster
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Tipe Roster /</span> Daftar Tipe Roster
    </h4>

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Daftar Tipe Roster
            </h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm disabled-bulk" id="edit-selected" style="display: none;">
                    <i class="fas fa-edit me-1"></i>Edit Terpilih (<span id="selected-count">0</span>)
                </button>
                <button type="button" class="btn btn-info btn-sm disabled-bulk" id="detail-selected" style="display: none;">
                    <i class="fas fa-eye me-1"></i>Detail Terpilih (<span id="selected-count-2">0</span>)
                </button>
                <button type="button" class="btn btn-danger btn-sm disabled-bulk" id="delete-selected" style="display: none;">
                    <i class="fas fa-trash me-1"></i>Hapus Terpilih (<span id="selected-count-3">0</span>)
                </button>
                <a href="{{ route('addtiperoster') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Tipe Roster
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
                                <div class="btn-group" role="group">
                                    <a href="{{ route('edittiperoster', $tipe->IdTipe) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $tipe->IdTipe }})">
                                        <i class="fas fa-trash"></i>
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
    const detailBtn = document.getElementById('detail-selected');
    const deleteBtn = document.getElementById('delete-selected');
    
    // Update count displays
    document.getElementById('selected-count').textContent = count;
    document.getElementById('selected-count-2').textContent = count;
    document.getElementById('selected-count-3').textContent = count;
    
    // Show/hide buttons based on selection
    if (count > 0) {
        editBtn.style.display = 'inline-block';
        detailBtn.style.display = 'inline-block';
        deleteBtn.style.display = 'inline-block';
        editBtn.classList.remove('disabled-bulk');
        detailBtn.classList.remove('disabled-bulk');
        deleteBtn.classList.remove('disabled-bulk');
    } else {
        editBtn.style.display = 'none';
        detailBtn.style.display = 'none';
        deleteBtn.style.display = 'none';
        editBtn.classList.add('disabled-bulk');
        detailBtn.classList.add('disabled-bulk');
        deleteBtn.classList.add('disabled-bulk');
    }
}

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
