@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Edit Jenis Barang
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Edit Jenis Barang</h4>
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                     <h5 class="mb-0 fw-bold fs-4">Edit Jenis Barang</h5>
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
                    <form action="{{ route('updatetype') }}" method="POST">
                        @csrf
                        <input type="hidden" name="original_id" value="{{ $typeinfo->IdJenisBarang }}">
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Id Jenis Barang</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="IdJenisBarang" name="IdJenisBarang"
                                    value="{{ $typeinfo->IdJenisBarang }}" readonly/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Jenis Barang</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="JenisBarang" name="JenisBarang"
                                    value="{{ $typeinfo->JenisBarang }}" placeholder="Kertas" />
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-outline-primary">Update Jenis Barang</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
