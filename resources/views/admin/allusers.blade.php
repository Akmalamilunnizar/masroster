@extends('admin.layouts.template')
@section('page_title')
Semua Pengguna - Restorant
@endsection
@section('search')
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <form method="GET" action={{ route('searchusers') }}>
                <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2"
                    placeholder="Pencarian Id atau nama..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..." />
            </form>
        </div>
    </div>
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Semua Pengguna</h4>

    <div class="card">
        <h5 class="card-header">Pengguna Yang Tersedia </h5>
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>

        @endif
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead class="table-light">
              <tr>
                <th>User Id</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Verified</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @foreach($users as $user)
              <tr>
                <!-- <td>{{ $loop->iteration }}</td> -->
                <td>{{ $user->id}}</td>
                <td>{{ $user->f_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->status }}</td>
                <td>{{ $user->created_at }}</td>
                <td>{{ $user->updated_at }}</td>
                <td>
                    <a href="{{route('editusers', $user->id)}}" class="btn btn-primary">Edit</a>
                    {{-- <a href="{{route('deleteusers'. $user->id)}}" class="btn btn-warning">Delete</a> --}}
                    <a href="{{ url('admin/delete-users/' . $user->id) }}" class="btn btn-warning">Hapus</a>
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
