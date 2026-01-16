@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Edit Barang
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Edit Barang</h4>
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                   <h5 class="mb-0 fw-bold fs-4">Edit Barang</h5>
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
                    <form action="{{ route('updateitem') }}" method="POST">
                        @csrf
                        <input type="hidden" value="{{ $iteminfo->IdBarang }}" name="IdBarang">

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="NamaBarang">Nama Barang</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="NamaBarang" name="NamaBarang"
                                    value="{{ $iteminfo->NamaBarang }}" placeholder="Contoh: Banner" required />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="IdJenisBarang">Pilih Jenis</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="IdJenisBarang" name="IdJenisBarang" required>
                                    <option value="{{ $parent_title->IdJenisBarang ?? '0' }}" selected>
                                        {{ $parent_title->JenisBarang ?? 'ROOT' }}</option>
                                    @foreach ($typeid as $type)
                                        <option value="{{ $type->IdJenisBarang }}">{{ $type->JenisBarang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Jumlah Stok </label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" value="{{ $iteminfo->stock }}" readonly />
                            </div>
                        </div> --}}                        

                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-outline-primary">Update Barang</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
