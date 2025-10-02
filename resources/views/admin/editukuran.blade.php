@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Edit Ukuran
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
 <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span>Edit Ukuran</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Edit Ukuran</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('updateukuran', $size->id_ukuran) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama Ukuran</label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" name="nama" placeholder="Masukkan nama ukuran" 
                               value="{{ old('nama', $size->nama) }}" required />
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
                               value="{{ old('panjang', $size->panjang) }}" required />
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
                               value="{{ old('lebar', $size->lebar) }}" required />
                        @error('lebar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                

                

                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bx bx-save"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih Ukuran",
            allowClear: true,
            width: '100%',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            templateResult: function (data) {
                var $result = $("<span></span>");
                $result.text(data.text);
                if (data.newOption) {
                    $result.append(" <em>(Tambah ukuran baru)</em>");
                }
                return $result;
            }
        });
    });
</script>
@endpush
@endsection
