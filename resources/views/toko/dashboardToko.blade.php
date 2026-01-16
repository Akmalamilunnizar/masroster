    @extends('toko.layouts.template')

    @section('page_title')
CIME | Halaman Dashboard E-Commerce
    @endsection
    @section('js')

        <!-- Load jQuery terlebih dahulu -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

         <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />

        <!-- Load ApexCharts setelahnya -->
        <script src="{{ asset('assets/apexcharts/dist/apexcharts.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('assets/apexcharts/dist/apexcharts.css') }}" />
        <link rel="stylesheet" href="{{ URL::asset('assets/apexcharts/dist/apexcharts.css') }}">
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="{{ URL('assets/apexcharts/dist/apexcharts.min.js') }}"></script>
        <link href="css/toko.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script>
            // alert("Script jalan!");
            $(document).ready(function () {
            console.log('DOM siap!');
            $(document).on('click', '.pesan-btn', function() {
                console.log('Tombol Pesan diklik!');
                var productId = $(this).data('id');
                var productNama = $(this).data('nama');
                var productHarga = $(this).data('harga');
                var productImg = $(this).data('img');
                var productUkuran = $(this).data('ukuran') || 'custom';
                var productUkuranLabel = $(this).data('ukuran-label') || 'Custom Ukuran';
                var quantity = 1;
                var subtotal = productHarga * quantity;

                console.log('Data yang dikirim:', {
                    id: productId,
                    nama: productNama,
                    harga: productHarga,
                    img: productImg,
                    ukuran: productUkuran,
                    ukuran_label: productUkuranLabel,
                    quantity: quantity,
                    subtotal: subtotal,
                    _token: '{{ csrf_token() }}'
                });

        $.ajax({
            url: "{{ route('cart.add') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: productId,
                nama: productNama,
                harga: productHarga,
                img: productImg,
                ukuran: productUkuran,
                ukuran_label: productUkuranLabel,
                quantity: quantity,
                subtotal: subtotal
            },
                    success: function(response) {
            console.log('Respon sukses:', response);
            if (response.success) {
                $('#cart-count').text(response.cartCount);

                // Tambahkan notifikasi SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pesanan berhasil ditambahkan ke keranjang.',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },

            error: function(xhr, status, error) {
                console.log('Error:', error);
                console.log('Status:', status);
                console.log('XHR:', xhr);
            }
        });
    });
});

        </script>
    @endsection


    @section('content')
    <style>
        /* Custom Styles for Dashboard */
        .hero-banner {
            background-image: url('{{ asset('dashboard2/assets/img/imgtoko/backgroundimg2.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 2rem 1.5rem;
            min-height: 200px;
        }

        .hero-banner-content {
            position: relative;
            z-index: 2;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .hero-banner-text {
            flex: 1;
            min-width: 200px;
            color: white;
        }

        .hero-banner-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .hero-banner-subtitle {
            font-size: 0.95rem;
            font-weight: 400;
            opacity: 0.95;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .hero-banner-decoration {
            display: none;
            flex-shrink: 0;
        }

        @media (min-width: 576px) {
            .hero-banner {
                min-height: 220px;
                padding: 2.5rem 2rem;
            }

            .hero-banner-title {
                font-size: 1.75rem;
            }

            .hero-banner-subtitle {
                font-size: 1.05rem;
            }
        }

        @media (min-width: 768px) {
            .hero-banner {
                min-height: 280px;
                padding: 3rem 2.5rem;
            }

            .hero-banner-title {
                font-size: 2.25rem;
            }

            .hero-banner-subtitle {
                font-size: 1.15rem;
            }

            .hero-banner-decoration {
                display: block;
            }
        }

        @media (min-width: 992px) {
            .hero-banner {
                min-height: 320px;
                padding: 3.5rem 3rem;
            }

            .hero-banner-title {
                font-size: 2.75rem;
            }

            .hero-banner-subtitle {
                font-size: 1.3rem;
            }
        }

        @media (min-width: 1200px) {
            .hero-banner {
                min-height: 360px;
            }

            .hero-banner-title {
                font-size: 3.25rem;
            }
        }

        /* Overlay for better text readability */
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(67, 24, 255, 0.3) 0%, rgba(29, 30, 148, 0.2) 100%);
            z-index: 1;
        }

        .section-title {
            color: #2B3674;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #4318FF, #1D1E94);
            border-radius: 2px;
        }

        .product-card {
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(67, 24, 255, 0.15);
            border-color: #e0e0ff;
        }

        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
            aspect-ratio: 1 / 1;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-title {
            color: #2B3674;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-category {
            color: #8b8b8b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .product-price {
            color: #4318FF;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: auto;
        }

        .btn-view {
            flex: 1;
            border-radius: 12px;
            padding: 0.625rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            border: 2px solid #4318FF;
            color: #4318FF;
            background: transparent;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-view:hover {
            background: #4318FF;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 24, 255, 0.3);
        }

        .btn-pesan {
            flex: 1;
            border-radius: 12px;
            padding: 0.625rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            background: linear-gradient(135deg, #1D1E94, #4318FF);
            color: white;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-pesan:hover {
            background: linear-gradient(135deg, #4318FF, #1D1E94);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(29, 30, 148, 0.4);
        }

        .btn-pesan:active {
            transform: translateY(0);
        }

        .section-container {
            padding: 2rem 0;
        }

        @media (max-width: 767px) {
            .section-container {
                padding: 1.5rem 0;
            }

            .section-title {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .product-card-body {
                padding: 1rem;
            }

            .product-title {
                font-size: 1rem;
            }

            .product-price {
                font-size: 1.1rem;
            }

            .product-actions {
                flex-direction: column;
            }

            .btn-view,
            .btn-pesan {
                width: 100%;
            }
        }

        @media (min-width: 576px) and (max-width: 767px) {
            .product-actions {
                flex-direction: row;
            }
        }
    </style>

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Hero Banner Section -->
        <div class="container-fluid px-3 px-md-4 py-4">
            <div class="hero-banner">
                <div class="hero-banner-content">
                    <div class="hero-banner-text">
                        <h1 class="hero-banner-title">E-commerce CIME</h1>
                        <p class="hero-banner-subtitle">Percetakan Citra Media</p>
                    </div>
                    <div class="hero-banner-decoration">
                        <!-- Decorative elements from background image -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Terlaris Section -->
        <div class="section-container">
            <div class="container px-3 px-md-4">
                <h2 class="section-title">
                    <i class="bi bi-star-fill me-2" style="color: #FFD700;"></i>
                    Product Terlaris
                </h2>
                <div class="row g-4">
                    @foreach ($produkTerlaris as $item)
                        @php
                            $minHarga = null;
                            $defaultUkuran = 'custom';
                            $defaultUkuranLabel = 'Custom Ukuran';

                            if ($item->sizes && count($item->sizes)) {
                                $firstSize = $item->sizes->first();
                                $minHarga = $item->sizes->min(function($size) {
                                    return $size->pivot->harga;
                                });
                                $defaultUkuran = $firstSize->id_ukuran;
                                $defaultUkuranLabel = $firstSize->nama . ' (' . $firstSize->panjang . ' x ' . $firstSize->lebar . ' cm)';
                            }
                            if (!$minHarga) {
                                $minHarga = $item->custom_harga;
                            }
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card">
                                <div class="product-image-wrapper">
                                    <img src="{{ asset('storage/' . ($item->Img ?? 'assets/images/poster1.jpeg')) }}"
                                         class="product-image"
                                         alt="{{ $item->NamaProduk }}"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                                </div>
                                <div class="product-card-body">
                                    <h5 class="product-title">{{ $item->NamaProduk }}</h5>
                                    <p class="product-category">Digital Printing</p>
                                    <div class="product-price">
                                        Rp {{ number_format($minHarga, 0, ',', '.') }}
                                    </div>
                                    <div class="product-actions">
                                        <a href="{{ route('detail.produk', ['id' => $item->IdRoster]) }}"
                                           class="btn-view">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        <button class="btn-pesan pesan-btn"
                                                data-id="{{ $item->IdRoster }}"
                                                data-nama="{{ $item->NamaProduk }}"
                                                data-harga="{{ $minHarga }}"
                                                data-img="{{ $item->Img }}"
                                                data-ukuran="{{ $defaultUkuran }}"
                                                data-ukuran-label="{{ $defaultUkuranLabel }}">
                                            <i class="bi bi-cart-plus me-1"></i>Pesan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Semua Product Section -->
        <div class="section-container">
            <div class="container px-3 px-md-4">
                <h2 class="section-title">
                    <i class="bi bi-grid-3x3-gap me-2" style="color: #4318FF;"></i>
                    Semua Product
                </h2>
                <div class="row g-4">
                    @foreach ($produk as $item)
                        @php
                            $minHarga = null;
                            $defaultUkuran = 'custom';
                            $defaultUkuranLabel = 'Custom Ukuran';

                            if ($item->sizes && count($item->sizes)) {
                                $firstSize = $item->sizes->first();
                                $minHarga = $item->sizes->min(function($size) {
                                    return $size->pivot->harga;
                                });
                                $defaultUkuran = $firstSize->id_ukuran;
                                $defaultUkuranLabel = $firstSize->nama . ' (' . $firstSize->panjang . ' x ' . $firstSize->lebar . ' cm)';
                            }
                            if (!$minHarga) {
                                $minHarga = $item->custom_harga;
                            }
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card">
                                <div class="product-image-wrapper">
                                    <img src="{{ asset('storage/' . ($item->Img ?? 'assets/images/poster1.jpeg')) }}"
                                         class="product-image"
                                         alt="{{ $item->NamaProduk }}"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                                </div>
                                <div class="product-card-body">
                                    <h5 class="product-title">{{ $item->NamaProduk }}</h5>
                                    <p class="product-category">Digital Printing</p>
                                    <div class="product-price">
                                        Rp {{ number_format($minHarga, 0, ',', '.') }}
                                    </div>
                                    <div class="product-actions">
                                        <a href="{{ route('detail.produk', ['id' => $item->IdRoster]) }}"
                                           class="btn-view">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        <button class="btn-pesan pesan-btn"
                                                data-id="{{ $item->IdRoster }}"
                                                data-nama="{{ $item->NamaProduk }}"
                                                data-harga="{{ $minHarga }}"
                                                data-img="{{ $item->Img }}"
                                                data-ukuran="{{ $defaultUkuran }}"
                                                data-ukuran-label="{{ $defaultUkuranLabel }}">
                                            <i class="bi bi-cart-plus me-1"></i>Pesan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endsection
