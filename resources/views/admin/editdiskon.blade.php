@extends('admin.layouts.template')
@section('page_title', 'Edit Diskon')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Edit Diskon</h4>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('updatediskon', $diskon->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Diskon</label>
                    <input type="text" class="form-control" name="nama" value="{{ $diskon->nama }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <input type="text" class="form-control" name="description" value="{{ $diskon->description }}" maxlength="250">
                </div>
                <div class="mb-3">
                    <label for="persentase" class="form-label">Persentase (%)</label>
                    <input type="number" class="form-control" name="persentase" min="0" max="100" value="{{ $diskon->persentase }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
