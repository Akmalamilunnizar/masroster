<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title> CIME | Toko Online</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />
  <link rel="stylesheet" href="{{ asset('css/order.css') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .shipping-method.active {
      background-color: #1D1E94 !important;
      color: white !important;
    }
    .shipping-option.active {
      background-color: #1D1E94 !important;
      color: white !important;
    }
  </style>
</head>

<body>

<!-- Stepper -->
<div class="stepper-wrapper">
  <div class="stepper-item completed" id="step-1">
    <div class="step-counter">1</div>
    <div class="step-name">Cart</div>
  </div>
  <div class="stepper-item completed" id="step-2">
    <div class="step-counter">2</div>
    <div class="step-name">Details</div>
  </div>
  <div class="stepper-item active" id="step-3">
    <div class="step-counter">3</div>
    <div class="step-name">Shipping</div>
  </div>
  <div class="stepper-item" id="step-4">
    <div class="step-counter">4</div>
    <div class="step-name">Payment</div>
  </div>
  <div class="stepper-item" id="step-5">
    <div class="step-counter">5</div>
    <div class="step-name">Review</div>
  </div>
</div>

<div class="container py-4">
  <div class="row justify-content-center">
    <!-- Shipping Selection -->
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Pilih Pengiriman</h5>
          <div class="mb-3">
            <button class="btn btn-outline-secondary me-2 shipping-method" data-method="kurir">Kurir</button>
            <button class="btn btn-outline-secondary shipping-method" data-method="pickup">Self Pickup</button>
          </div>

          <!-- Kurir Options -->
          <div id="kurirOptions" class="shipping-options" style="display: none;">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead class="table-light">
                  <tr>
                    <th>JENIS PENGIRIMAN</th>
                    <th>DURASI</th>
                    <th>BIAYA</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <button class="btn btn-outline-secondary btn-sm shipping-option" data-cost="20000">Reguler</button>
                    </td>
                    <td>2-3 hari</td>
                    <td>Rp 20.000</td>
                  </tr>
                  <tr>
                    <td>
                      <button class="btn btn-outline-secondary btn-sm shipping-option" data-cost="35000">Express</button>
                    </td>
                    <td>1 hari</td>
                    <td>Rp 35.000</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Self Pickup Option -->
          <div id="pickupOptions" class="shipping-options" style="display: none;">
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Anda dapat mengambil pesanan di toko kami
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('details') }}" class="btn btn-secondary">Back</a>
            <button id="proceedButton" class="btn btn-danger" disabled>
              <i class="bi bi-credit-card me-2"></i> Proses Checkout
              @if($selectedAddress)
                {{ $selectedAddress->full_address }}
              @endif
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-4">
      <div class="card shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
        <h5 class="fw-bold mb-3">Order summary</h5>

        @php $total = 0; $total_berat = 0; @endphp

        @if(session('cart') && count(session('cart')) > 0)
            @php $cart = session('cart', []); @endphp
            @foreach ($cart as $id => $details)
                @php 
                    $subtotal = $details['harga'] * $details['quantity'];
                    $total += $subtotal;
                    $total_berat += ($details['berat'] ?? 0) * $details['quantity'];   
                @endphp
                <div class="d-flex mb-3 border-bottom pb-2">
                    @php
                        $imagePath = $details['img'] ?? 'assets/images/poster1.jpeg';
                        $fullPath = asset('storage/' . $imagePath);
                    @endphp
                    <img src="{{ $fullPath }}" 
                         alt="{{ $details['nama'] }}" 
                         style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px;"
                         class="me-3"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $details['nama'] }}</div>
                        <div class="text-primary">Rp {{ number_format($details['harga'], 0, ',', '.') }} 
                            <span class="text-muted">× {{ $details['quantity'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Totals -->
            <div class="mt-4">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                
                <div class="d-flex justify-content-between" id="shippingCost" style="display: none;">
                    <span class="text-muted">Biaya Pengiriman:</span>
                    <span class="fw-semibold">Rp <span id="shippingAmount">0</span></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total:</span>
                    <span class="fw-bold" id="grandTotal">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                  {{-- Debug: --}}
  Selected Address ID: {{ session('selected_address_id') }}

            </div>
        @else
            <div class="alert alert-warning">
                Keranjang kosong. Silakan pilih produk terlebih dahulu.
                <a href="{{ route('tokodashboard') }}" class="btn btn-primary btn-sm ms-2">Kembali ke Katalog</a>
            </div>
        @endif
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const shippingMethods = document.querySelectorAll('.shipping-method');
  const shippingOptions = document.querySelectorAll('.shipping-option');
  const proceedButton = document.getElementById('proceedButton');
  const shippingCost = document.getElementById('shippingCost');
  const shippingAmount = document.getElementById('shippingAmount');
  const grandTotal = document.getElementById('grandTotal');
  
  let selectedMethod = null;
  let selectedOption = null;
  const subtotal = {{ $total }};

  // Handle shipping method selection
  shippingMethods.forEach(method => {
    method.addEventListener('click', function() {
      // Remove active class from all methods
      shippingMethods.forEach(m => m.classList.remove('active'));
      // Add active class to selected method
      this.classList.add('active');
      
      selectedMethod = this.dataset.method;
      
      // Show/hide appropriate options
      document.getElementById('kurirOptions').style.display = 
        selectedMethod === 'kurir' ? 'block' : 'none';
      document.getElementById('pickupOptions').style.display = 
        selectedMethod === 'pickup' ? 'block' : 'none';
      
      // Reset shipping cost for pickup
      if (selectedMethod === 'pickup') {
        shippingCost.style.display = 'none';
        shippingAmount.textContent = '0';
        grandTotal.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        selectedOption = null;
      }
      
      updateProceedButton();
    });
  });

  // Handle shipping option selection
  shippingOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Remove active class from all options
      shippingOptions.forEach(o => o.classList.remove('active'));
      // Add active class to selected option
      this.classList.add('active');
      
      selectedOption = this.dataset.cost;
      const shippingCostValue = parseInt(selectedOption);
      
      // Update shipping cost display
      shippingCost.style.display = 'flex';
      shippingAmount.textContent = shippingCostValue.toLocaleString('id-ID');
      grandTotal.textContent = `Rp ${(subtotal + shippingCostValue).toLocaleString('id-ID')}`;
      
      updateProceedButton();
    });
  });

  function updateProceedButton() {
    proceedButton.disabled = !selectedMethod || 
      (selectedMethod === 'kurir' && !selectedOption);
  }

  // Handle proceed button click
  proceedButton.addEventListener('click', function() {
    const shippingData = {
      method: selectedMethod,
      type: selectedMethod === 'kurir' ? (selectedOption == 20000 ? 'Reguler' : 'Express') : null,
      cost: selectedMethod === 'kurir' ? parseInt(selectedOption) : 0,
      address_id: '{{ session('selected_address_id') }}'
    };
    
    // Store shipping data in session
    fetch('{{ route("save.shipping") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify(shippingData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = '{{ route("payment") }}';
      }
    });
  });
});
</script>

</body>
</html>
