@extends('admin.layouts.template')
@section('page_title')
Tambahkan Pengguna - Restorant
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Tambah Pengguna</h4>
    <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Tambah Pengguna Baru</h5>
            <small class="text-muted float-end">Input Informasi</small>
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
            <form action="{{route('storeusers')}}" method="POST">
            @csrf
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Pengguna</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="f_name" name="f_name" placeholder="" />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Email</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="email" name="email" placeholder="" />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Nomor Telepon</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="phone" name="phone" placeholder="" />
                </div>
              </div>

              {{-- <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal email terverifikasi</label>
                <div class="col-sm-10">
                    <input type="datetime-local">
                </div>
              </div> --}}

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Password</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="password" name="password" placeholder="" type="hash" />
                </div>
              </div>

              <div class="row justify-content-end">
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
</div>
@endsection
