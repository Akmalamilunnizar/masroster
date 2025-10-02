<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shipping</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f8ff;
        }
        .progress-step {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1rem;
        }
        .progress-label {
            font-size: 0.9rem;
        }
        .progress-bar-custom {
            background: #4a67b3;
            border-radius: 12px 12px 0 0;
            padding: 24px 24px 12px 24px;
            margin-bottom: 32px;
        }
        .card {
            border-radius: 16px;
        }
        .btn-danger {
            background: #ff5a36;
            border: none;
        }
        .btn-danger:hover {
            background: #e04a2b;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <!-- Progress Bar -->
    <div class="progress-bar-custom mb-4">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <div class="text-center">
                <div class="progress-step bg-white text-primary border border-primary">1</div>
                <div class="progress-label text-white">Cart</div>
            </div>
            <div class="text-white">&#8226;</div>
            <div class="text-center">
                <div class="progress-step bg-white text-primary border border-primary">2</div>
                <div class="progress-label text-white">Details</div>
            </div>
            <div class="text-danger">&#8226;</div>
            <div class="text-center">
                <div class="progress-step bg-danger text-white">3</div>
                <div class="progress-label text-white">Shipping</div>
            </div>
            <div class="text-secondary">&#8226;</div>
            <div class="text-center">
                <div class="progress-step bg-secondary text-white">4</div>
                <div class="progress-label text-white">Payment</div>
            </div>
            <div class="text-secondary">&#8226;</div>
            <div class="text-center">
                <div class="progress-step bg-secondary text-white">5</div>
                <div class="progress-label text-white">Review</div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center align-items-start g-4">
        <!-- Shipping Selection -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Pilih Pengiriman</h5>
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary me-2">Kurir</button>
                        <button class="btn btn-outline-secondary">Self Pickup</button>
                    </div>
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
                                @foreach($shippingOptions as $option)
                                <tr>
                                    <td>{{ $option['jenis'] }}</td>
                                    <td>{{ $option['durasi'] }}</td>
                                    <td>Rp {{ number_format($option['biaya'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="#" class="btn btn-secondary">&larr; Kembali</a>
                        <a href="#" class="btn btn-danger">Proses Pembayaran &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <strong>Order Summary</strong>
                </div>
                <div class="card-body">
                    @foreach($cartItems as $item)
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] ?? '' }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                        <div class="ms-3 flex-grow-1">
                            <div class="fw-bold">{{ $item['name'] ?? '' }}</div>
                            <div class="text-muted">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }} <span class="ms-2">x{{ $item['qty'] ?? 0 }}</span></div>
                        </div>
                    </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format(collect($cartItems)->sum(function($i){return ($i['price'] ?? 0)*($i['qty'] ?? 0);}), 0, ',', '.') }}</span>
                    </div>                    
                    <div class="d-flex justify-content-between">
                        <span>Shipping:</span>
                        <span>Rp 0</span>
                    </div>
                    <hr>
                    <div class="text-center">
                        <span class="fs-4 fw-bold text-primary">Rp {{ number_format(collect($cartItems)->sum(function($i){return ($i['price'] ?? 0)*($i['qty'] ?? 0);}), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
