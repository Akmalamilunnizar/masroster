@extends('admin.layouts.template')

@section('page_title')
    CIME | Edit Tipe Roster
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Tipe Roster /</span> Edit Tipe Roster
    </h4>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Form Edit Tipe Roster
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('updatetiperoster', $tipeRoster->IdTipe) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="IdTipe" class="form-label">ID Tipe Roster</label>
                    <input type="text" class="form-control" id="IdTipe" value="{{ $tipeRoster->IdTipe }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="namaTipe" class="form-label">Nama Tipe Roster</label>
                    <input type="text" class="form-control @error('namaTipe') is-invalid @enderror" 
                           id="namaTipe" name="namaTipe" value="{{ old('namaTipe', $tipeRoster->namaTipe) }}" 
                           placeholder="Masukkan nama tipe roster" required maxlength="40">
                    @error('namaTipe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Terhubung dengan Jenis Roster</label>
                    <div class="row">
                        @foreach($jenisList as $jenis)
                          <div class="col-md-4 mb-2">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" 
                                     name="jenis_connections[]" 
                                     value="{{ $jenis->IdJenisBarang }}" 
                                     id="jenis_{{ $jenis->IdJenisBarang }}"
                                     {{ in_array($jenis->IdJenisBarang, $tipeRoster->jenisRosters->pluck('IdJenisBarang')->toArray()) ? 'checked' : '' }}>
                              <label class="form-check-label" for="jenis_{{ $jenis->IdJenisBarang }}">
                                {{ $jenis->JenisBarang }}
                              </label>
                            </div>
                          </div>
                        @endforeach
                    </div>
                    @error('jenis_connections')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('alltiperoster') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Tipe Roster
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
