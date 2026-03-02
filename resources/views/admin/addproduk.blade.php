@extends('admin.layouts.template')
@section('page_title', 'Tambah Produk')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">Tambah Data Produk</h4>
        <div class="card">
            <div class="card-body">
                @if (session('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
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
                <form id="add-produk-form" action="{{ route('storeproduk') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">


                    <!-- Jenis Roster -->
                    <div class="mb-3">
                        <label for="IdJenisBarang" class="form-label">Jenis Roster</label>
                        <div class="input-group">
                            <select name="IdJenisBarang" id="IdJenisBarang" class="form-select" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis->IdJenisBarang }}">{{ $jenis->JenisBarang }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#addJenisModal">
                                <i class="fas fa-plus"></i> Tambah Jenis
                            </button>
                        </div>
                    </div>
                    <!-- Tipe Roster -->
                    <div class="mb-3">
                        <label for="id_tipe" class="form-label">Tipe Roster</label>
                        <div class="input-group">
                            <select name="id_tipe" id="id_tipe" class="form-select" required>
                                <option value="">Pilih Tipe</option>
                            </select>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#addTipeModal">
                                <i class="fas fa-plus"></i> Tambah Tipe
                            </button>
                        </div>
                    </div>
                    <!-- Motif (opsional) -->
                    <div class="mb-3">
                        <label for="id_motif" class="form-label">Motif (opsional)</label>
                        <div class="input-group">
                            <select name="id_motif" id="id_motif" class="form-select">
                                <option value="">Pilih Motif</option>
                            </select>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#addMotifModal">
                                <i class="fas fa-plus"></i> Tambah Motif
                            </button>
                        </div>
                    </div>
                    <!-- Ukuran & Harga per Ukuran -->
                    <div class="mb-3">
                        <label class="form-label">Ukuran & Harga per Ukuran</label>
                        <div id="ukuran-harga-list">
                            <div class="row mb-2 ukuran-harga-item">
                                <div class="col-md-6">
                                    <select name="sizes[]" class="form-select" required>
                                        <option value="">Pilih Ukuran</option>
                                        @foreach($sizeList as $size)
                                            <option value="{{ $size->id_ukuran }}">
                                                {{ $size->nama }} ({{ $size->panjang }} x {{ $size->lebar }} Cm)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="harga_per_size[]" class="form-control" placeholder="Harga"
                                        required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger"
                                        style="border-radius: 8px;">Hapus</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="add-ukuran-harga" class="btn btn-outline-secondary btn-sm">+ Tambah
                                Ukuran</button>
                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addSizeModal">
                                <i class="fas fa-plus"></i> Tambah Ukuran Baru
                            </button>
                        </div>
                    </div>
                    <!-- Stok Awal -->
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stok Awal</label>
                        <input type="number" name="stock" id="stock" class="form-control"
                            placeholder="Masukkan jumlah stok awal" min="0" value="{{ old('stock', 0) }}" required>
                        <div class="form-text">Masukkan jumlah stok awal untuk produk ini</div>
                    </div>
                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required>{{ old('deskripsi') }}</textarea>
                    </div>
                    <!-- Gambar -->
                    <div class="mb-3">
                        <label for="Img" class="form-label">Gambar</label>
                        <input type="file" class="form-control" name="Img" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Tambah Produk</button>
                </form>
            </div>
        </div>
    </div>

    @push('modals')
        <!-- Modal for adding new Jenis -->
        <div class="modal fade" id="addJenisModal" tabindex="-1" aria-labelledby="addJenisModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addJenisModalLabel">Tambah Jenis Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        <div class="modal fade" id="addTipeModal" tabindex="-1" aria-labelledby="addTipeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTipeModalLabel">Tambah Tipe Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        <div class="modal fade" id="addMotifModal" tabindex="-1" aria-labelledby="addMotifModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMotifModalLabel">Tambah Motif Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    @foreach($tipeList as $tipe)
                                        <option value="{{ $tipe->IdTipe }}">{{ $tipe->TipeBarang }}</option>
                                    @endforeach
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
        <div class="modal fade" id="addSizeModal" tabindex="-1" aria-labelledby="addSizeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSizeModalLabel">Tambah Ukuran Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    @endpush

@endsection

@push('scripts')
    <script>
        window.AppConfig = {
            routes: {
                getTipe: "{{ route('get.connected.tipe') }}",
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