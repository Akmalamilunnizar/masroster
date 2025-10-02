@extends('admin.layouts.template')

@section('page_title')
    CIME | Tambah Tipe Roster
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Tipe Roster /</span> Tambah Tipe Roster
    </h4>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="row">
        <!-- Form untuk menambah tipe roster baru -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Tambah Tipe Roster Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('storetiperoster') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="namaTipe" class="form-label">Nama Tipe Roster</label>
                            <input type="text" class="form-control @error('namaTipe') is-invalid @enderror" 
                                   id="namaTipe" name="namaTipe" value="{{ old('namaTipe') }}" 
                                   placeholder="Masukkan nama tipe roster" required maxlength="40">
                            @error('namaTipe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Tipe Roster
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form untuk menghubungkan jenis dengan tipe melalui detail_tipe -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-link me-2"></i>Hubungkan Jenis dengan Tipe
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('store.detail.tipe') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="id_jenis" class="form-label">Jenis Roster</label>
                            <select class="form-select @error('id_jenis') is-invalid @enderror" 
                                    id="id_jenis" name="id_jenis" required>
                                <option value="">Pilih Jenis Roster</option>
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis->IdJenisBarang }}" {{ old('id_jenis') == $jenis->IdJenisBarang ? 'selected' : '' }}>
                                        {{ $jenis->JenisBarang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_tipe" class="form-label">Tipe Roster</label>
                            <select class="form-select @error('id_tipe') is-invalid @enderror" 
                                    id="id_tipe" name="id_tipe" required>
                                <option value="">Pilih Tipe Roster</option>
                                @foreach($tipeList as $tipe)
                                    <option value="{{ $tipe->IdTipe }}" {{ old('id_tipe') == $tipe->IdTipe ? 'selected' : '' }}>
                                        {{ $tipe->namaTipe }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-link me-1"></i>Hubungkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('alltiperoster') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar Tipe Roster
        </a>
    </div>
</div>
@endsection
