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

                console.log('Data yang dikirim:', {
                    id: productId,
                    nama: productNama,
                    harga: productHarga,
                    img: productImg,
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
                img: productImg
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
        <!-- Content wrapper -->
        <div class="content-wrapper">
            <!-- Container Tambahan dengan Background Ungu -->
            <div class="container-fluid py-3"
                style="background-image: url('{{ asset('dashboard2/assets/img/imgtoko/backgroundimg2.png') }}');
                    background-size: cover;
                    background-position: center;
                    color: white;
                    min-height: 280px;
                    border-radius: 15px;">
                <!-- Konten di sini -->
            </div>

            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="row">
                    <div class="col-lg-12 mb-12 order-0 mb-4">
                        <div class="card">
                            <div class="d-flex align-items-center row">
                                <!-- Logo di kiri -->
                                <div class="col-sm-5 text-center text-sm-left">
                                    <!-- Kosong -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- / Content -->

    
    <!-- Section Produk Terlaris -->
    <div class="container mt-4">
    <h3 class="mb-4 fw-bold" style="color: #2B3674; font-size: 23px;">Product Terlaris</h3>
    <div class="row">
        @foreach ($produkTerlaris as $item)
        @php
            $minHarga = null;
            if ($item->sizes && count($item->sizes)) {
                $minHarga = $item->sizes->min(function($size) {
                    return $size->pivot->harga;
                });
            }
            if (!$minHarga) {
                $minHarga = $item->custom_harga;
            }
        @endphp
        <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 p-4" style="background-color: #ffffff; border-radius: 15px;">
                <img src="{{ asset('storage/' . ($item->Img ?? 'assets/images/poster1.jpeg')) }}" alt="Gambar Produk" 
                     class="img-fluid" 
                     alt="Foto Produk"
                     style="height: 280px; width: 100%; object-fit: cover; border-radius: 15px;"
                     onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    <div class="card-body" style="padding: 15px;">
                        <h5 class="fw-bold mb-1" style="color: #2B3674;">{{$item->NamaProduk}}</h5>
                        <p class="text-muted mb-2">Digital Printing</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold" style="color: #4318FF;">Rp {{ number_format($minHarga, 0, ',', '.') }}</span>
                            <a href="{{ route('detail.produk', ['id' => $item->IdRoster]) }}" class="btn" style="background-color: #1D1E94; color: white; border-radius: 30px; padding: 5px 20px;">Pesan</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


    <!-- Section: Semua Product -->
    <div class="container mt-4">
        <h3 class="mb-4 fw-bold" style="color: #2B3674; font-size: 23px;">Semua Product</h3>
        <div class="row">
        @foreach ($produk as $item)
            @php
                $minHarga = null;
                if ($item->sizes && count($item->sizes)) {
                    $minHarga = $item->sizes->min(function($size) {
                        return $size->pivot->harga;
                    });
                }
                if (!$minHarga) {
                    $minHarga = $item->custom_harga;
                }
            @endphp
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 p-4" style="background-color: #ffffff; border-radius: 15px;">
                <img src="{{ asset('storage/' . ($item->Img ?? 'assets/images/poster1.jpeg')) }}" alt="Gambar Produk" 
                     class="img-fluid" 
                     alt="Foto Produk"
                     style="height: 280px; width: 100%; object-fit: cover; border-radius: 15px;"
                     onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    <div class="card-body" style="padding: 15px;">
                        <h5 class="fw-bold mb-1" style="color: #2B3674;">{{$item->NamaProduk}}</h5>
                        <p class="text-muted mb-2">Digital Printing</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold" style="color: #4318FF;">Rp {{ number_format($minHarga, 0, ',', '.') }}</span>
                            <a href="{{ route('detail.produk', ['id' => $item->IdRoster]) }}" class="btn" style="background-color: #1D1E94; color: white; border-radius: 30px; padding: 5px 20px;">Pesan</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
    @endsection
