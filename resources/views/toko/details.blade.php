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
    .address-card {
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .address-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .address-card.selected {
      border: 2px solid #1D1E94;
      background-color: #f8f9fa;
    }
    #nextButton {
      display: none;
      margin-top: 20px;
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
  <div class="stepper-item active" id="step-2">
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
<div class="form-container" style="max-width: 1000px; width: 1000px; padding: 50px;">
  <h5 class="mb-4">Alamat Pengiriman</h5>

  @if($addresses->count() > 0)
    <div class="mb-4">
      <h6 class="mb-3">Pilih Alamat Tersimpan</h6>
      <div class="row">
        @foreach($addresses as $address)
          <div class="col-md-6 mb-3">
            <div class="card h-100 address-card {{ $address->is_default ? 'border-primary' : '' }}" 
                 data-address-id="{{ $address->id }}">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <h6 class="card-title">{{ $address->label }}</h6>
                  @if($address->is_default)
                    <span class="badge bg-primary">Default</span>
                  @endif
                </div>
                <p class="card-text mb-1">{{ $address->recipient_name }}</p>
                <p class="card-text mb-1">{{ $address->phone_number }}</p>
                <p class="card-text mb-1">{{ $address->full_address }}</p>
                <p class="card-text mb-1">{{ $address->city }}, {{ $address->postal_code }}</p>
                <button class="btn btn-outline-primary btn-sm mt-2 select-address" 
                        data-address-id="{{ $address->id }}">
                  Pilih Alamat Ini
                </button>
                <button class="btn btn-warning btn-sm mt-2 edit-address" 
                        data-address-id="{{ $address->id }}"
                        data-label="{{ $address->label }}"
                        data-recipient="{{ $address->recipient_name }}"
                        data-phone="{{ $address->phone_number }}"
                        data-city="{{ $address->city }}"
                        data-postal="{{ $address->postal_code }}"
                        data-fulladdress="{{ $address->full_address }}">
                  Edit
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="text-center mb-4">
      <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#newAddressForm">
        <i class="bi bi-plus-circle"></i> Tambah Alamat Baru
      </button>
    </div>
  @endif

  <!-- Next Button (Hidden by Default) -->
  <div class="text-center" id="nextButton">
    <a href="{{ route('shipping') }}" class="btn btn-danger">
      <i class="bi bi-arrow-right me-2"></i> Lanjut ke Pengiriman
    </a>
  </div>

  <!-- New Address Form -->
  <div class="{{ $addresses->count() > 0 ? 'collapse' : '' }}" id="newAddressForm">
    <form action="{{ route('save.address') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label for="label" class="form-label">Label Alamat</label>
        <input type="text" class="form-control" id="label" name="label" placeholder="Rumah / Kantor / Lainnya" required>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="recipient_name" class="form-label">Nama Penerima</label>
          <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="phone_number" class="form-label">Nomor Telepon</label>
          <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ Auth::user()->nomor_telepon }}" required readonly>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 mb-3">
          <label for="city" class="form-label">Kota / Kecamatan</label>
          <input type="text" class="form-control" id="city" name="city" placeholder="Masukkan nama kota/kecamatan" required>
        </div>
        <div class="col-md-4 mb-3">
          <label for="postal_code" class="form-label">Kode Pos</label>
          <input type="text" class="form-control" id="postal_code" name="postal_code" required>
        </div>
      </div>
      <div class="mb-3">
        <label for="full_address" class="form-label">Alamat Lengkap</label>
        <textarea class="form-control" id="full_address" name="full_address" rows="3" required></textarea>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
        <label class="form-check-label" for="is_default">Jadikan alamat default</label>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('cart') }}" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Simpan & Lanjutkan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Alamat -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editAddressForm" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAddressModalLabel">Edit Alamat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="address_id" id="editAddressId">
          <div class="mb-3">
            <label class="form-label">Label</label>
            <input type="text" class="form-control" name="label" id="editLabel" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Penerima</label>
            <input type="text" class="form-control" name="recipient_name" id="editRecipient" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nomor Telepon</label>
            <input type="text" class="form-control" name="phone_number" id="editPhone" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kota/Kecamatan</label>
            <input type="text" class="form-control" name="city" id="editCity" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kode Pos</label>
            <input type="text" class="form-control" name="postal_code" id="editPostal" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat Lengkap</label>
            <textarea class="form-control" name="full_address" id="editFullAddress" rows="2" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addressCards = document.querySelectorAll('.address-card');
    const nextButton = document.getElementById('nextButton');
    let selectedAddressId = null;

    // Highlight selected address and show next button
    addressCards.forEach(card => {
      card.addEventListener('click', function() {
        // Remove 'selected' class from all cards
        addressCards.forEach(c => c.classList.remove('selected'));
        
        // Add 'selected' class to clicked card
        this.classList.add('selected');
        
        // Store selected address ID
        selectedAddressId = this.dataset.addressId;
        
        // Show next button
        nextButton.style.display = 'block';
      });
    });

    // Handle "Pilih Alamat Ini" button click
    document.querySelectorAll('.select-address').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const addressId = this.dataset.addressId;
        fetch('/set-selected-address', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ address_id: addressId })
        }).then(() => {
          window.location.href = '{{ route('shipping') }}';
        });
      });
    });

    // Saat tombol edit diklik, isi modal dengan data alamat
    document.querySelectorAll('.edit-address').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.getElementById('editAddressId').value = btn.dataset.addressId;
        document.getElementById('editLabel').value = btn.dataset.label;
        document.getElementById('editRecipient').value = btn.dataset.recipient;
        document.getElementById('editPhone').value = btn.dataset.phone;
        document.getElementById('editCity').value = btn.dataset.city;
        document.getElementById('editPostal').value = btn.dataset.postal;
        document.getElementById('editFullAddress').value = btn.dataset.fulladdress;
        var modal = new bootstrap.Modal(document.getElementById('editAddressModal'));
        modal.show();
      });
    });

    // Submit form edit alamat
    document.getElementById('editAddressForm').addEventListener('submit', function(e) {
      e.preventDefault();
      var addressId = document.getElementById('editAddressId').value;
      var formData = new FormData(this);
      fetch('/addresses/' + addressId, {
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
          location.reload();
        } else {
          alert('Gagal update alamat');
        }
      });
    });
  });
</script>

</body>
</html>
