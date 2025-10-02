@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Edit Supplier
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman /</span> Edit Supplier</h4>
    <div class="col-xxl">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold fs-4">Edit Data Supplier</h5>
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

                <form action="{{ route('updatesupplier', $supplier->IdSupplier) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdSupplier">ID Supplier</label>
                        <div class="col-sm-10">
                            <input type="text" id="IdSupplier" name="IdSupplier" class="form-control"
                                value="{{ $supplier->IdSupplier }}" readonly
                                style="background-color: #e9ecef; cursor: default;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="NamaSupplier">Nama Supplier</label>
                        <div class="col-sm-10"> 
                            <input type="text" id="NamaSupplier" name="NamaSupplier" class="form-control"
                                   value="{{ $supplier->NamaSupplier }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="NoTelp">No. Telepon</label>
                        <div class="col-sm-10">
                            <input type="text" id="NoTelp" name="NoTelp" class="form-control"
                                   value="{{ $supplier->NoTelp }}" required>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-outline-primary">Update Supplier</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
