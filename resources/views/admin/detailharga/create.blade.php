@extends('admin.layouts.template')

@section('page_title')
    CIME | Tambah Detail Harga
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman /</span> Tambah Detail Harga</h4>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus me-2"></i>Input Harga Roster per Supplier
            </h5>
        </div>
        <div class="card-body">
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

            <form action="{{ route('detailharga.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="id_roster" class="form-label">Pilih Roster</label>
                        <select name="id_roster" id="id_roster" class="form-select" required>
                            <option value="">Pilih Roster</option>
                            @foreach($rosters as $roster)
                                <option value="{{ $roster->IdRoster }}">
                                    {{ $roster->NamaRoster }} - {{ $roster->jenisRoster ? $roster->jenisRoster->JenisBarang : '-' }}
                                    @if($roster->motif)
                                        - {{ $roster->motif->nama_motif }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="id_user" class="form-label">Pilih Pembeli</label>
                        <select name="id_user" id="id_user" class="form-select" required>
                            <option value="">Pilih Pembeli</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->f_name }} ({{ $user->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="id_ukuran" class="form-label">Pilih Ukuran</label>
                        <select name="id_ukuran" id="id_ukuran" class="form-select" required>
                            <option value="">Pilih Ukuran</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size->id_ukuran }}">{{ $size->nama }} ({{ $size->panjang }}×{{ $size->lebar }} cm)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga" id="harga" class="form-control" 
                                   placeholder="Masukkan harga (contoh: 63,000)" required 
                                   oninput="formatNumber(this)" onblur="validateNumber(this)">
                        </div>
                        <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('detailharga.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Harga</button>
                </div>
            </form>
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

function validateNumber(input) {
    let value = input.value.replace(/[^\d]/g, '');
    
    if (value === '') {
        input.setCustomValidity('Harga harus diisi');
    } else if (parseInt(value) < 0) {
        input.setCustomValidity('Harga tidak boleh negatif');
    } else {
        input.setCustomValidity('');
    }
}

// Simple form submission - let the server handle validation
document.querySelector('form').addEventListener('submit', function(e) {
    console.log('Form submission started...');
    
    // Convert formatted values to raw numbers before submission
    const hargaInput = document.getElementById('harga');
    
    // Remove all non-digit characters from harga
    const hargaValue = hargaInput.value.replace(/[^\d]/g, '');
    hargaInput.value = hargaValue;
    
    console.log('Form validation passed, submitting...');
    console.log('Harga (raw):', hargaValue);
});
</script>

@endsection
