@extends('admin.layouts.template')

@section('page_title')
    CIME | Tambah Motif Roster
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Motif Roster /</span> Tambah Motif Roster
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
        <!-- Form untuk menambah motif roster baru -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Tambah Motif Roster Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('storemotif') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="nama_motif" class="form-label">Nama Motif Roster</label>
                            <input type="text" class="form-control @error('nama_motif') is-invalid @enderror" 
                                   id="nama_motif" name="nama_motif" value="{{ old('nama_motif') }}" 
                                   placeholder="Masukkan nama motif roster" required maxlength="35">
                            @error('nama_motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Motif Roster
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form untuk menghubungkan tipe dengan motif melalui detail_motif -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-link me-2"></i>Hubungkan Tipe dengan Motif
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('store.detail.motif') }}" method="POST">
                        @csrf
                        
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

                        <div class="mb-3">
                            <label for="id_motif" class="form-label">Motif Roster</label>
                            <select class="form-select @error('id_motif') is-invalid @enderror" 
                                    id="id_motif" name="id_motif" required>
                                <option value="">Pilih Motif Roster</option>
                                @foreach($motifList as $motif)
                                    <option value="{{ $motif->IdMotif }}" {{ old('id_motif') == $motif->IdMotif ? 'selected' : '' }}>
                                        {{ $motif->nama_motif }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motif')
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
        <a href="{{ route('allmotif') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar Motif Roster
        </a>
    </div>
</div>
@endsection


