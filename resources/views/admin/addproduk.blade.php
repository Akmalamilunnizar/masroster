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
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addJenisModal">
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
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addTipeModal">
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
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addMotifModal">
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
                                <input type="number" name="harga_per_size[]" class="form-control" placeholder="Harga" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger" style="border-radius: 8px;">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" id="add-ukuran-harga" class="btn btn-outline-secondary btn-sm">+ Tambah Ukuran</button>
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                            <i class="fas fa-plus"></i> Tambah Ukuran Baru
                        </button>
                    </div>
                </div>
                <!-- Stok Awal -->
                <div class="mb-3">
                    <label for="JumlahStok" class="form-label">Stok Awal</label>
                    <input type="number" name="JumlahStok" id="JumlahStok" class="form-control" 
                           placeholder="Masukkan jumlah stok awal" min="0" value="{{ old('JumlahStok', 0) }}" required>
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
                <button type="button" class="btn btn-outline-secondary" onclick="testAjax()">Test AJAX</button>
                <button type="button" class="btn btn-outline-warning" onclick="testModal()">Test Modal</button>
                <button type="button" class="btn btn-outline-danger" onclick="forceClearAllModals()">Clear Modal</button>
                <button type="button" class="btn btn-outline-info" onclick="testMotifModal()">Test Motif Modal</button>
            </form>
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
@endsection

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

    // Endpoints via route() to avoid path issues
    const GET_TIPE_URL = "{{ route('get.connected.tipe') }}";
    const GET_MOTIF_URL = "{{ route('get.connected.motif') }}";
    const TEST_AJAX_URL = "{{ route('test.ajax') }}";

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
            })
            .catch((error) => {
                console.error('Error fetching motif:', error);
                setOptions(motifSelect, [], 'IdMotif', 'nama_motif', 'Gagal memuat motif');
            });
    });

    // Test AJAX function
    function testAjax() {
        console.log('Testing AJAX...');
        fetch(TEST_AJAX_URL, {
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
            console.log('Test AJAX successful:', data);
            alert('AJAX test successful: ' + JSON.stringify(data));
        })
        .catch((error) => {
            console.error('Test AJAX failed:', error);
            alert('AJAX test failed: ' + error.message);
        });
    }

    // Test Modal function
    function testModal() {
        console.log('Testing Modal...');
        var modal = new bootstrap.Modal(document.getElementById('addTipeModal'));
        modal.show();
    }

    // Test Motif Modal function
    function testMotifModal() {
        console.log('Testing Motif Modal...');
        var modal = new bootstrap.Modal(document.getElementById('addMotifModal'));
        modal.show();
    }

    // Function to clear modal backdrop if stuck
    function clearModalBackdrop() {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Also remove any stuck modal classes
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        });
    }

    // Force clear all modals and backdrops
    function forceClearAllModals() {
        console.log('Force clearing all modals...');
        
        // Remove all backdrops
        var backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.remove();
        });
        
        // Hide all modals
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        });
        
        // Reset body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('All modals cleared!');
    }

    // Add event listeners for modal events
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for modal events
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('show.bs.modal', function() {
                console.log('Modal showing:', this.id);
            });
            
            modal.addEventListener('shown.bs.modal', function() {
                console.log('Modal shown:', this.id);
                
                // Special handling for motif modal
                if (this.id === 'addMotifModal') {
                    console.log('Motif modal shown, checking tipe dropdown...');
                    const tipeSelect = document.getElementById('newTipeForMotif');
                    const jenisSelect = document.getElementById('newJenisForMotif');
                    
                    console.log('Tipe select element:', tipeSelect);
                    console.log('Jenis select element:', jenisSelect);
                    console.log('Jenis select value:', jenisSelect ? jenisSelect.value : 'not found');
                    
                    // If jenis is already selected, load tipe options
                    if (jenisSelect && jenisSelect.value) {
                        console.log('Jenis already selected, loading tipe options...');
                        jenisSelect.dispatchEvent(new Event('change'));
                    }
                }
            });
            
            modal.addEventListener('hide.bs.modal', function() {
                console.log('Modal hiding:', this.id);
            });
            
            modal.addEventListener('hidden.bs.modal', function() {
                console.log('Modal hidden:', this.id);
                // Clear any stuck backdrop
                clearModalBackdrop();
            });
        });
        
        // Add keyboard shortcut to clear stuck modals (Ctrl+Shift+M)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'M') {
                console.log('Force clearing all modals...');
                forceClearAllModals();
                alert('All modals and backdrops cleared!');
            }
        });
    });

    // Initialize on load if Jenis already has a value (e.g., when returning from validation error)
    document.addEventListener('DOMContentLoaded', function() {
        if (jenisSelect && jenisSelect.value) {
            jenisSelect.dispatchEvent(new Event('change'));
        }
        
        // Initialize Bootstrap modals properly
        var modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(function(modalEl) {
            // Don't initialize modals here, let Bootstrap handle it automatically
        });
        
        // Add click event listeners for modal buttons
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Modal button clicked:', button.getAttribute('data-bs-target'));
                
                // Clear any existing modals first
                forceClearAllModals();
                
                var targetModal = document.querySelector(button.getAttribute('data-bs-target'));
                if (targetModal) {
                    // Wait a bit for cleanup, then show modal
                    setTimeout(function() {
                        var modal = bootstrap.Modal.getOrCreateInstance(targetModal);
                        modal.show();
                    }, 100);
                } else {
                    console.error('Modal target not found:', button.getAttribute('data-bs-target'));
                }
            });
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
                
                alert('Jenis berhasil ditambahkan!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambah jenis');
        });
    }

    function addTipe() {
        console.log('addTipe function called');
        alert('addTipe function is working!');
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
                
                alert('Tipe berhasil ditambahkan!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambah tipe');
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
                
                alert('Motif berhasil ditambahkan!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambah motif');
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
                
                alert('Ukuran berhasil ditambahkan!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambah ukuran');
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
</script>
@endpush

<!-- Bootstrap CSS (in <head>) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>