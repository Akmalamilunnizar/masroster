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
                        <label class="col-sm-2 col-form-label" for="IdRoster">ID Produk</label>
                        <div class="col-sm-10">
                            <input type="text" id="IdRoster" name="IdRoster" class="form-control" value="{{ $produk->IdRoster }}" readonly style="background-color: #e9ecef; cursor: default;">
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
    // Endpoints via route() to avoid path issues
    const GET_TIPE_URL = "{{ route('get.connected.tipe') }}";
    const GET_MOTIF_URL = "{{ route('get.connected.motif') }}";

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

    // Cascading dropdowns for Jenis -> Tipe -> Motif
    const jenisSelect = document.getElementById('IdJenisBarang');
    const tipeSelect = document.getElementById('id_tipe');
    const motifSelect = document.getElementById('id_motif');

    function setOptions(selectEl, items, valueKey, labelKey, emptyLabel) {
        selectEl.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = emptyLabel;
        selectEl.appendChild(placeholder);
        if (Array.isArray(items) && items.length > 0) {
            items.forEach(function(item) {
                const opt = document.createElement('option');
                opt.value = item[valueKey];
                opt.textContent = item[labelKey];
                selectEl.appendChild(opt);
            });
        }
    }

    jenisSelect.addEventListener('change', function() {
        const jenisId = this.value;
        // Preserve current tipe value before resetting
        const currentTipeValue = tipeSelect.value;

        // Reset child selects
        setOptions(tipeSelect, [], 'IdTipe', 'namaTipe', 'Pilih Tipe');
        setOptions(motifSelect, [], 'IdMotif', 'nama_motif', 'Pilih Motif');

        if (!jenisId) return;

        // Show loading indicator
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Memuat tipe...';
        tipeSelect.innerHTML = '';
        tipeSelect.appendChild(loading);

        fetch(`${GET_TIPE_URL}?jenis_id=${encodeURIComponent(jenisId)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        })
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(data => {
                console.log('Tipe data received:', data);
                setOptions(tipeSelect, data, 'IdTipe', 'namaTipe', data.length ? 'Pilih Tipe' : 'Tidak ada tipe');

                // Restore previous selection if it still exists in the new options
                if (currentTipeValue && tipeSelect.querySelector(`option[value="${currentTipeValue}"]`)) {
                    tipeSelect.value = currentTipeValue;
                    // Trigger change to load motif if tipe value was restored
                    tipeSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch((error) => {
                console.error('Error fetching tipe:', error);
                let errorMessage = 'Gagal memuat tipe';
                if (error.status === 403) {
                    errorMessage = 'Akses ditolak (403) - Periksa login';
                } else if (error.status === 404) {
                    errorMessage = 'Endpoint tidak ditemukan (404)';
                } else if (error.status === 500) {
                    errorMessage = 'Server error (500)';
                }
                setOptions(tipeSelect, [], 'IdTipe', 'namaTipe', errorMessage);
            });
    });

    tipeSelect.addEventListener('change', function() {
        const tipeId = this.value;
        // Preserve current motif value before resetting
        const currentMotifValue = motifSelect.value;

        setOptions(motifSelect, [], 'IdMotif', 'nama_motif', 'Pilih Motif');
        if (!tipeId) return;

        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Memuat motif...';
        motifSelect.innerHTML = '';
        motifSelect.appendChild(loading);

        fetch(`${GET_MOTIF_URL}?tipe_id=${encodeURIComponent(tipeId)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        })
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(data => {
                console.log('Motif data received:', data);
                setOptions(motifSelect, data, 'IdMotif', 'nama_motif', data.length ? 'Pilih Motif' : 'Tidak ada motif');

                // Restore previous selection if it still exists in the new options
                if (currentMotifValue && motifSelect.querySelector(`option[value="${currentMotifValue}"]`)) {
                    motifSelect.value = currentMotifValue;
                }
            })
            .catch((error) => {
                console.error('Error fetching motif:', error);
                setOptions(motifSelect, [], 'IdMotif', 'nama_motif', 'Gagal memuat motif');
            });
    });

    // Functions for adding new items via modals
    function addJenis() {
        console.log('addJenis function called');
        const formData = new FormData(document.getElementById('addJenisForm'));
        fetch('{{ route("quick.add.jenis") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to jenis select
                const newOption = document.createElement('option');
                newOption.value = data.id;
                newOption.textContent = data.name;
                document.getElementById('IdJenisBarang').appendChild(newOption);
                newOption.selected = true;

                // Close modal and clear form
                var modal = bootstrap.Modal.getInstance(document.getElementById('addJenisModal'));
                if (modal) {
                    modal.hide();
                }
                document.getElementById('addJenisForm').reset();

                CustomModal.success('Jenis berhasil ditambahkan!');
            } else {
                CustomModal.error(data.message || 'Terjadi kesalahan saat menambah jenis');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            CustomModal.error('Terjadi kesalahan saat menambah jenis');
        });
    }

    function addTipe() {
        console.log('addTipe function called');
        const formData = new FormData(document.getElementById('addTipeForm'));
        fetch('{{ route("quick.add.tipe") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to tipe select
                const newOption = document.createElement('option');
                newOption.value = data.id;
                newOption.textContent = data.name;
                document.getElementById('id_tipe').appendChild(newOption);
                newOption.selected = true;

                // Close modal and clear form
                var modal = bootstrap.Modal.getInstance(document.getElementById('addTipeModal'));
                if (modal) {
                    modal.hide();
                }
                document.getElementById('addTipeForm').reset();

                CustomModal.success('Tipe berhasil ditambahkan!');
            } else {
                CustomModal.error(data.message || 'Terjadi kesalahan saat menambah tipe');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            CustomModal.error('Terjadi kesalahan saat menambah tipe');
        });
    }

    function addMotif() {
        const formData = new FormData(document.getElementById('addMotifForm'));
        fetch('{{ route("quick.add.motif") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to motif select
                const newOption = document.createElement('option');
                newOption.value = data.id;
                newOption.textContent = data.name;
                document.getElementById('id_motif').appendChild(newOption);
                newOption.selected = true;

                // Close modal and clear form
                bootstrap.Modal.getInstance(document.getElementById('addMotifModal')).hide();
                document.getElementById('addMotifForm').reset();

                CustomModal.success('Motif berhasil ditambahkan!');
            } else {
                CustomModal.error(data.message || 'Terjadi kesalahan saat menambah motif');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            CustomModal.error('Terjadi kesalahan saat menambah motif');
        });
    }

    function addSize() {
        const formData = new FormData(document.getElementById('addSizeForm'));
        fetch('{{ route("quick.add.size") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to all size selects
                const sizeSelects = document.querySelectorAll('select[name="sizes[]"]');
                sizeSelects.forEach(select => {
                    const newOption = document.createElement('option');
                    newOption.value = data.id;
                    newOption.textContent = data.name + ' (' + data.panjang + ' x ' + data.lebar + ' Cm)';
                    select.appendChild(newOption);
                });

                // Close modal and clear form
                bootstrap.Modal.getInstance(document.getElementById('addSizeModal')).hide();
                document.getElementById('addSizeForm').reset();

                CustomModal.success('Ukuran berhasil ditambahkan!');
            } else {
                CustomModal.error(data.message || 'Terjadi kesalahan saat menambah ukuran');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            CustomModal.error('Terjadi kesalahan saat menambah ukuran');
        });
    }

    // Load tipe options when jenis changes in motif modal
    document.addEventListener('DOMContentLoaded', function() {
        const newJenisForMotif = document.getElementById('newJenisForMotif');
        if (newJenisForMotif) {
            newJenisForMotif.addEventListener('change', function() {
                const jenisId = this.value;
                const tipeSelect = document.getElementById('newTipeForMotif');

                if (!jenisId) {
                    tipeSelect.innerHTML = '<option value="">Pilih Tipe</option>';
                    return;
                }

                console.log('Loading tipe for jenis in motif modal:', jenisId);
                console.log('GET_TIPE_URL:', GET_TIPE_URL);

                fetch(`${GET_TIPE_URL}?jenis_id=${encodeURIComponent(jenisId)}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin'
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(data => {
                    console.log('Tipe data received for motif modal:', data);
                    setOptions(tipeSelect, data, 'IdTipe', 'namaTipe', data.length ? 'Pilih Tipe' : 'Tidak ada tipe');
                })
                .catch(error => {
                    console.error('Error fetching tipe for motif modal:', error);
                    tipeSelect.innerHTML = '<option value="">Error loading tipe</option>';
                });
            });
        }
    });

    // Load initial data when page loads (for edit form)
    document.addEventListener('DOMContentLoaded', function() {
        // Get old values from Laravel (for form validation errors)
        const oldTipeValue = @json(old('id_tipe', $produk->id_tipe ?? null));
        const oldMotifValue = @json(old('id_motif', $produk->id_motif ?? null));
        const oldJenisValue = @json(old('IdJenisBarang', $produk->id_jenis ?? null));

        // If jenis is already selected, load connected tipe and motif
        const jenisIdToLoad = oldJenisValue || jenisSelect.value;
        if (jenisSelect && jenisIdToLoad) {
            // Set jenis value if it's different
            if (oldJenisValue && jenisSelect.value !== oldJenisValue) {
                jenisSelect.value = oldJenisValue;
            }

            // Load connected tipe for the current jenis
            fetch(`${GET_TIPE_URL}?jenis_id=${jenisIdToLoad}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin'
            })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(data => {
                    // Clear existing options
                    tipeSelect.innerHTML = '<option value="">Pilih Tipe</option>';

                    // Determine which tipe value to select (old value or current value)
                    const tipeValueToSelect = oldTipeValue || tipeSelect.dataset.currentValue || null;

                    data.forEach(tipe => {
                        const option = document.createElement('option');
                        option.value = tipe.IdTipe;
                        option.textContent = tipe.namaTipe;
                        if (tipe.IdTipe == tipeValueToSelect) {
                            option.selected = true;
                        }
                        tipeSelect.appendChild(option);
                    });

                    // Update tipe select value if old value exists
                    if (oldTipeValue && tipeSelect.value !== oldTipeValue) {
                        tipeSelect.value = oldTipeValue;
                    }

                    // If tipe is selected, load connected motif
                    const tipeIdToLoad = oldTipeValue || tipeSelect.value;
                    if (tipeIdToLoad) {
                        fetch(`${GET_MOTIF_URL}?tipe_id=${tipeIdToLoad}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            credentials: 'same-origin'
                        })
                            .then(r => r.ok ? r.json() : Promise.reject(r))
                            .then(motifData => {
                                motifSelect.innerHTML = '<option value="">Pilih Motif</option>';

                                // Determine which motif value to select (old value or current value)
                                const motifValueToSelect = oldMotifValue || motifSelect.dataset.currentValue || null;

                                motifData.forEach(motif => {
                                    const option = document.createElement('option');
                                    option.value = motif.IdMotif;
                                    option.textContent = motif.nama_motif;
                                    if (motif.IdMotif == motifValueToSelect) {
                                        option.selected = true;
                                    }
                                    motifSelect.appendChild(option);
                                });

                                // Update motif select value if old value exists
                                if (oldMotifValue && motifSelect.value !== oldMotifValue) {
                                    motifSelect.value = oldMotifValue;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching motif data:', error);
                            });
                    }
                })
                .catch(error => {
                    console.error('Error fetching tipe data:', error);
                });
        } else {
            // Only trigger change event if we haven't already loaded data above
            // This handles the case when there's no initial value but user wants to select
            if (jenisSelect && jenisSelect.value && !oldJenisValue) {
                // Use setTimeout to ensure DOM is ready
                setTimeout(() => {
                    jenisSelect.dispatchEvent(new Event('change'));
                }, 100);
            }
        }
    });
</script>
@endpush
@endsection
