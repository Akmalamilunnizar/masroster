@extends('admin.layouts.template')
@section('page_title')
Daftar Motif Roster
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Daftar Motif Roster</h4>

    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('addmotif') }}" class="btn btn-outline-primary" style="border-radius:8px;">+ Tambah Motif</a>
        <button id="batchDeleteBtn" class="btn btn-danger" style="display:none; border-radius:8px;">
            <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
        </button>
    </div>

    <div class="card">
        <h5 class="card-header fw-bold">Motif Yang Tersedia</h5>
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

    batchBtn.addEventListener('click', function(){
        const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        if (ids.length === 0) return;
        if (!confirm('Yakin menghapus ' + ids.length + ' motif terpilih?')) return;

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
    });
});
</script>
@endsection


