
@extends('admin.layouts.template')

@section('page_title')
    CIME | Edit Transaksi Manual
@endsection

@section('content')
<style>
    /* Fix form layout issues */
    .form-section {
        margin-bottom: 1.5rem;
    }
    
    .form-row {
        margin-bottom: 1rem;
    }
    
    .form-group {
        margin-bottom: 0.75rem;
    }
    
    .product-row {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 1px solid #e9ecef;
    }
    
    .product-row .row {
        align-items: end;
    }
    
    .btn-remove-product {
        margin-top: 1.5rem;
    }
    
    .subtotal-display {
        background-color: #e9ecef;
        font-weight: 600;
    }
    
    /* Fix input alignment */
    .form-control, .form-select {
        height: 38px;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }
    
    /* Fix card spacing */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid #dee2e6;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    /* Fix textarea height */
    textarea.form-control {
        height: auto;
        min-height: 80px;
    }
    
    /* Fix sidebar overlap issue */
    .container-xxl {
        margin-left: 0 !important;
        padding-left: 1rem !important;
        width: 100% !important;
        max-width: none !important;
    }
    
    /* Ensure content is not hidden behind sidebar */
    .layout-page {
        margin-left: 280px !important;
        width: calc(100% - 280px) !important;
        padding-left: 0.5rem !important;
    }
    
    /* Fix form container spacing */
    .container-xxl.flex-grow-1.container-p-y {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        margin-left: 0 !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Transaksi /</span> Edit Transaksi Manual
    </h4>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Edit Transaksi: {{ $transaksi->IdTransaksi }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('transaksi.update', $transaksi->IdTransaksi) }}" method="POST" id="transactionForm">
                @csrf
                @method('PUT')
                
                <!-- Basic Transaction Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="IdTransaksi" class="form-label">ID Transaksi (Invoice)</label>
                        <input type="text" class="form-control" id="IdTransaksi" value="{{ $transaksi->IdTransaksi }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="id_customer" class="form-label">Customer</label>
                        <select class="form-select @error('id_customer') is-invalid @enderror" id="id_customer" name="id_customer" required>
                            <option value="">Pilih Customer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('id_customer', $transaksi->id_customer) == $user->id ? 'selected' : '' }}>
                                    {{ $user->f_name }} ({{ $user->username }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_customer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="shipping_method" class="form-label">Metode Pembelian</label>
                        <select class="form-select @error('shipping_method') is-invalid @enderror" id="shipping_method" name="shipping_method" required>
                            <option value="">Pilih Metode</option>
                            <option value="Online" {{ old('shipping_method', $transaksi->shipping_method) == 'Online' ? 'selected' : '' }}>Online (E-commerce)</option>
                            <option value="Offline" {{ old('shipping_method', $transaksi->shipping_method) == 'Offline' ? 'selected' : '' }}>Offline (Toko)</option>
                        </select>
                        <small class="text-muted">Otomatis diset ke "Offline" untuk transaksi manual</small>
                        @error('shipping_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="delivery_method" class="form-label">Metode Pengiriman</label>
                        <select class="form-select @error('delivery_method') is-invalid @enderror" id="delivery_method" name="delivery_method" required>
                            <option value="">Pilih Metode Pengiriman</option>
                            <option value="Pickup" {{ old('delivery_method', $transaksi->delivery_method) == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                            <option value="Delivery" {{ old('delivery_method', $transaksi->delivery_method) == 'Delivery' ? 'selected' : '' }}>Delivery</option>
                        </select>
                        @error('delivery_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Shipping Type -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="shipping_type" class="form-label">Jenis Pengiriman</label>
                        <select class="form-select @error('shipping_type') is-invalid @enderror" id="shipping_type" name="shipping_type" required>
                            <option value="">Pilih Jenis Pengiriman</option>
                            <option value="Free Ongkir" {{ old('shipping_type', $transaksi->shipping_type) == 'Free Ongkir' ? 'selected' : '' }}>Free Ongkir (Qty > 100)</option>
                            <option value="Ongkir" {{ old('shipping_type', $transaksi->shipping_type) == 'Ongkir' ? 'selected' : '' }}>Ongkir (Qty ≤ 100)</option>
                        </select>
                        @error('shipping_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="address_id" class="form-label">Pilih Alamat Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('address_id') is-invalid @enderror" id="address_id" name="address_id" required>
                            <option value="">Pilih Alamat</option>
                            @foreach($customerAddresses as $address)
                                <option value="{{ $address->id }}" 
                                        {{ old('address_id', $transaksi->address_id) == $address->id ? 'selected' : '' }}>
                                    {{ $address->label ?? 'Alamat' }} - {{ $address->full_address }}, {{ $address->city }}, {{ $address->postal_code }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih alamat dari daftar alamat customer</small>
                        @error('address_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat Pengiriman (Preview)</label>
                        <div class="form-control-plaintext" id="address_preview" style="min-height: 80px; background-color: #f8f9fa; padding: 0.5rem; border-radius: 0.375rem;">
                            @if($transaksi->address)
                                {{ $transaksi->address->full_address }}, {{ $transaksi->address->city }}, {{ $transaksi->address->postal_code }}
                            @else
                                Pilih alamat customer untuk melihat preview
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Produk
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="products-container">
                            @foreach($transaksi->detailTransaksi as $index => $detail)
                            <div class="product-row row mb-3" data-row="{{ $index }}">
                                <div class="col-md-3">
                                    <label class="form-label">Produk</label>
                                    <select class="form-select product-select" name="products[{{ $index }}][product_id]" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $product)
                                                                                         <option value="{{ $product->IdRoster }}" 
                                                     data-sizes="{{ $product->sizes->toJson() }}"
                                                     {{ $detail->IdRoster == $product->IdRoster ? 'selected' : '' }}>
                                                {{ $product->jenisRoster->JenisBarang ?? 'N/A' }} - {{ $product->tipeRoster->namaTipe ?? 'N/A' }} - {{ $product->motif->nama_motif ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Ukuran</label>
                                    <select class="form-select size-select" name="products[{{ $index }}][size_id]" required>
                                        <option value="">Pilih Ukuran</option>
                                        @if($detail->produk)
                                            @foreach($detail->produk->sizes as $size)
                                                <option value="{{ $size->id_ukuran }}" 
                                                        data-price="{{ $size->pivot->harga }}"
                                                        {{ $detail->id_ukuran == $size->id_ukuran ? 'selected' : '' }}>
                                                    {{ $size->nama }} ({{ $size->panjang }}×{{ $size->lebar }} cm) - Rp {{ number_format($size->pivot->harga, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty</label>
                                    <input type="number" class="form-control qty-input" name="products[{{ $index }}][qty]" 
                                           min="1" value="{{ $detail->QtyProduk }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Harga Satuan</label>
                                    <input type="number" class="form-control price-input" name="products[{{ $index }}][price]" 
                                           min="0" step="1000" value="{{ $detail->SubTotal / $detail->QtyProduk }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" class="form-control subtotal-display" value="{{ number_format($detail->SubTotal, 0, ',', '.') }}" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-product" {{ $index == 0 ? 'style=display:none;' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-product">
                            <i class="fas fa-plus me-1"></i>Tambah Produk
                        </button>
                    </div>
                </div>

                                 <!-- Shipping Cost -->
                 <div class="row mb-3">
                     <div class="col-md-6">
                         <label for="ongkir" class="form-label">Biaya Ongkir</label>
                         <div class="input-group">
                             <span class="input-group-text">Rp</span>
                             <input type="text" class="form-control @error('ongkir') is-invalid @enderror"
                                 id="ongkir" name="ongkir" 
                                 placeholder="Masukkan biaya ongkir (contoh: 50,000)" 
                                 value="{{ number_format(old('ongkir', $transaksi->ongkir ?? 0), 0, ',', '.') }}" required
                                 oninput="formatNumber(this)" onblur="validateNumber(this)">
                         </div>
                         <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                         @error('ongkir')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                 </div>

                 <!-- Payment Info -->
                 <div class="row mb-3">
                     <div class="col-md-6">
                         <label for="GrandTotal" class="form-label">Total Grand</label>
                         <div class="input-group">
                             <span class="input-group-text">Rp</span>
                             <input type="text" class="form-control @error('GrandTotal') is-invalid @enderror"
                                 id="GrandTotal" name="GrandTotal" 
                                 placeholder="Masukkan total (contoh: 1,260,000)" 
                                 value="{{ number_format(old('GrandTotal', $transaksi->GrandTotal), 0, ',', '.') }}" required
                                 oninput="formatNumber(this)" onblur="validateNumber(this)">
                             <input type="hidden" name="GrandTotal_raw" id="GrandTotal_raw" value="{{ old('GrandTotal', $transaksi->GrandTotal) }}">
                         </div>
                         <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                         @error('GrandTotal')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                     <div class="col-md-6">
                         <label for="Bayar" class="form-label">Jumlah Dibayar</label>
                         <div class="input-group">
                             <span class="input-group-text">Rp</span>
                             <input type="text" class="form-control @error('Bayar') is-invalid @enderror"
                                 id="Bayar" name="Bayar" 
                                 placeholder="Masukkan jumlah dibayar (contoh: 1,300,000)" 
                                 value="{{ number_format(old('Bayar', $transaksi->Bayar), 0, ',', '.') }}" required
                                 oninput="formatNumber(this)" onblur="validateNumber(this)">
                             <input type="hidden" name="Bayar_raw" id="Bayar_raw" value="{{ old('Bayar', $transaksi->Bayar) }}">
                         </div>
                         <small class="text-muted">Masukkan angka tanpa koma, akan otomatis diformat</small>
                         @error('Bayar')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                 </div>

                <!-- Payment Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="StatusPembayaran" class="form-label">Status Pembayaran</label>
                        <select class="form-select @error('StatusPembayaran') is-invalid @enderror" id="StatusPembayaran" name="StatusPembayaran" required>
                            <option value="">Pilih Status</option>
                            <option value="Lunas" {{ old('StatusPembayaran', $transaksi->StatusPembayaran) == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="Hutang" {{ old('StatusPembayaran', $transaksi->StatusPembayaran) == 'Hutang' ? 'selected' : '' }}>Hutang</option>
                            <option value="Transfer" {{ old('StatusPembayaran', $transaksi->StatusPembayaran) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                        @error('StatusPembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Order Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="StatusPesanan" class="form-label">Status Pesanan</label>
                        <select class="form-select @error('StatusPesanan') is-invalid @enderror" id="StatusPesanan" name="StatusPesanan" required>
                            <option value="">Pilih Status</option>
                            <option value="Pending" {{ old('StatusPesanan', $transaksi->StatusPesanan) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="MENUNGGU KONFIRMASI" {{ old('StatusPesanan', $transaksi->StatusPesanan) == 'MENUNGGU KONFIRMASI' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                            <option value="Diterima" {{ old('StatusPesanan', $transaksi->StatusPesanan) == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="Ditolak" {{ old('StatusPesanan', $transaksi->StatusPesanan) == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        @error('StatusPesanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $transaksi->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('alltransaksi') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize product row count from PHP
let productRowCount = {!! count($transaksi->detailTransaksi) !!};

// Add new product row
document.addEventListener('DOMContentLoaded', function() {
    const addProductBtn = document.getElementById('add-product');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            const container = document.getElementById('products-container');
            const templateRow = document.querySelector('.product-row');
            if (container && templateRow) {
                const newRow = templateRow.cloneNode(true);
                
                // Update row index
                newRow.dataset.row = productRowCount;
                newRow.querySelectorAll('select, input').forEach(element => {
                    if (element.name) {
                        element.name = element.name.replace(/\[\d+\]/, `[${productRowCount}]`);
                    }
                    element.value = '';
                });
                
                // Show remove button
                const removeBtn = newRow.querySelector('.remove-product');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }
                
                container.appendChild(newRow);
                productRowCount++;
                
                // Reattach event listeners
                attachProductRowListeners(newRow);
            }
        });
    }

    // Remove product row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product') || e.target.closest('.remove-product')) {
            const row = e.target.closest('.product-row');
            if (row && document.querySelectorAll('.product-row').length > 1) {
                row.remove();
                calculateTotal();
            }
        }
    });

    // Attach listeners to existing rows
    document.querySelectorAll('.product-row').forEach(row => {
        attachProductRowListeners(row);
    });

    // Auto-calculate total when form loads
    calculateTotal();
    
    // Add customer selection listener for address loading
    const customerSelect = document.getElementById('id_customer');
    const addressSelect = document.getElementById('address_id');
    const addressPreview = document.getElementById('address_preview');
    
    if (customerSelect && addressSelect && addressPreview) {
        // Load addresses for the current customer on page load
        if (customerSelect.value) {
            loadCustomerAddresses(customerSelect.value, '{{ $transaksi->address_id }}');
        }
        
        customerSelect.addEventListener('change', function() {
            const customerId = this.value;
            loadCustomerAddresses(customerId);
        });
        
        // Add address selection listener
        addressSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const addressText = selectedOption.textContent;
                addressPreview.textContent = addressText.split(' - ')[1] || 'Alamat tidak tersedia';
            } else {
                addressPreview.textContent = 'Pilih alamat customer untuk melihat preview';
            }
        });
    }
});

// Attach event listeners to product row
function attachProductRowListeners(row) {
    const productSelect = row.querySelector('.product-select');
    const sizeSelect = row.querySelector('.size-select');
    const qtyInput = row.querySelector('.qty-input');
    const priceInput = row.querySelector('.price-input');
    
    if (productSelect) {
        // Product selection
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.sizes) {
                try {
                    const sizes = JSON.parse(selectedOption.dataset.sizes);
                    
                    // Clear and populate size options
                    if (sizeSelect) {
                        sizeSelect.innerHTML = '<option value="">Pilih Ukuran</option>';
                        sizes.forEach(size => {
                            const option = document.createElement('option');
                            option.value = size.id_ukuran;
                            option.textContent = `${size.nama} (${size.panjang}×${size.lebar} cm) - Rp ${parseInt(size.pivot.harga).toLocaleString('id-ID')}`;
                            option.dataset.price = size.pivot.harga;
                            sizeSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error parsing sizes:', error);
                }
            }
        });
    }
    
    if (sizeSelect) {
        // Size selection
        sizeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.price && priceInput) {
                priceInput.value = selectedOption.dataset.price;
                calculateSubtotal(row);
            }
        });
    }
    
    // Quantity and price changes
    if (qtyInput) {
        qtyInput.addEventListener('input', () => calculateSubtotal(row));
    }
    if (priceInput) {
        priceInput.addEventListener('input', () => calculateSubtotal(row));
    }
}

// Calculate subtotal for a row
function calculateSubtotal(row) {
    const qtyInput = row.querySelector('.qty-input');
    const priceInput = row.querySelector('.price-input');
    const subtotalDisplay = row.querySelector('.subtotal-display');
    
    if (qtyInput && priceInput && subtotalDisplay) {
        const qty = parseInt(qtyInput.value) || 0;
        const price = parseInt(priceInput.value) || 0;
        const subtotal = qty * price;
        
        subtotalDisplay.value = subtotal.toLocaleString('id-ID');
        calculateTotal();
    }
}

// Calculate total grand
function calculateTotal() {
    let total = 0;
    let totalQty = 0;
    
    document.querySelectorAll('.subtotal-display').forEach(display => {
        const value = display.value.replace(/[^\d]/g, '') || '0';
        total += parseInt(value);
    });
    
    // Calculate total quantity
    document.querySelectorAll('.qty-input').forEach(qtyInput => {
        totalQty += parseInt(qtyInput.value) || 0;
    });
    
    // Auto-update shipping type based on quantity
    const shippingTypeSelect = document.getElementById('shipping_type');
    const ongkirInput = document.getElementById('ongkir');
    
    if (shippingTypeSelect && ongkirInput) {
        if (totalQty > 100) {
            shippingTypeSelect.value = 'Free Ongkir';
            ongkirInput.value = '0';
            ongkirInput.disabled = true;
        } else {
            shippingTypeSelect.value = 'Ongkir';
            ongkirInput.disabled = false;
        }
    }
    
    // Add shipping cost to total
    if (ongkirInput) {
        const ongkirCost = parseInt(ongkirInput.value.replace(/[^\d]/g, '')) || 0;
        total += ongkirCost;
    }
    
    const grandTotalInput = document.getElementById('GrandTotal');
    if (grandTotalInput) {
        grandTotalInput.value = total.toLocaleString('id-ID');
    }
}

function loadCustomerAddresses(customerId, currentAddressId = null) {
    const addressSelect = document.getElementById('address_id');
    const addressPreview = document.getElementById('address_preview');
    
    if (addressSelect && addressPreview) {
        // Clear address dropdown and preview
        addressSelect.innerHTML = '<option value="">Pilih Alamat</option>';
        addressPreview.textContent = 'Pilih alamat customer untuk melihat preview';
        
        if (customerId) {
            // Fetch customer addresses
            fetch(`/admin/get-customer-addresses/${customerId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(addresses => {
                    if (addresses && addresses.length > 0) {
                        addresses.forEach(address => {
                            const option = document.createElement('option');
                            option.value = address.id;
                            option.textContent = `${address.label || 'Alamat'} - ${address.full_address}, ${address.city}, ${address.postal_code}`;
                            
                            // Pre-select the current address if provided
                            if (currentAddressId && address.id == currentAddressId) {
                                option.selected = true;
                                // Update preview with selected address
                                addressPreview.textContent = `${address.full_address}, ${address.city}, ${address.postal_code}`;
                            }
                            
                            addressSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Tidak ada alamat tersimpan';
                        addressSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching addresses:', error);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Error loading addresses';
                    addressSelect.appendChild(option);
                });
        }
    }
}
</script>

<script>
function formatNumber(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/[^\d]/g, '');
    
    // Store raw value in hidden field first
    const rawFieldId = input.id + '_raw';
    const rawField = document.getElementById(rawFieldId);
    if (rawField) {
        rawField.value = value;
    }
    
    // Format with commas for display
    if (value !== '') {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    // Update display
    input.value = value;
}

function validateNumber(input) {
    let value = input.value.replace(/[^\d]/g, '');
    
    if (value === '') {
        input.setCustomValidity('Field ini harus diisi');
    } else if (parseInt(value) < 0) {
        input.setCustomValidity('Nilai tidak boleh negatif');
    } else {
        input.setCustomValidity('');
    }
}

// Update form submission to use raw values
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#transactionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const grandTotalInput = document.getElementById('GrandTotal');
            const grandTotalRaw = document.getElementById('GrandTotal_raw');
            const bayarInput = document.getElementById('Bayar');
            const bayarRaw = document.getElementById('Bayar_raw');
            const ongkirInput = document.getElementById('ongkir');
            
            // Set the raw values to the main inputs before submission
            if (grandTotalRaw && grandTotalRaw.value && grandTotalRaw.value.trim() !== '') {
                grandTotalInput.value = grandTotalRaw.value;
            } else if (grandTotalInput) {
                const formattedValue = grandTotalInput.value.replace(/[^\d]/g, '');
                if (formattedValue) {
                    grandTotalInput.value = formattedValue;
                }
            }
            
            if (bayarRaw && bayarRaw.value && bayarRaw.value.trim() !== '') {
                bayarInput.value = bayarRaw.value;
            } else if (bayarInput) {
                const formattedValue = bayarInput.value.replace(/[^\d]/g, '');
                if (formattedValue) {
                    bayarInput.value = formattedValue;
                }
            }
            
            // Clean ongkir value
            if (ongkirInput) {
                const ongkirValue = ongkirInput.value.replace(/[^\d]/g, '');
                ongkirInput.value = ongkirValue;
            }
        });
    }
});
</script>
@endpush

@endsection
