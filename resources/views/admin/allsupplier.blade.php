@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Daftar Supplier
@endsection
@section('search')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama supplier..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..."/>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-2 mb-3"><span class="text-muted fw-light">Data Supplier/</span> Daftar Supplier</h4>
        <a href="{{ route('addsupplier') }}" class="btn btn-outline-primary mb-3">
            + Tambah Supplier
        </a>
        @if (session()->has('message'))
            <div class="alert alert-success mb-2">
                {{ session()->get('message') }}
            </div>
        @endif
        <div class="card mt-3">
            <h5 class="card-header">Supplier Yang Terdaftar</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                     <thead class="table-primary">
                       <tr>
                            <th class="text-center fw-bold">Id Supplier</th>
                            <th class="text-center fw-bold">Nama Supplier</th>
                            <th class="text-center fw-bold">No Tlp</th>
                            <th class="text-center fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td class="text-center">{{ $supplier->IdSupplier }}</td>
                                <td class="text-center">{{ $supplier->NamaSupplier }}</td>
                                <td class="text-center">{{ $supplier->NoTelp }}</td>
                                <td class="text-center">
                                    <a href="{{ route('editsupplier', $supplier->IdSupplier) }}"  class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('deletesupplier', $supplier->IdSupplier) }}" method="POST" style="display:inline;" id="delete-form-{{ $supplier->IdSupplier }}">
                                        @csrf
                                        @method('DELETE')
                                        <a href="#" class="btn btn-danger" onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus supplier ini?')) document.getElementById('delete-form-{{ $supplier->IdSupplier }}').submit();">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
