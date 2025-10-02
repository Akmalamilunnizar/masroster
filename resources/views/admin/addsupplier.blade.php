@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Tambah Supplier
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman / </span>Tambah Data Supplier</h4>
    <div class="col-xxl">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold fs-4">Tambah Data Supplier</h5>
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

                <form action="{{ route('storesupplier') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdSupplier">ID Supplier</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="IdSupplier" name="IdSupplier" placeholder="Contoh: SUP001" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="NamaSupplier">Nama Supplier</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="NamaSupplier" name="NamaSupplier" placeholder="Contoh: PT Kertas Makmur" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="NoTelp">No Telepon</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="NoTelp" name="NoTelp" placeholder="Contoh: 08123456789" />
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-outline-primary">
                                Tambah Supplier
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
