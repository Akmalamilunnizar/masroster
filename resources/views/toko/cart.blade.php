<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <title> CIME | Toko Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />
    <link rel="stylesheet" href="{{ asset('css/order.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
      .empty-cart-center {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>
  </head>

  <body>

    <!-- Stepper -->
    <div class="stepper-wrapper">
      <div class="stepper-item active" id="step-1">
        <div class="step-counter">1</div>
        <div class="step-name">Cart</div>
      </div>
      <div class="stepper-item" id="step-2">
        <div class="step-counter">2</div>
        <div class="step-name">Details</div>
      </div>
      <div class="stepper-item" id="step-3">
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

    <!-- Form -->
    <div class="container py-4">
      <div class="row">
        <!-- Kiri: Daftar Produk -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>Berhasil!</strong> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
        @endif

        <div class="col-lg-8">
          <h4 class="fw-bold mb-4">Produk</h4>
          @if(session('cart') && count(session('cart')) > 0)
            @php $total = 0; @endphp
            @foreach (session('cart') as $id => $details)
              @php 
                  $itemSubtotal = isset($details['subtotal']) ? $details['subtotal'] : ($details['harga'] * $details['quantity']);
                  $total += $itemSubtotal;
              @endphp
              <div class="border-bottom pb-4 mb-4 d-flex">
                <!-- Gambar -->
                <div class="me-3">
                  @php
                    $imagePath = $details['img'] ?? 'default.jpg';
                    $fullPath = asset('storage/' . $imagePath);
                  @endphp
                  <img src="{{ $fullPath }}" 
                       alt="{{ $details['nama'] }}" 
                       style="width: 220px; height: 220px; object-fit: cover; border-radius: 10px;"
                       loading="lazy">
                </div>

                <!-- Detail Produk -->
                <div class="flex-grow-1">
                  <h5 class="mb-1" style="font-size: 20px; font-weight: bold;">{{ $details['nama'] }}</h5>
                  @if(isset($details['design_file']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">
                    File: <span class="text-danger">{{ basename($details['design_file']) }}</span>
                    <a href="{{ asset('storage/' . $details['design_file']) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                      <i class="bi bi-download"></i> Download
                    </a>
                  </div>
                  @endif
                  @if(isset($details['print_option']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">Cetak: {{ $details['print_option'] }}</div>
                  @endif
                  @if(isset($details['notes']))
                  <div class="text-muted small mb-1" style="font-size: 16px;">Catatan: {{ $details['notes'] }}</div>
                  @endif
                  <div>
                      <strong>Ukuran:</strong> {{ $details['ukuran_label'] ?? ($details['ukuran'] ?? 'Ukuran tidak tersedia') }}
                  </div>

                  <!-- Harga -->
                  <span class="fw-bold text-primary mt-2 item-subtotal" style="font-size: 24px;" data-id="{{ $id }}">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                  <span class="fw-bold text-primary mt-2 item-price d-none" data-id="{{ $id }}" data-price="{{ $details['harga'] }}"></span>
                  
                </div>

                <!-- Jumlah & Hapus -->
                <div class="text-center ms-4">
                  <label class="fw-bold">Jumlah</label>
                  <div class="input-group mb-2" style="width: 100px; margin: auto;">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-minus" data-id="{{ $id }}">-</button>
                    <input type="number" class="form-control form-control-sm text-center item-qty" value="{{ $details['quantity'] }}" min="1" data-id="{{ $id }}" readonly>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-plus" data-id="{{ $id }}">+</button>
                  </div>
                  

                  <!-- Trigger button -->
                  <button type="button" class="btn btn-link text-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $id }}">
                    ✖ Remove
                  </button>

                  <!-- Modal -->
                  <div class="modal fade" id="confirmDeleteModal{{ $id }}" tabindex="-1" aria-labelledby="confirmDeleteLabel{{ $id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content rounded-4 shadow-lg border-0">
                        <div class="modal-header border-0 pb-0">
                          <h5 class="modal-title text-danger" id="confirmDeleteLabel{{ $id }}">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body text-center">
                          <i class="bi bi-trash3 display-4 text-danger mb-3"></i>
                          <p class="fs-5">Apakah Anda yakin ingin menghapus produk ini dari keranjang?</p>
                        </div>
                        <div class="modal-footer border-0 d-flex justify-content-between px-4">
                          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                          <form action="{{ route('cart.remove', $id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger rounded-pill px-4">Hapus</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            @endforeach
          @else
            <div class="empty-cart-center">
                <div class="text-center w-100">
                    <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                    <h3 class="mb-3">Keranjang Belanja Kosong</h3>
                    <p class="text-muted mb-4">Anda belum menambahkan produk ke keranjang belanja.</p>
                    <a href="{{ route('tokodashboard') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali Berbelanja
                    </a>
                </div>
            </div>
          @endif

         </div>

        <!-- Kanan: Ringkasan -->
        @if(session('cart') && count(session('cart')) > 0)
        <div class="col-lg-4">
          <div class="card shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
            <h6 class="text-muted mb-2">Subtotal</h6>
            <h4 class="fw-bold" id="cart-total">Rp {{ number_format($total, 0, ',', '.') }}</h4>
            <hr>
            <form action="{{ route('details') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <span class="badge bg-primary">Note</span> <small class="text-muted">Additional comments</small>
                    <textarea name="notes" class="form-control mt-2 border-danger-subtle" rows="4" placeholder="Tambahkan catatan untuk pesanan Anda..."></textarea>
                </div>
                <button type="submit" class="btn btn-danger w-100 shadow">
                    <i class="bi bi-credit-card me-2"></i> Proses Checkout
                </button>
            </form>
          </div>
        </div>
        @endif
      </div>

      <!-- Button Back -->
      <a href="{{ route('tokodashboard') }}" class="btn-back" style="margin-right: 20px;">Back</a>
      <!-- Button Next
      <a href="{{ route('details') }}" class="btn-next">Next</a> -->

    </div>
  </div>

  <script>
    function goToNextStep() {
      // Cek step mana yang aktif
      let activeStep = document.querySelector('.stepper-item.active');

      // Hapus class active dari langkah saat ini
      activeStep.classList.remove('active');

      // Dapatkan langkah berikutnya
      let nextStep = activeStep.nextElementSibling;

      // Jika ada langkah berikutnya, tambahkan class active
      if (nextStep) {
        nextStep.classList.add('active');
      }
    }

    // Add this new script to handle notes submission
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const notes = document.querySelector('textarea[name="notes"]').value;
        document.querySelector('input[name="notes"]').value = notes;
        this.submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        function updateAllSubtotals() {
            let total = 0;
            document.querySelectorAll('.item-qty').forEach(function(input) {
                const id = input.dataset.id;
                const priceEl = document.querySelector('.item-price[data-id="'+id+'"]');
                const subtotalEl = document.querySelector('.item-subtotal[data-id="'+id+'"]');
                const price = parseInt(priceEl.dataset.price) || 0;
                const qty = parseInt(input.value) || 1;
                const subtotal = price * qty;
                subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
                total += subtotal;
            });
            const totalEl = document.getElementById('cart-total');
            if (totalEl) {
                totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
            }
        }

        document.querySelectorAll('.btn-minus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = btn.dataset.id;
                const qtyInput = document.querySelector('.item-qty[data-id="'+id+'"]');
                let qty = parseInt(qtyInput.value) || 1;
                if (qty > 1) {
                    qtyInput.value = qty - 1;
                    updateAllSubtotals();
                    updateCartOnServer(id, qty - 1);
                }
            });
        });

        document.querySelectorAll('.btn-plus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = btn.dataset.id;
                const qtyInput = document.querySelector('.item-qty[data-id="'+id+'"]');
                let qty = parseInt(qtyInput.value) || 1;
                qtyInput.value = qty + 1;
                updateAllSubtotals();
                updateCartOnServer(id, qty + 1);
            });
        });

        document.querySelectorAll('.item-qty').forEach(function(input) {
            input.addEventListener('input', updateAllSubtotals);
        });

        // Initial update
        updateAllSubtotals();
    });

    function updateCartOnServer(id, newQty) {
        fetch('/cart/update/' + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: 'set',
                quantity: newQty
            })
        });
    }
  </script>

</body>
</html>

