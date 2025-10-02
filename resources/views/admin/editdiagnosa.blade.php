<!-- resources/views/admin/editdiagnosa.blade.php -->
@extends('admin.layouts.template')

@section('page_title')
Edit Diagnosa - Restorant
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Edit Diagnosa</h4>
    <div class="col-xxl">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Diagnosa</h5>
                <small class="text-muted float-end">Perbarui Informasi</small>
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
                <form action="{{ route('update-diagnosa', $diagnosa->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST') <!-- Bisa juga menggunakan 'PUT' atau 'PATCH' jika diperlukan -->

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="tanggal">Tanggal</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="tanggal" name="tanggal"
                                value="{{ old('tanggal', $diagnosa->tanggal) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="jenis_koi">Jenis Koi</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="jenis_koi" name="jenis_koi"
                                value="{{ old('jenis_koi', $diagnosa->jenis_koi) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="penyakit">Penyakit</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="penyakit" name="penyakit"
                                value="{{ old('penyakit', $diagnosa->penyakit) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="penyebab">Penyebab</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="penyebab" name="penyebab"
                                value="{{ old('penyebab', $diagnosa->penyebab) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="gambar_koi">Gambar Koi</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" id="gambar_koi" name="gambar_koi" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="keterangan">Keterangan</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="keterangan" name="keterangan"
                                rows="3">{{ old('keterangan', $diagnosa->keterangan) }}</textarea>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Update Diagnosa</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection