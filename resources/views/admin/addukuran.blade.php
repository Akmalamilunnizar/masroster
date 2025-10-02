@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Tambah Ukuran
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
 <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span>Tambah Ukuran</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Tambah Ukuran</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('storeukuran') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama Ukuran</label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" name="nama" placeholder="Masukkan nama ukuran" 
                               value="{{ old('nama') }}" required />
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="panjang">Panjang</label>
                        <input type="number" class="form-control @error('panjang') is-invalid @enderror" 
                               id="panjang" name="panjang" placeholder="Masukkan panjang" 
                               value="{{ old('panjang') }}" required />
                        @error('panjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="lebar">Lebar</label>
                        <input type="number" class="form-control @error('lebar') is-invalid @enderror" 
                               id="lebar" name="lebar" placeholder="Masukkan lebar" 
                               value="{{ old('lebar') }}" required />
                        @error('lebar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                

                

                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bx bx-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Not necessary because no satuan -->
<!-- @push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih satuan",
            allowClear: true,
            width: '100%'
        });
    });
</script> -->
@endpush
@endsection
