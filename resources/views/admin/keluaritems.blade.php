@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Keluar Barang
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Halaman/</span> Keluar Barang</h4>

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif

        <div class="card">
            <h5 class="card-header fw-bold">Form Barang Keluar</h5>
            <div class="card-body">
                <form action="{{ route('store-exititems') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="IdKeluar">ID Keluar</label>
                            <input type="text" class="form-control" id="IdKeluar" name="IdKeluar" value="{{ $newIdKeluar }}" readonly />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ $username }}" readonly />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="IdBarang">Pilih Barang</label>
                            <select class="form-select" id="IdBarang" name="IdBarang" required>
                                <option value="">Pilih Barang...</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->IdBarang }}" data-stock="{{ $item->JumlahStok }}">
                                        {{ $item->NamaBarang }} (Stok: {{ $item->JumlahStok }} {{ $item->satuan->Satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="QtyKeluar">Jumlah Keluar</label>
                            <input type="number" class="form-control" id="QtyKeluar" name="QtyKeluar" min="1" required />
                            <small class="text-muted">Stok tersedia: <span id="availableStock">0</span></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">Barang keluar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('IdBarang').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            document.getElementById('availableStock').textContent = stock;
            document.getElementById('QtyKeluar').max = stock;
        });
    </script>
    @endpush
@endsection 