@extends('admin.layouts.template')

@section('page_title')
    CIME | Edit Detail Harga
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman /</span> Edit Detail Harga</h4>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Edit Harga Roster per Pembeli
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

            <form action="{{ route('detailharga.update', [$detailHarga->id_roster, $detailHarga->id_user, $detailHarga->id_ukuran]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="id_roster" class="form-label">Roster</label>
                        <input type="text" class="form-control" value="{{ $detailHarga->roster->NamaRoster ?? $detailHarga->id_roster }}" readonly>
                        <small class="text-muted">Tidak dapat diubah</small>
                    </div>
                    <div class="col-md-4">
                        <label for="id_user" class="form-label">Pembeli</label>
                        <input type="text" class="form-control" value="{{ $detailHarga->user->f_name ?? $detailHarga->id_user }}" readonly>
                        <small class="text-muted">Tidak dapat diubah</small>
                    </div>
                    <div class="col-md-4">
                        <label for="id_ukuran" class="form-label">Ukuran</label>
                        <input type="text" class="form-control" value="{{ $detailHarga->size ? $detailHarga->size->nama . ' (' . $detailHarga->size->panjang . '×' . $detailHarga->size->lebar . ' cm)' : $detailHarga->id_ukuran }}" readonly>
                        <small class="text-muted">Tidak dapat diubah</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga" id="harga" class="form-control" 
                                   value="{{ number_format($detailHarga->harga, 0, ',', '.') }}" 
                                   placeholder="Masukkan harga (contoh: 63,000)" required 
                                   oninput="formatNumber(this)" onblur="validateNumber(this)">
                        </div>
                        <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('detailharga.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Update Harga</button>
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
