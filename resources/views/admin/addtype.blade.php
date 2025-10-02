@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Jenis Barang
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Halaman /</span> Tambah Jenis Barang
        </h4>

        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold fs-4">Tambah Jenis Barang</h5>
                </div>

                <div class="card-body">
                    {{-- Error Alert --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form Tambah Satuan --}}
                    <form action="{{ route('store-type') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Nama Satuan --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="JenisBarang">Nama Jenis Barang</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="JenisBarang" name="JenisBarang"
                                    placeholder="Kertas" value="{{ old('JenisBarang') }}" />
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-outline-primary">
                                    Tambah Jenis Barang
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
