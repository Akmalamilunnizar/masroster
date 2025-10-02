@extends('admin.layouts.template')

@section('page_title')
CIME | Halaman Edit Produk
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman /</span> Edit Produk</h4>
    <div class="col-xxl">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold fs-4">Edit Data Produk</h5>
            </div>
            <div class="card-body">
                @if (session('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('updateproduk', $produk->IdRoster) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdRoster">ID Roster</label>
                        <div class="col-sm-10">
                            <input type="text" id="IdRoster" name="IdRoster" class="form-control" value="{{ $produk->IdRoster }}" readonly style="background-color: #e9ecef; cursor: default;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdJenisBarang">Jenis Roster</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="IdJenisBarang" name="IdJenisBarang" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis->IdJenisBarang }}" {{ old('IdJenisBarang', $produk->id_jenis) == $jenis->IdJenisBarang ? 'selected' : '' }}>
                                        {{ $jenis->JenisBarang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="id_tipe">Tipe Roster</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="id_tipe" name="id_tipe" required>
                                <option value="">Pilih Tipe</option>
                                @if($produk->tipeRoster)
                                    <option value="{{ $produk->tipeRoster->IdTipe }}" selected>
                                        {{ $produk->tipeRoster->namaTipe }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="id_motif">Motif</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="id_motif" name="id_motif">
                                <option value="">Pilih Motif</option>
                                @if($produk->motif)
                                    <option value="{{ $produk->motif->IdMotif }}" selected>
                                        {{ $produk->motif->nama_motif }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="sizes">Ukuran & Harga</label>
                        <div class="col-sm-10">
                            <div id="ukuran-harga-list">
                                @foreach($produk->sizes as $i => $size)
                                <div class="row mb-2 ukuran-harga-item">
                                    <div class="col-md-6">
                                        <select name="sizes[]" class="form-select" required>
                                            <option value="">Pilih Ukuran</option>
                                            @foreach($sizeList as $s)
                                                <option value="{{ $s->id_ukuran }}" {{ $size->id_ukuran == $s->id_ukuran ? 'selected' : '' }}>
                                                    {{ $s->nama }} ({{ $s->panjang }} x {{ $s->lebar }} + "Cm")
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="harga_per_size[]" class="form-control" placeholder="Harga" value="{{ $size->pivot->harga }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-ukuran-harga">Hapus</button>
                                    </div>
                                </div>
                                @endforeach
                                @if($produk->sizes->count() == 0)
                                <div class="row mb-2 ukuran-harga-item">
                                    <div class="col-md-6">
                                        <select name="sizes[]" class="form-select" required>
                                            <option value="">Pilih Ukuran</option>
                                            @foreach($sizeList as $s)
                                                <option value="{{ $s->id_ukuran }}">
                                                    {{ $s->nama }} ({{ $s->panjang }} x {{ $s->lebar }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="harga_per_size[]" class="form-control" placeholder="Harga" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ukuran-harga">Hapus</button>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <button type="button" id="add-ukuran-harga" class="btn btn-outline-secondary btn-sm mt-2">+ Tambah Ukuran</button>
                        </div>
                    </div>



                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="deskripsi">Deskripsi</label>
                        <div class="col-sm-10">
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4" required>{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="Img">Gambar</label>
                        <div class="col-sm-10">
                            @if($produk->Img)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $produk->Img) }}" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                            <input class="form-control @error('Img') is-invalid @enderror" type="file" id="Img" name="Img">
                            @error('Img')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-outline-primary">Update Produk</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Dynamic add/remove ukuran-harga rows
    document.getElementById('add-ukuran-harga').onclick = function() {
        let list = document.getElementById('ukuran-harga-list');
        let item = list.querySelector('.ukuran-harga-item').cloneNode(true);
        item.querySelector('select').value = '';
        item.querySelector('input').value = '';
        list.appendChild(item);
    };
    document.getElementById('ukuran-harga-list').onclick = function(e) {
        if (e.target.classList.contains('remove-ukuran-harga')) {
            let items = document.querySelectorAll('.ukuran-harga-item');
            if (items.length > 1) e.target.closest('.ukuran-harga-item').remove();
        }
    };

    // Cascading dropdowns for Jenis -> Tipe -> Motif (Edit form)
    document.getElementById('IdJenisBarang').addEventListener('change', function() {
        const jenisId = this.value;
        const tipeSelect = document.getElementById('id_tipe');
        const motifSelect = document.getElementById('id_motif');
        
        // Reset tipe and motif dropdowns
        tipeSelect.innerHTML = '<option value="">Pilih Tipe</option>';
        motifSelect.innerHTML = '<option value="">Pilih Motif</option>';
        
        if (jenisId) {
            // Fetch connected tipe roster
            fetch(`/admin/get-connected-tipe?jenis_id=${jenisId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(tipe => {
                        const option = document.createElement('option');
                        option.value = tipe.IdTipe;
                        option.textContent = tipe.namaTipe;
                        tipeSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching tipe data:', error);
                });
        }
    });

    document.getElementById('id_tipe').addEventListener('change', function() {
        const tipeId = this.value;
        const motifSelect = document.getElementById('id_motif');
        
        // Reset motif dropdown
        motifSelect.innerHTML = '<option value="">Pilih Motif</option>';
        
        if (tipeId) {
            // Fetch connected motif roster
            fetch(`/admin/get-connected-motif?tipe_id=${tipeId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(motif => {
                        const option = document.createElement('option');
                        option.value = motif.IdMotif;
                        option.textContent = motif.nama_motif;
                        motifSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching motif data:', error);
                });
        }
    });

    // Load initial data when page loads (for edit form)
    document.addEventListener('DOMContentLoaded', function() {
        const jenisSelect = document.getElementById('IdJenisBarang');
        const tipeSelect = document.getElementById('id_tipe');
        const motifSelect = document.getElementById('id_motif');
        
        // If jenis is already selected, load connected tipe and motif
        if (jenisSelect.value) {
            // Load connected tipe for the current jenis
            fetch(`/admin/get-connected-tipe?jenis_id=${jenisSelect.value}`)
                .then(response => response.json())
                .then(data => {
                    // Clear existing options except the selected one
                    const currentTipeValue = tipeSelect.value;
                    tipeSelect.innerHTML = '<option value="">Pilih Tipe</option>';
                    
                    data.forEach(tipe => {
                        const option = document.createElement('option');
                        option.value = tipe.IdTipe;
                        option.textContent = tipe.namaTipe;
                        if (tipe.IdTipe == currentTipeValue) {
                            option.selected = true;
                        }
                        tipeSelect.appendChild(option);
                    });
                    
                    // If tipe is selected, load connected motif
                    if (tipeSelect.value) {
                        fetch(`/admin/get-connected-motif?tipe_id=${tipeSelect.value}`)
                            .then(response => response.json())
                            .then(motifData => {
                                const currentMotifValue = motifSelect.value;
                                motifSelect.innerHTML = '<option value="">Pilih Motif</option>';
                                
                                motifData.forEach(motif => {
                                    const option = document.createElement('option');
                                    option.value = motif.IdMotif;
                                    option.textContent = motif.nama_motif;
                                    if (motif.IdMotif == currentMotifValue) {
                                        option.selected = true;
                                    }
                                    motifSelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching motif data:', error);
                            });
                    }
                })
                .catch(error => {
                    console.error('Error fetching tipe data:', error);
                });
        }
    });
</script>
@endpush
@endsection
