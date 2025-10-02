    @extends('admin.layouts.template')

    @section('page_title')
    CIME | Halaman Daftar Roster
    @endsection
    @section('search')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama produk..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..." />
        </div>
    </div>
    @endsection

    @section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-2 mb-3"><span class="text-muted fw-light">Data Roster /</span> Daftar Roster</h4>
        <a href="{{ route('addproduk') }}" class="btn btn-outline-primary mb-3">
            + Tambah Produk Roster
        </a>

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

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Roster Yang Terdaftar
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold text-center align-middle" style="width: 80px; min-width: 80px;">ID</th>
                            <th class="fw-bold text-center align-middle" style="width: 120px; min-width: 120px;">Gambar</th>
                            <th class="fw-bold text-center align-middle">Jenis Roster</th>
                            <th class="fw-bold text-center align-middle" style="width: 150px;">Tipe Roster</th>
                            <th class="fw-bold text-center align-middle" style="width: 150px;">Motif</th>
                            <th class="fw-bold text-center align-middle" style="width: 120px;">Harga</th>
                            <th class="fw-bold text-center align-middle" style="width: 180px;">Ukuran</th>
                            <th class="fw-bold text-center align-middle" style="width: 100px;">Stok</th>
                            <th class="fw-bold text-center align-middle" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataProduk as $produk)
                        <tr class="align-middle">
                            <td class="text-center fw-semibold">{{ $produk->IdRoster }}</td>

                            <td class="text-center">
                                @if ($produk->Img)
                                <img src="{{ asset('storage/' . $produk->Img) }}" class="img-thumbnail" style="max-width: 60px; height: 60px; object-fit: cover;">
                                @else
                                <span class="text-muted small">No Image</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $produk->jenisRoster->JenisBarang ?? '-' }}</span>
                            </td>
                            <td class="text-center fw-medium">{{ $produk->tipeRoster->namaTipe ?? '-' }}</td>
                            <td class="text-center">
                                @if($produk->motif)
                                <span class="badge bg-info">{{ $produk->motif->nama_motif }}</span>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($produk->sizes->count())
                                @foreach ($produk->sizes as $size)
                                <span class="badge bg-success text-white mb-1 d-block">
                                    Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                                </span>
                                @endforeach
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($produk->sizes->count())
                                @foreach ($produk->sizes as $size)
                                <span class="badge bg-info text-white mb-1 d-block">
                                    {{ $size->nama }} ({{ $size->panjang }}×{{ $size->lebar }} Cm)
                                </span>
                                @endforeach
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($produk->JumlahStok > 0)
                                    @if($produk->JumlahStok < 10)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $produk->JumlahStok }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>{{ $produk->JumlahStok }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>0
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.detail_allitems', $produk->IdRoster) }}" class="btn btn-info" style="border-radius: 8px;">
                                        <i class="fas fa-info-circle me-1"></i> Detail
                                    </a>
                                    <a href="{{ route('editproduk', $produk->IdRoster) }}" class="btn btn-sm btn-warning" style="border-radius: 6px; width: 80px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('deleteproduk', $produk->IdRoster) }}" method="POST" style="display:inline;" id="delete-form-{{ $produk->IdRoster }}">
                                        @csrf
                                        @method('DELETE')
                                        <a href="#" class="btn btn-sm btn-danger" style="border-radius: 6px; width: 80px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;" onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus produk ini?')) document.getElementById('delete-form-{{ $produk->IdRoster }}').submit();">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endsection

    <script>
        $(document).ready(function() {
            $('#ukuran').on('change', function() {
                if ($(this).val() === '') {
                    $('#customSizeFields').show();
                } else {
                    $('#customSizeFields').hide();
                }
            });
        });
    </script>