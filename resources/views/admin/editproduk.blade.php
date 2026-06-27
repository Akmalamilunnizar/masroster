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

                <form action="{{ route('updateproduk', $produk->sku ?? $produk->IdRoster ?? $produk->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdRoster">ID Produk</label>
                        <div class="col-sm-10">
                            <input type="text" id="IdRoster" name="IdRoster" class="form-control" value="{{ $produk->sku ?? $produk->IdRoster ?? $produk->id }}" readonly style="background-color: #e9ecef; cursor: default;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="IdJenisBarang">Jenis Produk</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <select class="form-select" id="IdJenisBarang" name="IdJenisBarang" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($jenisList as $jenis)
                                        <option value="{{ $jenis->IdJenisBarang }}" {{ old('IdJenisBarang', $produk->id_jenis) == $jenis->IdJenisBarang ? 'selected' : '' }}>
                                            {{ $jenis->JenisBarang }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addJenisModal">
                                    <i class="fas fa-plus"></i> Tambah Jenis
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="id_tipe">Tipe Produk</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <select class="form-select" id="id_tipe" name="id_tipe" required data-current-value="{{ old('id_tipe', $produk->id_tipe ?? '') }}">
                                    <option value="">Pilih Tipe</option>
                                    @if($produk->tipeRoster)
                                        <option value="{{ $produk->tipeRoster->IdTipe }}" {{ old('id_tipe', $produk->id_tipe) == $produk->tipeRoster->IdTipe ? 'selected' : '' }}>
                                            {{ $produk->tipeRoster->namaTipe }}
                                        </option>
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addTipeModal">
                                    <i class="fas fa-plus"></i> Tambah Tipe
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="id_motif">Motif (opsional)</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <select class="form-select" id="id_motif" name="id_motif" data-current-value="{{ old('id_motif', $produk->id_motif ?? '') }}">
                                    <option value="">Pilih Motif</option>
                                    @if($produk->motif)
                                        <option value="{{ $produk->motif->IdMotif }}" {{ old('id_motif', $produk->id_motif) == $produk->motif->IdMotif ? 'selected' : '' }}>
                                            {{ $produk->motif->nama_motif }}
                                        </option>
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addMotifModal">
                                    <i class="fas fa-plus"></i> Tambah Motif
                                </button>
                            </div>
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
                                                    {{ $s->nama }} ({{ $s->panjang }} x {{ $s->lebar }} Cm)
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
                            <div class="mt-2">
                                <button type="button" id="add-ukuran-harga" class="btn btn-outline-secondary btn-sm">+ Tambah Ukuran</button>
                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                                    <i class="fas fa-plus"></i> Tambah Ukuran Baru
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="stock">Stok Awal</label>
                        <div class="col-sm-10">
                            <input type="number" name="stock" id="stock" class="form-control"
                                   placeholder="Masukkan jumlah stok awal" min="0" value="{{ old('stock', $produk->stock ?? 0) }}" required>
                            <small class="form-text text-muted">Masukkan jumlah stok awal untuk produk ini</small>
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

<!-- Modal for adding new Jenis -->
<div class="modal fade" id="addJenisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jenis Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addJenisForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newJenisBarang" class="form-label">Nama Jenis</label>
                        <input type="text" class="form-control" id="newJenisBarang" name="JenisBarang" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addJenis()" id="addJenisBtn">Tambah</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for adding new Tipe -->
<div class="modal fade" id="addTipeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tipe Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTipeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newNamaTipe" class="form-label">Nama Tipe</label>
                        <input type="text" class="form-control" id="newNamaTipe" name="namaTipe" required>
                    </div>
                    <div class="mb-3">
                        <label for="newJenisForTipe" class="form-label">Jenis</label>
                        <select class="form-select" id="newJenisForTipe" name="id_jenis" required>
                            <option value="">Pilih Jenis</option>
                            @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->IdJenisBarang }}">{{ $jenis->JenisBarang }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addTipe()" id="addTipeBtn">Tambah</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for adding new Motif -->
<div class="modal fade" id="addMotifModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Motif Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMotifForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newNamaMotif" class="form-label">Nama Motif</label>
                        <input type="text" class="form-control" id="newNamaMotif" name="nama_motif" required>
                    </div>
                    <div class="mb-3">
                        <label for="newJenisForMotif" class="form-label">Jenis</label>
                        <select class="form-select" id="newJenisForMotif" name="id_jenis" required>
                            <option value="">Pilih Jenis</option>
                            @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->IdJenisBarang }}">{{ $jenis->JenisBarang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newTipeForMotif" class="form-label">Tipe</label>
                        <select class="form-select" id="newTipeForMotif" name="id_tipe" required>
                            <option value="">Pilih Tipe</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addMotif()">Tambah</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for adding new Size -->
<div class="modal fade" id="addSizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Ukuran Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSizeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newNamaSize" class="form-label">Nama Ukuran</label>
                        <input type="text" class="form-control" id="newNamaSize" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPanjang" class="form-label">Panjang (cm)</label>
                        <input type="number" class="form-control" id="newPanjang" name="panjang" required>
                    </div>
                    <div class="mb-3">
                        <label for="newLebar" class="form-label">Lebar (cm)</label>
                        <input type="number" class="form-control" id="newLebar" name="lebar" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addSize()">Tambah</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.AppConfig = {
            routes: {
                // getTipe: "{{ route('get.connected.tipe') }}",
                getMotif: "{{ route('get.connected.motif') }}",
                testAjax: "{{ route('test.ajax') }}",
                addJenis: "{{ route('quick.add.jenis') }}",
                addTipe: "{{ route('quick.add.tipe') }}",
                addMotif: "{{ route('quick.add.motif') }}",
                addSize: "{{ route('quick.add.size') }}"
            }
        };
    </script>
    <script src="{{ asset('js/produk-management.js') }}"></script>
@endpush
@endsection
