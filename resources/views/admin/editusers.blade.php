@extends('admin.layouts.template')
@section('page_title')
    Edit Sub Category - Single Ecom
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Edit Detail Pengguna</h4>
        <div class="col-xxl">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Edit Detail Pengguna</h5>
                    <small class="text-muted float-end">Masukkan Informasi</small>
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
                    <form action="{{ route('update-users', $users_info->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $users_info->id }}" id="id">
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Pengguna</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="f_name" name="f_name" placeholder=""
                                    value="{{ $users_info->f_name }}" />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Email</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="email" name="email" placeholder=""
                                    value="{{ $users_info->email }}" />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="basic-default-name">Nomor Telepon</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="phone" name="phone" placeholder=""
                                    value="{{ $users_info->phone }}" />
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
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="Masukkan Password Baru" type="hash" />
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Update Detail Pengguna</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
