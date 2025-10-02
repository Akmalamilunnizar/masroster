@if(!Auth::check())
    <script>
        window.location.href = '{{ route("login") }}';
    </script>
@else
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
  <title>CIME | Review Pesanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />
  <link rel="stylesheet" href="{{ asset('css/order.css') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Check if user is authenticated
      const isAuthenticated = document.querySelector('meta[name="user-authenticated"]').content === 'true';
      if (!isAuthenticated) {
        window.location.href = '{{ route("login") }}';
        return;
      }

      const form = document.getElementById('confirmOrderForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          console.log('Form submitted to:', this.action);
          
          // Show loading state
          const submitButton = this.querySelector('button[type="submit"]');
          const originalText = submitButton.innerHTML;
          submitButton.disabled = true;
          submitButton.innerHTML = 'Memproses...';

          fetch(this.action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: new FormData(this)
          })
          .then(response => {
            console.log('Response status:', response.status);
            if (response.status === 401) {
              // Redirect to login if unauthorized
              window.location.href = '{{ route("login") }}';
              return;
            }
            return response.json().then(data => {
              if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                  ${data.message}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertDiv);

                // Redirect after a short delay
                setTimeout(() => {
                  window.location.href = data.redirect;
                }, 1500);
              } else {
                throw new Error(data.error || 'Terjadi kesalahan');
              }
            });
          })
          .catch(error => {
            console.error('Error:', error);
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
              ${error.message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);
          });
        });
      }
    });
  </script>
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
    <div class="stepper-item completed" id="step-3">
      <div class="step-counter">3</div>
      <div class="step-name">Shipping</div>
    </div>
    <div class="stepper-item completed" id="step-4">
      <div class="step-counter">4</div>
      <div class="step-name">Payment</div>
    </div>
    <div class="stepper-item active" id="step-5">
      <div class="step-counter">5</div>
      <div class="step-name">Review</div>
    </div>
  </div>

  <div class="container py-4">
    <div class="row">
      <!-- Daftar Produk (Kiri) -->
      <div class="col-lg-8">
        <h4 class="fw-bold mb-4">Review Produk</h4>
        @if($cart && count($cart) > 0)
          @foreach($cart as $id => $details)
            <div class="border-bottom pb-4 mb-4 d-flex">
              <div class="me-3">
                @php
                  $imagePath = $details['img'] ?? 'default.jpg';
                  $fullPath = asset('storage/' . $imagePath);
                @endphp
                <img src="{{ $fullPath }}" alt="{{ $details['nama'] }}" style="width: 220px; height: 220px; object-fit: cover; border-radius: 10px;">
              </div>
              <div class="flex-grow-1">
                <h5 class="mb-1" style="font-size: 20px; font-weight: bold;">{{ $details['nama'] }}</h5>
                @if(isset($details['design_file']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">File: <span class="text-danger">{{ basename($details['design_file']) }}</span></div>
                @endif
                @if(isset($details['print_option']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">Cetak: {{ $details['print_option'] }}</div>
                @endif
                @if(isset($details['notes']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">Catatan: {{ $details['notes'] }}</div>
                @endif
                <div><strong>Ukuran:</strong> {{ $details['ukuran_label'] ?? ($details['ukuran'] ?? 'Ukuran tidak tersedia') }}</div>
                <div class="fw-bold text-primary mt-2" style="font-size: 24px;">Rp {{ number_format($details['harga'] * $details['quantity'], 0, ',', '.') }}</div>
              </div>
              <div class="text-center ms-4">
                <label class="fw-bold">Jumlah</label>
                <div class="form-control text-center" style="width: 100px; margin: auto;">{{ $details['quantity'] }}</div>
              </div>
            </div>
          @endforeach
        @else
          <div class="alert alert-info">Keranjang kosong. <a href="{{ route('tokodashboard') }}">Kembali ke Katalog</a></div>
        @endif
      </div>

      <!-- Ringkasan (Kanan) -->
      <div class="col-lg-4">
        <div class="card shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
          <h6 class="text-muted mb-2">Subtotal</h6>
          <h4 class="fw-bold">Rp {{ number_format($subtotal ?? 0, 0, ',', '.') }}</h4>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-muted">Biaya Pengiriman:</span>
            <span class="fw-semibold">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
          </div>
          <hr />
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Grand Total:</span>
            <span class="fw-bold text-primary">Rp {{ number_format($grandTotal ?? 0, 0, ',', '.') }}</span>
          </div>
          <div class="mb-3 mt-3">
            <span class="badge bg-primary">Note</span>
            <small class="text-muted">Catatan untuk pesanan</small>
            <div class="form-control mt-2" style="min-height: 100px;">{{ $orderNotes ? $orderNotes : 'Tidak ada catatan' }}</div>
          </div>
          <!-- Informasi Kontak -->
          <div class="mb-3">
            <span class="badge bg-primary">Kontak</span>
            <small class="text-muted">Informasi kontak</small>
            <div class="form-control mt-2">
              <div class="d-flex justify-content-between">
                <span>Nomor Telepon:</span>
                <span class="fw-bold">{{ Auth::user()->nomor_telepon ?? '-' }}</span>
              </div>
            </div>
          </div>
          <!-- Alamat Pengiriman -->
          <div class="mb-3">
            <span class="badge bg-primary">Alamat Pengiriman</span>
            <small class="text-muted">Informasi alamat pengiriman</small>
            <div class="form-control mt-2">
              @if($shippingCost > 0)
                @php
                  $selectedAddressId = session('selected_address_id');
                  $selectedAddress = null;
                  if ($selectedAddressId) {
                      $selectedAddress = \App\Models\Address::find($selectedAddressId);
                  }
                @endphp
                @if($selectedAddress && $selectedAddress->full_address)
                  Alamat Pengiriman: {{ $selectedAddress->full_address }}
                @else
                  <span class="text-danger">Alamat pengiriman belum dipilih! Silakan pilih alamat pada langkah sebelumnya.</span>
                @endif
              @else
                <span class="text-muted">Tidak ada pengiriman (pickup/self pickup)</span>
              @endif
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('payment') }}" class="btn btn-secondary me-2 w-50">Back</a>
            <form action="{{ route('confirm.order') }}" method="POST" class="w-50" id="confirmOrderForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <button type="submit" class="btn btn-danger w-100">Konfirmasi Pesanan</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>


</body>
</html>
@endif
