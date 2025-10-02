@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Tambah Data Roster
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span>Tambah Data Roster</h4>
    <div class="col-xxl">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold fs-4">Tambah Data Roster</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('store-item') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="number" class="form-control" id="IdBarang" name="IdBarang" placeholder="Scan Id Roster" hidden />
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Username Admin</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" name="username" value="{{ $username }}" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Id Masuk</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="IdMasuk" name="IdMasuk" value="{{ $newIdMasuk }}" readonly />
                        </div>
                    </div>
                    <!-- Ukuran & Harga per Ukuran -->
                    <div class="mb-3">
                        <label class="form-label">Ukuran & Harga per Ukuran</label>
                        <div id="ukuran-harga-list">
                            <div class="row mb-2 ukuran-harga-item">
                                <div class="col-md-6">
                                    <select name="sizes[]" class="form-select" required>
                                        <option value="">Pilih Ukuran</option>
                                        @foreach($sizeList as $size)
                                        <option value="{{ $size->id_ukuran }}">
                                            {{ $size->nama }} ({{ $size->panjang }} x {{ $size->lebar }} Cm)
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="harga_per_size[]" class="form-control" placeholder="Harga" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger" style="border-radius: 8px;">Hapus</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-ukuran-harga" class="btn btn-outline-secondary btn-sm mt-2">+ Tambah Ukuran</button>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Kuantitas Masuk</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="QtyMasuk" name="QtyMasuk" placeholder="50" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Harga Satuan</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="HargaSatuan" name="HargaSatuan" 
                                       placeholder="Masukkan harga satuan (contoh: 55,000)" 
                                       oninput="formatNumber(this)" onblur="validateNumber(this)" required>
                                <input type="hidden" name="HargaSatuan_raw" id="HargaSatuan_raw">
                            </div>
                            <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Sub Total</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="SubTotal" name="SubTotal" placeholder="1100000" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Roster</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="NamaBarang" name="NamaBarang" placeholder="Mukura" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Motif Roster</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="IdJenisBarang" name="IdJenisBarang" aria-label="Default select example">
                                <option selected>Pilih Motif Roster</option>
                                @foreach ($typeid as $type)
                                <option value="{{ $type->IdJenisBarang }}">{{ $type->JenisBarang }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis Roster</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="IdJenisBarang" name="IdJenisBarang" aria-label="Default select example">
                                <option selected>Pilih Jenis Roster</option>
                                @foreach ($typeid as $type)
                                <option value="{{ $type->IdJenisBarang }}">{{ $type->JenisBarang }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    {{-- <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Upload Gambar</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="file" id="img" name="img" />
                        </div>
                    </div> --}}
                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-outline-primary">
                                Tambah Barang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // Dynamic add/remove ukuran-harga rows
    document.getElementById('add-ukuran-harga').onclick = function() {
        let list = document.getElementById('ukuran-harga-list');
        let item = list.querySelector('.ukuran-harga-item').cloneNode(true);
        item.querySelector('select').value = '';
        item.querySelector('input').value = '';
        list.appendChild(item);
    };
    document.getElementById('ukuran-harga-list').onclick = function(e) {
        if (e.target.classList.contains('remove-ukuran-harga')) {
            let items = document.querySelectorAll('.ukuran-harga-item');
            if (items.length > 1) e.target.closest('.ukuran-harga-item').remove();
        }
    };
</script>

<script>
function formatNumber(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/[^\d]/g, '');
    
    // Store raw value in hidden field first
    const rawFieldId = input.id + '_raw';
    document.getElementById(rawFieldId).value = value;
    
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
        input.setCustomValidity('Field ini harus diisi');
    } else if (parseInt(value) < 0) {
        input.setCustomValidity('Nilai tidak boleh negatif');
    } else {
        input.setCustomValidity('');
    }
}

// Update form submission to use raw values
document.querySelector('form').addEventListener('submit', function(e) {
    const hargaSatuanInput = document.getElementById('HargaSatuan');
    const hargaSatuanRaw = document.getElementById('HargaSatuan_raw');
    
    // Set the raw value to the main input before submission
    if (hargaSatuanRaw.value && hargaSatuanRaw.value.trim() !== '') {
        hargaSatuanInput.value = hargaSatuanRaw.value;
    } else {
        // If no raw value, try to extract from formatted value
        const formattedValue = hargaSatuanInput.value.replace(/[^\d]/g, '');
        if (formattedValue) {
            hargaSatuanInput.value = formattedValue;
        }
    }
});
</script>

@endpush
<script src="{{ asset('js/script.js') }}"></script>




@endsection