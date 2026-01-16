@extends('toko.layouts.template')

@section('page_title')
    CIME | Detail Produk
@endsection

@section('styles')
<style>
/* Product Detail Page Styles */
.container-fluid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Product Image Container */
.product-image-container {
    position: relative;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    aspect-ratio: 1;
    max-height: 500px;
}

.image-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.product-image {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.02);
}

/* Product Details */
.product-details {
    padding: 20px 0;
}

.product-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

/* Price Section */
.price-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 2rem;
}

.current-price {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.price-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    font-weight: 500;
}

.price-value {
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
}

/* Size Section */
.size-section {
    margin-bottom: 2rem;
}

.section-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 1rem;
}

.size-select {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.size-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Description Section */
.description-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
}

.description-text {
    color: #4a5568;
    line-height: 1.6;
    font-size: 1rem;
}

/* Order Form */
.order-form {
    background: #f8fafc;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e2e8f0;
}

/* Quantity Controls */
.quantity-section {
    margin-bottom: 1.5rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    width: fit-content;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.qty-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 12px 16px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    min-width: 44px;
}

.qty-btn:hover {
    background: #5a67d8;
}

.qty-input {
    border: none;
    text-align: center;
    padding: 12px 8px;
    font-size: 1rem;
    font-weight: 600;
    width: 80px;
    background: white;
}

.qty-input:focus {
    outline: none;
}

/* Total Price Card */
.total-price-section {
    margin-bottom: 1.5rem;
}

.total-price-card {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    border-radius: 8px;
    padding: 16px 20px;
}

.total-price-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-label {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    font-size: 1rem;
}

.total-value {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 700;
}

/* Notes Section */
.notes-section {
    margin-bottom: 1.5rem;
}

.notes-input {
    width: 100%;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 1rem;
    resize: vertical;
    transition: border-color 0.3s ease;
}

.notes-input:focus {
    outline: none;
    border-color: #667eea;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-cart, .btn-buy {
    flex: 1;
    min-width: 200px;
    padding: 16px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-cart {
    background: #667eea;
    color: white;
}

.btn-cart:hover {
    background: #5a67d8;
    transform: translateY(-2px);
}

.btn-buy {
    background: #e53e3e;
    color: white;
}

.btn-buy:hover {
    background: #c53030;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 992px) {
    .container-fluid {
        padding: 0 10px;
    }

    .product-image-container {
        max-height: 400px;
        margin-bottom: 2rem;
    }

    .product-details {
        padding: 0;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0 5px;
    }

    .product-title {
        font-size: 1.6rem;
        margin-bottom: 1rem;
    }

    .price-section {
        padding: 15px;
        margin-bottom: 1.5rem;
    }

    .price-value {
        font-size: 1.5rem;
    }

    .order-form {
        padding: 20px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }

    .btn-cart, .btn-buy {
        min-width: 100%;
        padding: 14px 20px;
        font-size: 0.95rem;
    }

    .quantity-controls {
        width: 100%;
        justify-content: center;
    }

    .qty-input {
        width: 100px;
    }
}

@media (max-width: 576px) {
    .product-title {
        font-size: 1.4rem;
    }

    .price-value {
        font-size: 1.3rem;
    }

    .total-value {
        font-size: 1.2rem;
    }

    .order-form {
        padding: 15px;
    }

    .section-label {
        font-size: 0.9rem;
    }

    .size-select {
        padding: 10px 12px;
        font-size: 0.9rem;
    }

    .notes-input {
        padding: 10px 12px;
        font-size: 0.9rem;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="product-image-container">
                <div class="image-wrapper">
                    <img src="{{ asset('storage/' . ($produk->Img ?? 'assets/images/poster1.jpeg')) }}"
                         alt="{{ $produk->NamaProduk }}"
                         class="product-image"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-details">
                <h1 class="product-title">{{ $produk->NamaProduk }}</h1>

                <!-- Price Display -->
                <div class="price-section mb-4">
                    <div class="current-price">
                        <span class="price-label">Harga Satuan:</span>
                        <span class="price-value" id="displayPrice">
                            Rp {{ isset($produk->sizes[0]) ? number_format($produk->sizes[0]->pivot->harga, 0, ',', '.') : number_format($produk->custom_harga, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Size Selection -->
                <div class="size-section mb-4">
                    <label class="section-label">Pilih Ukuran</label>
                    <select class="form-select size-select" id="sizeSelect" name="size_id" onchange="updatePrice()">
                        @foreach($produk->sizes as $size)
                            <option value="{{ $size->id_ukuran }}" data-harga="{{ $size->pivot->harga }}">
                                {{ $size->nama }} ({{ $size->panjang }} x {{ $size->lebar }} cm) - Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                        <option value="custom" data-harga="{{ $produk->custom_harga }}">Custom Ukuran - Rp {{ number_format($produk->custom_harga, 0, ',', '.') }}</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="description-section mb-4">
                    <h5 class="section-title">Deskripsi Produk</h5>
                    <p class="description-text">{{ $produk->deskripsi ?: 'Produk berkualitas tinggi dengan hasil cetak yang memukau. Tersedia dalam berbagai ukuran dan finishing yang dapat disesuaikan dengan kebutuhan Anda.' }}</p>
                </div>

                <!-- Order Form -->
                <div class="order-form">
                    <form id="orderForm">
                        @csrf
                        <input type="hidden" name="id" value="{{ $produk->IdRoster }}">
                        <input type="hidden" name="nama" value="{{ $produk->NamaProduk }}">
                        <input type="hidden" name="harga" id="product_price" value="{{ isset($produk->sizes[0]) ? $produk->sizes[0]->pivot->harga : $produk->custom_harga }}">
                        <input type="hidden" name="img" value="{{ $produk->Img }}">

                        <!-- Quantity Section -->
                        <div class="quantity-section mb-4">
                            <label class="section-label">Jumlah</label>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="decreaseQuantity()">-</button>
                                <input type="number" class="qty-input" name="quantity" id="quantity" value="1" min="1" onchange="updateTotalPrice()">
                                <button type="button" class="qty-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>

                        <!-- Total Price Display -->
                        <div class="total-price-section mb-4">
                            <div class="total-price-card">
                                <div class="total-price-content">
                                    <span class="total-label">Total Harga:</span>
                                    <span class="total-value" id="totalPrice">Rp {{ number_format(isset($produk->sizes[0]) ? $produk->sizes[0]->pivot->harga : $produk->custom_harga, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="notes-section mb-4">
                            <label class="section-label">Catatan (Opsional)</label>
                            <textarea class="notes-input" name="notes" rows="3" placeholder="Tambahkan catatan khusus untuk pesanan Anda..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button type="button" class="btn-cart" onclick="addToCart()">
                                <i class="bi bi-cart-plus"></i>
                                <span>Tambah ke Keranjang</span>
                            </button>
                            <button type="button" class="btn-buy" onclick="buyNow()">
                                <i class="bi bi-lightning"></i>
                                <span>Beli Sekarang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    updateTotalPrice();
}
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
    updateTotalPrice();
});

function increaseQuantity() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
    updateTotalPrice();
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateTotalPrice();
    }
}

function updateTotalPrice() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const price = parseInt(document.getElementById('product_price').value) || 0;
    const total = quantity * price;

    document.getElementById('totalPrice').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function addToCart() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    const sizeSelect = document.getElementById('sizeSelect');
    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
    const ukuranValue = sizeSelect.value;
    const ukuranLabel = selectedOption.text.split(' - ')[0]; // Get label without price
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const price = parseInt(document.getElementById('product_price').value) || 0;
    const subtotal = quantity * price;
    
    formData.append('id', '{{ $produk->IdRoster }}');
    formData.append('ukuran', ukuranValue);
    formData.append('ukuran_label', ukuranLabel);
    formData.append('subtotal', subtotal);
    
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
    const sizeSelect = document.getElementById('sizeSelect');
    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
    const ukuranValue = sizeSelect.value;
    const ukuranLabel = selectedOption.text.split(' - ')[0]; // Get label without price
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const price = parseInt(document.getElementById('product_price').value) || 0;
    const subtotal = quantity * price;
    
    formData.append('id', '{{ $produk->IdRoster }}');
    formData.append('ukuran', ukuranValue);
    formData.append('ukuran_label', ukuranLabel);
    formData.append('subtotal', subtotal);
    
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
