@extends('toko.layouts.template')

@section('page_title')
    CIME | Detail Produk
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="{{ asset('storage/' . ($produk->Img ?? 'assets/images/poster1.jpeg')) }}" 
                     alt="{{ $produk->NamaProduk }}" 
                     class="img-fluid rounded"
                     style="width: 100%; height: auto; object-fit: cover;"
                     onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="fw-bold mb-3" style="color: #2B3674;">{{ $produk->NamaProduk }}</h1>
            
            <!-- Price & Size Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">Pilih Ukuran</label>
                <select class="form-select" id="sizeSelect" name="size_id" onchange="updatePrice()">
                    @foreach($produk->sizes as $size)
                        <option value="{{ $size->id_ukuran }}" data-harga="{{ $size->pivot->harga }}">
                            {{ $size->nama }} ({{ $size->panjang }} x {{ $size->lebar }} {{ $size->satuan->Satuan }}) - Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                        </option>
                    @endforeach
                    <option value="custom" data-harga="{{ $produk->custom_harga }}">Custom Ukuran - Rp {{ number_format($produk->custom_harga, 0, ',', '.') }}</option>
                </select>
            </div>
            <div class="mb-4">
                <span class="fw-bold" id="displayPrice" style="font-size: 1.5rem; color: #4318FF;">
                    Rp {{ isset($produk->sizes[0]) ? number_format($produk->sizes[0]->pivot->harga, 0, ',', '.') : number_format($produk->custom_harga, 0, ',', '.') }}
                </span>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3" style="color: #2B3674;">Deskripsi Produk</h5>
                <p class="text-muted" style="white-space: pre-line;">{{ $produk->deskripsi }}</p>
            </div>

            <!-- Order Form -->
            <div class="card border-0 shadow-sm p-4">
                <form id="orderForm" class="mb-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $produk->IdProduk }}">
                    <input type="hidden" name="product_name" value="{{ $produk->NamaProduk }}">
                    <input type="hidden" name="product_price" id="product_price" value="{{ isset($produk->sizes[0]) ? $produk->sizes[0]->pivot->harga : $produk->custom_harga }}">
                    <input type="hidden" name="product_image" value="{{ $produk->Img }}">

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload File Desain</label>
                        <input type="file" class="form-control" name="design_file" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format yang didukung: JPG, PNG, PDF (Max. 5MB)</small>
                    </div>

                    <!-- Print Options -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilihan Cetak</label>
                        <select class="form-select" name="print_option">
                            <option value="1_sisi">1 Sisi</option>
                            <option value="2_sisi">2 Sisi</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah</label>
                        <div class="input-group" style="width: 150px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">-</button>
                            <input type="number" class="form-control text-center" name="quantity" id="quantity" value="1" min="1">
                            <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Tambahkan catatan khusus untuk pesanan Anda..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger btn-lg" onclick="addToCart()">
                            <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg" onclick="buyNow()">
                            <i class="bi bi-lightning me-2"></i>Beli Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    var select = document.getElementById('sizeSelect');
    var harga = select.options[select.selectedIndex].getAttribute('data-harga');
    document.getElementById('displayPrice').innerText = 'Rp ' + Number(harga).toLocaleString('id-ID');
    document.getElementById('product_price').value = harga;
}
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
});

function increaseQuantity() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCart() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    formData.append('product_id', '{{ $produk->IdProduk }}');
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Produk berhasil ditambahkan ke keranjang',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '{{ route("cart") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Terjadi kesalahan saat menambahkan ke keranjang'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan saat menambahkan ke keranjang'
        });
    });
}

function buyNow() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    formData.append('product_id', '{{ $produk->IdProduk }}');
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("cart") }}';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Terjadi kesalahan saat memproses pesanan'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan saat memproses pesanan'
        });
    });
}
</script>
@endsection 