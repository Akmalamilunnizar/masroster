@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Daftar Satuan
@endsection
@section('search')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            {{-- <form method="GET" action={{ route('searchitem') }}> --}}
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..."
                style="600px" />
            </form>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Daftar Satuan</h4>
        <a href="{{ route('addsatuan') }}" class="btn btn-outline-primary mb-3">
            + Tambah Satuan
        </a>
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        <div class="card">
            <h5 class="card-header fw-bold">Satuan Yang Tersedia</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                     <thead class="table-primary">
                        <tr>
                            <th class="fw-bold" style="text-align: center;">Id</th>
                            <th class="fw-bold" style="text-align: center;">Nama Satuan</th>
                            <th class="fw-bold" style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">

                        @foreach ($satuan as $item)
                            <tr>
                                <td style="text-align: center;">{{ $item->IdSatuan }}</td>
                                <td style="text-align: center;">{{ $item->Satuan }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ route('editsatuan', $item->IdSatuan) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('deletesatuan', $item->IdSatuan) }}" class="btn btn-danger" onclick="return confirm('Yakin ingin hapus data ini?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
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
