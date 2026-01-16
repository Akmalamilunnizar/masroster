@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Daftar Customer
@endsection

@section('search')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama customer..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..."/>
        </div>
    </div>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman</span> Daftar Customer</h4>
    @if (session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
<div class="card">
    <h5 class="card-header fw-bold">Customer yang Terdaftar</h5>
    <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                     <thead class="table-primary">
                <tr>
                  <th style="text-align: center; font-weight: bold;">Id</th>
                <th style="text-align: center; font-weight: bold;">Nama Customer</th>
                <th style="text-align: center; font-weight: bold;">Nomor Telepon</th>
                <th style="text-align: center; font-weight: bold;">Email</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">

                @foreach ($customer->where('user', 'User') as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->id }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('customerDetails', $item->id) }}" class="text-primary fw-bold">
                                {{ $item->f_name }}
                            </a>
                        </td>
                        <td style="text-align: center;">{{ $item->nomor_telepon }}</td>
                        <td style="text-align: center;">{{ $item->email }}</td>
                        <td style="text-align: center;">
                            {{ $item->is_default ? $item->defaultAddress->full_address : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Bootstrap Table with Header - Light -->
</div>
@endsection