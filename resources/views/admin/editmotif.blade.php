@extends('admin.layouts.template')
@section('page_title','Edit Motif Roster')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Edit Motif</h4>
  
  @if (session('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Edit Motif: {{ $motif->nama_motif }}</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('updatemotif', $motif->IdMotif) }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
          <label for="nama_motif" class="form-label">Nama Motif</label>
          <input type="text" class="form-control @error('nama_motif') is-invalid @enderror" 
                 id="nama_motif" name="nama_motif" required maxlength="35" 
                 value="{{ old('nama_motif', $motif->nama_motif) }}">
          @error('nama_motif')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Terhubung dengan Tipe Roster</label>
          <div class="row">
            @foreach($tipeList as $tipe)
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" 
                         name="tipe_connections[]" 
                         value="{{ $tipe->IdTipe }}" 
                         id="tipe_{{ $tipe->IdTipe }}"
                         {{ in_array($tipe->IdTipe, $motif->tipeRosters->pluck('IdTipe')->toArray()) ? 'checked' : '' }}>
                  <label class="form-check-label" for="tipe_{{ $tipe->IdTipe }}">
                    {{ $tipe->namaTipe }}
                  </label>
                </div>
              </div>
            @endforeach
          </div>
          @error('tipe_connections')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary">
            <i class="fas fa-save me-1"></i>Update Motif
          </button>
          <a href="{{ route('allmotif') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


