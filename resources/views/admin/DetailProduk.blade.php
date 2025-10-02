@extends('toko.layouts.template')
@section('page_title')
    CIME | Detail Produk
@endsection

@section('search')
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <form method="GET" action="{{ route('searchusers') }}" class="d-inline-block ms-2">
                <input type="text" name="search" class="form-control border-0 shadow-none ps-2"
                    placeholder="Pencarian ID atau nama..." value="{{ isset($search) ? $search : '' }}" />
            </form>
        </div>
    </div>
@endsection



<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
@section('content')
    @php
        // Collect all prices from sizes and custom_harga
        $hargaList = collect();
        $selectedUkuranId = null;
        if ($produk->sizes && count($produk->sizes)) {
            $minUkuran = null;
            foreach ($produk->sizes as $size) {
                if ($size->pivot->harga > 0) {
                    $hargaList->push($size->pivot->harga);
                    if ($minUkuran === null || $size->pivot->harga < $minUkuran->pivot->harga) {
                        $minUkuran = $size;
                    }
                }
            }
            if ($minUkuran) {
                $selectedUkuranId = $minUkuran->id_ukuran;
            }
        }

        // Get the minimum non-zero price, or 0 if all are zero
        $minHarga = $hargaList->count() ? $hargaList->min() : 0;
        $maxHarga = $produk->sizes->max(function($size) {
            return $size->pivot->harga;
        });
        $customHargaFinal = $maxHarga;
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y" style="overflow-x: auto;">
            <div class="row">
                <div class="col-md-6">
                    <div class="product-image-container">
                        <img src="{{ asset('storage/' . ($produk->Img ?? 'assets/images/poster1.jpeg')) }}"
                            alt="{{ $produk->NamaRoster }}"
                            class="product-image" id="main-image"
                            onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                    </div>
                    <div class="thumbnail-scroll-wrapper mt-3" id="thumbnailScrollWrapper">
                        
                    </div>
                    @if(auth()->check() && $user)
                        <div class="mt-4">
                            <h5><b>Informasi Kontak</b></h5>
                            <p>Nomor Telepon: 0896 2716 0919</p>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">

                    <div>
                        <h1>
                            <b style="font-size: 2em; color: black;">
                                {{ $produk->NamaRoster ?? 'Detail Produk' }}
                            </b>
                        </h1>
                        <div class="mb-2">
                            <span class="badge bg-secondary me-2">{{ $produk->jenisRoster->JenisBarang ?? '-' }}</span>
                            @if($produk->motif)
                                <span class="badge bg-info">{{ $produk->motif->nama_motif }}</span>
                            @endif
                        </div>
                        <p><b style="color: black;">Terjual</b> 10rb+ <span class="star" style="color: gold;">&#9733;</span>
                            <b style="color: black;">5
                        </p>
                        <h4 class="jarakHarga">
                            <b style="font-size: 2em; color: black; position: relative; top: 5px;">
                                Rp <span id="displayHargaSatuan">
                                    {{ number_format($minHarga, 0, ',', '.') }} *starts from
                                </span>
                            </b>
                        </h4>

                        <div class="mt-3">
                            <h5 style="margin-top: 25px;"><b style="font-size: 1.5em; color: black; color:rgb(104, 59, 187)">Deskripsi Produk </b></h5>
                            <p style="margin-top: 20px; white-space: pre-line;">{{ $produk->deskripsi ?? ($description ?? '-') }}</p>
                        </div>
                        


                            <h5 class="mt-4">Pemesanan</h5>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row mb-3 align-items-center">
                                        <div class="row mb-3">
                                            <label for="ukuran_produk_select" class="form-label fw-bold">
                                                <i class="bx bx-ruler-horizontal me-2"></i> Ukuran
                                            </label>
                                            <div class="col-sm-10">
                                                <select class="form-select" id="ukuran_produk_select"
                                                    name="ukuran_produk_selected">
                                                    @foreach ($produk->sizes as $ukuran)
                                                        <option value="{{ $ukuran->id_ukuran }}" data-harga="{{ $ukuran->pivot->harga }}" @if($ukuran->id_ukuran == $selectedUkuranId) selected @endif>
                                                            {{ $ukuran->nama }} ({{ $ukuran->panjang }} x {{ $ukuran->lebar }} + "Cm") - Rp {{ number_format($ukuran->pivot->harga, 0, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                    <option value="custom" data-harga="{{ $customHargaFinal }}">
                                                        Custom - Rp {{ number_format($customHargaFinal, 0, ',', '.') }}
                                                    </option>
                                                </select>

                                                <input type="text"
                                                    class="form-control mt-2 {{ old('ukuran_produk_is_custom') ? '' : 'd-none' }}"
                                                    id="ukuran_produk_custom"
                                                    placeholder="Masukkan ukuran custom (contoh: 2x3 meter)"
                                                    value="{{ old('ukuran_produk_custom_value') }}" />

                                                {{-- Hidden input to store the final selected/custom value for submission
                                                --}}
                                                <input type="hidden" name="ukuran_produk" id="ukuran_produk_final"
                                                    value="{{ old('ukuran_produk') }}">
                                            </div>
                                        </div>


                                    </div>
                                    
                                    
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-4">
                                            <label for="jumlahOrderInput" class="form-label fw-bold"><i
                                                    class="bx bx-plus-minus me-2"></i> Jumlah</label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <button class="btn btn-outline-secondary" type="button" id="minusButton"><i
                                                        class="bx bx-minus"></i></button>
                                                <input type="number" class="form-control text-center" id="jumlahOrderInput"
                                                    value="1" min="1">
                                                <button class="btn btn-outline-secondary" type="button" id="plusButton"><i
                                                        class="bx bx-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-4">
                                            <label for="uploadFile" class="form-label fw-bold"><i
                                                    class="bx bx-upload me-2"></i> Upload File</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input class="form-control form-control-sm" type="file" id="uploadFile" name="design_file"
                                                accept=".jpg,.jpeg,.png,.webp,.pdf,.rar,.zip" />
                                            <small class="text-muted">nb : jpg, png, jpeg, webp, pdf, rar, zip. max
                                                10mb</small>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-4">
                                            <label for="nomorHP" class="form-label fw-bold"><i class="bx bx-phone me-2"></i>
                                                Nomor HP</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="tel" class="form-control" id="nomorHP" placeholder="Nomor Telepon" value="{{ Auth::user()->NoTelp }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold"><i class="bx bx-receipt me-2"></i> Rincian Harga</h6>
                                    <hr>
                                    <div class="row mb-2 fw-bold" style="margin-top: 10px;">
                                        <div class="col-6">Total Harga Satuan</div>
                                        <div class="col-6 text-end">
                                            Rp <span id="hargaSatuanDisplay"></span>
                                            <span id="hargaSatuanOriginal" class="text-decoration-line-through ms-2 text-muted"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">Jumlah Order</div>
                                        <div class="col-6 text-end" id="summaryJumlahOrder">1</div>
                                    </div>
                                    <hr>
                                    <div class="row fw-bold text-primary">
                                        <div class="col-6" style="margin-top: 10px;">Total Pembayaran</div>
                                        <div class="col-6 text-end" style="margin-top: 10px;">Rp
                                            <span id="summaryTotalPembayaran">{{ number_format($minHarga, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2" style="margin-top: 20px;">
                                        <button class="btn btn-primary shadow-sm" type="button" id="beliSekarangBtn">Beli Sekarang</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="beliSekarangModal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h2>Konfirmasi Pembelian</h2>
                    <div class="konfirmasi-detail">
                        <div class="konfirmasi-produk">
                            <img src="{{ asset('storage/' . $produk->Img) }}" alt="{{ $produk->NamaProduk }}"
                                class="product-image" id="modalProductImage"> <div class="produk-info">
                                <h6 class="produk-nama" style="margin-left: 20px;">
                                    {{ $produk->NamaProduk ?? 'Detail Produk' }}
                                </h6>
                                <small style="margin-left: 20px;">Ukuran: <span id="modalUkuran"></span></small><br>
                                <small style="margin-left: 20px;">Nomor HP: <span id="modalNomorHP"></span></small>
                            </div>
                        </div>
                        <div class="konfirmasi-harga">
                            <div class="harga-item">
                                <span>Harga Satuan:</span>
                                <span>Rp <span id="modalHargaSatuan"></span></span>
                            </div>
                            <div class="harga-item">
                                <span>Jumlah:</span>
                                <span><span id="modalJumlah"></span></span>
                            </div>
                            <div class="harga-item total">
                                <span>Subtotal:</span>
                                <span>Rp <span id="modalSubtotal"></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" id="batalBeliBtn">Batal</button>
                        <button class="btn btn-primary" id="customModalBeliSekarangBtn" disabled>Pesan Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="modal fade" id="modalBeliSekarang" tabindex="-1" aria-labelledby="modalBeliSekarangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBeliSekarangLabel">Konfirmasi Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin membeli produk ini sekarang?</p>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Order</label>
                        <input type="number" class="form-control" id="jumlahOrderInput" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="bsModalBeliSekarangBtn">Pesan Sekarang</button>
                </div>
            </div>
        </div>
    </div>
@endsection


<script>
$(document).ready(function () {
    $('#beliSekarangBtn2').click(function () {
        let productId = $(this).data('id');
        let productNama = $(this).data('nama');
        let productHarga = $(this).data('harga');
        let productImg = $(this).data('img');

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
            success: function (response) {
                if (response.success) {
                    // Tutup modal konfirmasi pembelian
                    $('#beliSekarangModal').removeClass("show");
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk berhasil ditambahkan ke keranjang.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log('Error:', error);
            }
        });
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeSelect = document.getElementById('ukuran_produk_select');
        const jumlahInput = document.getElementById('jumlahOrderInput');
        const plusButton = document.getElementById('plusButton');
        const minusButton = document.getElementById('minusButton');
        const hargaSatuanDisplay = document.getElementById('hargaSatuanDisplay');
        const summaryTotalPembayaran = document.getElementById('summaryTotalPembayaran');
        const summaryJumlahOrder = document.getElementById('summaryJumlahOrder');

        // Modal elements
        const beliSekarangBtn = document.getElementById('beliSekarangBtn');
        const beliSekarangModal = document.getElementById('beliSekarangModal');
        const closeButton = document.querySelector('.close-button');
        const batalBeliBtn = document.getElementById('batalBeliBtn');
        const ukuranProdukCustom = document.getElementById('ukuran_produk_custom');
        const catatanInput = document.getElementById('catatan');
        const uploadFileInput = document.getElementById('uploadFile');
        const nomorHPInput = document.getElementById('nomorHP');
        const modalProductImage = document.getElementById('modalProductImage');
        const modalUkuran = document.getElementById('modalUkuran');
        const modalCatatan = document.getElementById('modalCatatan');
        const modalNomorHP = document.getElementById('modalNomorHP');
        const modalHargaSatuan = document.getElementById('modalHargaSatuan');
        const modalJumlah = document.getElementById('modalJumlah');
        const modalSubtotal = document.getElementById('modalSubtotal');
        const modalBeliSekarangBtn = document.getElementById('customModalBeliSekarangBtn');

        function updateHargaDanTotal() {
            const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
            const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
            const jumlah = parseInt(jumlahInput.value) || 1;
            if (hargaSatuanDisplay) hargaSatuanDisplay.innerText = harga.toLocaleString('id-ID');
            if (summaryTotalPembayaran) summaryTotalPembayaran.innerText = (harga * jumlah).toLocaleString('id-ID');
            if (summaryJumlahOrder) summaryJumlahOrder.innerText = jumlah;
            // Update modal if open
            if (modalHargaSatuan && modalSubtotal && modalJumlah) {
                modalHargaSatuan.textContent = harga.toLocaleString('id-ID');
                modalJumlah.textContent = jumlah;
                modalSubtotal.textContent = (harga * jumlah).toLocaleString('id-ID');
            }
        }

        function handleCustomUkuranRequired() {
            if (sizeSelect.value === 'custom' && ukuranProdukCustom) {
                ukuranProdukCustom.required = true;
                ukuranProdukCustom.classList.remove('d-none');
            } else if (ukuranProdukCustom) {
                ukuranProdukCustom.required = false;
                ukuranProdukCustom.classList.add('d-none');
            }
        }
        sizeSelect.addEventListener('change', function() {
            handleCustomUkuranRequired();
            updateHargaDanTotal();
        });
        // Initial state
        handleCustomUkuranRequired();

        jumlahInput.addEventListener('input', updateHargaDanTotal);
        if (plusButton) {
            plusButton.addEventListener('click', function () {
                jumlahInput.value = parseInt(jumlahInput.value) + 1;
                updateHargaDanTotal();
            });
        }
        if (minusButton) {
            minusButton.addEventListener('click', function () {
                if (parseInt(jumlahInput.value) > 1) {
                    jumlahInput.value = parseInt(jumlahInput.value) - 1;
                    updateHargaDanTotal();
                }
            });
        }
        // Initial update
        updateHargaDanTotal();

        // Modal logic for Beli Sekarang
        if (beliSekarangBtn && beliSekarangModal) {
            beliSekarangBtn.addEventListener('click', function () {
                // Update modal fields with current values
                const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
                const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
                const jumlah = parseInt(jumlahInput.value) || 1;
                const catatan = catatanInput ? catatanInput.value : '-';
                const nomorHP = nomorHPInput ? nomorHPInput.value : '-';
                modalUkuran.textContent = sizeSelect.value === 'custom' && ukuranProdukCustom ? ukuranProdukCustom.value : selectedOption.text;
                if (modalCatatan) modalCatatan.textContent = catatan;
                if (modalNomorHP) modalNomorHP.textContent = nomorHP;
                if (modalHargaSatuan) modalHargaSatuan.textContent = harga.toLocaleString('id-ID');
                if (modalJumlah) modalJumlah.textContent = jumlah;
                if (modalSubtotal) modalSubtotal.textContent = (harga * jumlah).toLocaleString('id-ID');
                // Handle uploaded image
                if (uploadFileInput && uploadFileInput.files.length > 0) {
                    const uploadedFile = uploadFileInput.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (modalProductImage) modalProductImage.src = e.target.result;
                    };
                    reader.readAsDataURL(uploadedFile);
                } else if (modalProductImage) {
                    modalProductImage.src = "{{ asset('storage/' . $produk->Img) }}";
                }
                beliSekarangModal.classList.add("show");
                if (modalBeliSekarangBtn && uploadFileInput) {
                    modalBeliSekarangBtn.disabled = uploadFileInput.files.length === 0;
                }
            });
            if (closeButton) closeButton.addEventListener('click', function () { beliSekarangModal.classList.remove("show"); });
            if (batalBeliBtn) batalBeliBtn.addEventListener('click', function () { beliSekarangModal.classList.remove("show"); });
            window.addEventListener('click', function (event) {
                if (event.target === beliSekarangModal) {
                    beliSekarangModal.classList.remove("show");
                }
            });
            // Modal Pesan Sekarang button logic
            if (modalBeliSekarangBtn) {
                modalBeliSekarangBtn.addEventListener('click', function() {
                    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
                    const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
                    const jumlah = parseInt(jumlahInput.value) || 1;
                    if (harga === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Harga Tidak Valid',
                            text: 'Silakan pilih ukuran yang benar. Produk dengan harga 0 tidak dapat dipesan.'
                        });
                        modalBeliSekarangBtn.disabled = false;
                        modalBeliSekarangBtn.innerHTML = 'Pesan Sekarang';
                        return;
                    }
                    var productId = '{{ $produk->IdRoster }}';
                    var productName = '{{ $produk->NamaRoster }}';
                    var size = sizeSelect.value;
                    var ukuranLabel = selectedOption.text;
                    var fileInput = uploadFileInput;
                    var designFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;
                    var subtotal = harga * jumlah;
                    var customUkuran = '';
                    if (sizeSelect.value === 'custom' && ukuranProdukCustom) {
                        customUkuran = ukuranProdukCustom.value;
                        if (!customUkuran) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Ukuran custom harus diisi!',
                                text: 'Silakan masukkan ukuran custom.'
                            });
                            modalBeliSekarangBtn.disabled = false;
                            modalBeliSekarangBtn.innerHTML = 'Pesan Sekarang';
                            return;
                        }
                    }
                    var formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('id', productId);
                    formData.append('nama', productName);
                    formData.append('harga', harga);
                    formData.append('img', '{{ $produk->Img }}');
                    formData.append('quantity', jumlah);
                    formData.append('ukuran', size);
                    formData.append('ukuran_label', ukuranLabel);
                    formData.append('subtotal', subtotal);
                    formData.append('custom_ukuran', customUkuran);
                    if (designFile) {
                        formData.append('design_file', designFile);
                    }
                    // Show loading state
                    modalBeliSekarangBtn.disabled = true;
                    modalBeliSekarangBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                    // Add to cart via AJAX
                    fetch('{{ route("cart.add") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        if (response.ok) {
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
                            let errorMsg = 'Terjadi kesalahan saat menambahkan ke keranjang';
                            if (response.status === 422) {
                                const data = await response.json();
                                errorMsg = Object.values(data.errors).join('<br>');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: errorMsg
                            });
                            modalBeliSekarangBtn.disabled = false;
                            modalBeliSekarangBtn.innerHTML = 'Pesan Sekarang';
                        }
                    })
                    .catch(async error => {
                        let errorMsg = error.message || 'Terjadi kesalahan saat menambahkan ke keranjang';
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: errorMsg
                        });
                        modalBeliSekarangBtn.disabled = false;
                        modalBeliSekarangBtn.innerHTML = 'Pesan Sekarang';
                    });
                });
            }
        }
    });
</script>

<script>
    function highlightStars(selectedStar) {
        const stars = document.querySelectorAll('.star-filter');
        const rating = parseInt(selectedStar.getAttribute('data-rating'));

        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.style.color = 'gold'; // Warna emas yang lebih pekat adalah default
            } else {
                star.style.color = '#ccc';
            }
        });
    }
         
</script>

<script>
    function highlightStars(selectedStar) {
        const stars = document.querySelectorAll('.star-filter');
        const rating = parseInt(selectedStar.getAttribute('data-rating'));

        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.style.color = 'gold'; // Warna emas yang lebih pekat adalah default
            } else {
                star.style.color = '#ccc';
            }
        });
    }
</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('ukuran_produk_select');
            const customInput = document.getElementById('ukuran_produk_custom');
            const hiddenInput = document.getElementById('ukuran_produk_final');

            function updateFinalValue() {
                if (select.value === 'custom') {
                    customInput.classList.remove('d-none');
                    hiddenInput.value = customInput.value;
                } else {
                    customInput.classList.add('d-none');
                    hiddenInput.value = select.value;
                }
            }

            select.addEventListener('change', updateFinalValue);
            customInput.addEventListener('input', function () {
                hiddenInput.value = customInput.value;
            });

            // Panggil saat pertama kali jika old value adalah custom
            if (select.value === 'custom') {
                customInput.classList.remove('d-none');
            }
        });
    </script>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectEl = document.getElementById('ukuran_produk_select');
        const customInput = document.getElementById('ukuran_produk_custom');
        const hiddenFinal = document.getElementById('ukuran_produk_final');

        function handleChange() {
            const selectedValue = selectEl.value;
            if (selectedValue === 'custom') {
                customInput.classList.remove('d-none');
                hiddenFinal.value = customInput.value; // Isi sementara
            } else {
                customInput.classList.add('d-none');
                hiddenFinal.value = selectedValue;
            }
        }

        selectEl.addEventListener('change', handleChange);

        // Update hidden field saat user mengetik ukuran custom
        customInput.addEventListener('input', function () {
            hiddenFinal.value = this.value;
        });
    });
</script>

<style>

    .modal {
    display: none;
    /* lainnya... */
}

.modal.show {
    display: block;
    /* lainnya... */
}
    .product-image-container {
        width: 100%;
        /* Mengisi lebar parent (misalnya, col-md-4) */
        height: 550px;
        /* Tinggi yang Anda inginkan (sesuaikan!) */
        overflow: hidden;
        /* Potong gambar jika melebihi container */
        display: flex;
        /* Untuk centering gambar */
        align-items: center;
        /* Vertikal center gambar */
        justify-content: center;
        /* Horizontal center gambar */
    }

    .product-image {
        width: 100%;
        /* Gambar mengisi lebar container */
        height: 100%;
        /* Gambar mengisi tinggi container */
        object-fit: cover;
        /* Penting: Gambar memotong agar mengisi */
        display: block;
        /* Pastikan gambar block-level */
        border-radius: 8px;
        /* Jika Anda ingin sudut bulat */
    }

    /*ukuran bintang di sebelah teks ulasan*/
    .star-filter {
        cursor: pointer;
        font-size: 2em;
        /* Atau ukuran lain yang Anda inginkan */
    }

    /* CSS Anda yang sudah ada */
    .jarakHarga {
        margin-top: 30px;
    }

    /*Fitur pemesanan*/
    .pemesanan-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, auto));
        gap: 15px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, auto));
        gap: 15px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #ced4da;
    }

    .form-label {
        font-size: 0.9em;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }

    .form-select,
    .form-control {
        font-size: 0.9em;
    }

    .input-group-text {
        font-size: 0.9em;
        border: 1px solid #ced4da;
    }

    .form-label {
        font-size: 0.9em;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }

    .form-select,
    .form-control {
        font-size: 0.9em;
    }

    .input-group-text {
        font-size: 0.9em;
    }

    .jumlah-container {
        margin-right: 20px;
    }

    .jumlah-container label {
        display: block;
        margin-bottom: 5px;
        font-size: 0.9em;
        color: #495057;
    }

    .input-jumlah {
        display: flex;
        align-items: center;
    }

    .btn-jumlah {
        background: none;
        border: 1px solid #ced4da;
        color: #495057;
        width: 30px;
        height: 30px;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.2s ease;
    }

    .btn-jumlah:hover {
        background-color: #e9ecef;
    }

    .input-jumlah input[type="number"] {
        width: 50px;
        height: 30px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        text-align: center;
        margin: 0 5px;
        -moz-appearance: textfield;
        /* Firefox */
    }

    .input-jumlah input[type="number"]::-webkit-outer-spin-button,
    .input-jumlah input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .stok-total {
        display: block;
        margin-top: 5px;
        font-size: 0.8em;
        color: #6c757d;
    }

    .subtotal-container {
        margin-right: auto;
        /* Dorong tombol ke kanan */
        text-align: right;
    }

    .subtotal-label {
        font-size: 0.9em;
        color: #495057;
        margin-bottom: 3px;
    }

    .subtotal-harga {
        font-size: 1.2em;
        font-weight: bold;
        color: #212529;
    }

    .tombol-container {
        display: flex;
        gap: 10px;
    }

    .btn-keranjang {
        background-color: #6f42c1;
        /* Warna ungu mirip pada gambar */
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.2s ease;
    }

    .btn-keranjang:hover {
        background-color: #563d7c;
    }

    .btn-beli {
        background-color: #e0f7fa;
        /* Warna biru muda mirip pada gambar */
        color: #00bcd4;
        /* Warna teks biru tosca */
        border: 1px solid #00bcd4;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .btn-beli:hover {
        background-color: #b2ebf2;
        color: #008ba3;
        border-color: #008ba3;
    }

    /*--------------------------*/
    /* CSS Tambahan untuk Desain Pemesanan yang Lebih Menarik */
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 15px;
    }

    .card-body {
        padding: 1.5rem;
        border: 1px solid #555;
        border-radius: 8px;
    }

    .form-label {
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        font-size: 0.9rem;
        border-radius: 5px;
        border: 1px solid #ced4da;
    }

    .input-group-text {
        font-size: 0.9rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 5px;
    }

    .btn-outline-secondary {
        border-radius: 5px;
    }

    .card-title {
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .text-primary {
        font-size: 1.05rem;
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    /* CSS Tambahan untuk Desain Pemesanan yang Lebih Menarik */
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 15px;
    }

    .card-body {
        padding: 1.5rem;
        border: 1px solid #555;
        border-radius: 8px;
    }

    .form-label {
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        font-size: 0.9rem;
        border-radius: 5px;
        border: 1px solid #ced4da;
    }

    .input-group-text {
        font-size: 0.9rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 5px;
    }

    .btn-outline-secondary {
        border-radius: 5px;
    }

    .card-title {
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .text-primary {
        font-size: 1.05rem;
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    /*untuk button detail dan info penting*/
    .nav-link:hover {
        background-color: lightpurple;
        /* Warna latar belakang saat kursor diarahkan */
        color: black;
        /* Anda mungkin ingin mengubah warna teks agar tetap terlihat */
    }


    .img-outline {
        width: 100%;
        height: auto;
        border: 10px solid white;
        padding: 2px;
        border-radius: 15px;
        object-fit: contain;
    }

    .img-outline-thin {
        border-width: 3px !important;
        border-radius: 10px;
    }

    .img-thumb-wrapper {
        flex: 0 0 auto;
        width: 70px;
        height: 70px;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff;
        margin-left: 10px;
        cursor: pointer;
        /* Menambahkan indikasi bahwa elemen bisa diklik */
        transition: transform 0.3s ease-in-out;
        /* Animasi transisi */
        transform-origin: center center;
        /* Mengatur titik pusat transformasi */
    }

    .img-thumb-wrapper:hover {
        transform: scale(1.2);
        /* Skala 1.2 kali ukuran semula saat dihover */
    }

    .img-thumb {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .thumbnail-scroll-wrapper {
        overflow-x: hidden;
        /* Sembunyikan konten yang melebihi lebar */
        padding-bottom: 10px;

    }

    .thumbnail-scroll {
        display: flex;
        flex-wrap: nowrap;
        /* Hitung lebar maksimum untuk 9 thumbnail (lebar thumbnail + margin) */
        max-width: calc(9 * (70px + 5px));
        /* Contoh: 70px lebar thumb, 5px margin */
        margin-top: 10px;
    }

    /*rate ulasan pembeli*/
    .review-summary {
        padding: 15px;
        border: 1px solid #555;
        /* Garis outlane warna hitam*/
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .overall-rating {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 1.2em;
    }

    .overall-rating .star {
        color: gold;
        margin-right: 5px;
    }

    .rating-value {
        font-weight: bold;
    }

    .out-of {
        color: #777;
    }

    .satisfaction {
        color: #28a745;
        margin-bottom: 5px;
        font-size: 0.9em;
    }

    .rating-count {
        color: #555;
        font-size: 0.9em;
        margin-bottom: 10px;
    }

    .rating-bars {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rating-bar {
        display: flex;
        align-items: center;
        font-size: 0.9em;
        margin-right: 25px;
    }

    .rating-bar .star {
        color: gold;
        margin-right: 10px;
        font-size: 1em;
    }

    .rating-bar .bar-container {
        background-color: #ddd;
        border-radius: 5px;
        height: 8px;
        width: 100px;
        /* Sesuaikan lebar sesuai kebutuhan */
        margin-left: 10px;
        margin-right: 10px;
        overflow: hidden;
    }

    .rating-bar .bar {
        background-color: rgb(5, 122, 248);
        /* Warna bar */
        height: 100%;
        border-radius: 5px;
    }

    .rating-bar .count {
        color: #777;
        margin-left: 5px;
    }

    /* Style untuk Modal */
    .modal {
        display: none;
        /* Tersembunyi secara default */
        position: fixed;
        /* Tetap di posisinya meskipun di-scroll */
        z-index: 1;
        /* Lapisan di atas elemen lain */
        left: 0;
        top: 0;
        width: 100%;
        /* Lebar penuh layar */
        height: 100%;
        /* Tinggi penuh layar */
        overflow: auto;
        /* Aktifkan scroll jika konten modal melebihi layar */
        background-color: rgba(0, 0, 0, 0.4);
        /* Latar belakang semi-transparan */
        display: flex;
        /* Mengaktifkan flexbox untuk pemosisian anak elemen */
        justify-content: center;
        /* Membuat anak elemen berada di tengah horizontal */
        align-items: center;
        /* Membuat anak elemen berada di tengah vertikal */
    }

    /* Style untuk Konten Modal (kotak putih) */
    .modal-content {
        background-color: #fefefe;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        position: relative;
        /* Untuk memposisikan elemen di dalamnya */
        width: auto;
        /* Lebar menyesuaikan konten */
        max-width: 1000px;
        /* Lebar maksimum agar tidak terlalu lebar */
    }

    .modal-content .product-image {
        /* Lebih spesifik */
        max-width: 150px;
        /* Sesuaikan dengan lebar yang Anda inginkan */
        max-height: 150px;
        /* Sesuaikan dengan tinggi yang Anda inginkan */
        width: auto;
        /* Biarkan lebar menyesuaikan proporsi */
        height: auto;
        /* Biarkan tinggi menyesuaikan proporsi */
        object-fit: contain;
        /* Jaga proporsi gambar */
    }

    .modal-content #main-image {
        /* Jika Anda menggunakan ID */
        max-width: 100px;
        /* Sesuaikan dengan lebar yang Anda inginkan */
        max-height: 100px;
        /* Sesuaikan dengan tinggi yang Anda inginkan */
        width: auto;
        /* Biarkan lebar menyesuaikan proporsi */
        height: auto;
        /* Biarkan tinggi menyesuaikan proporsi */
        object-fit: contain;
        /* Jaga proporsi gambar */
    }

    .modal.show {
        display: flex !important;
    }


    .close-button {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        /* Tambahkan properti position */
        top: 10px;
        /* Atur jarak dari atas */
        right: 15px;
        /* Atur jarak dari kanan */
        cursor: pointer;
        /* Tambahkan cursor agar terlihat bisa diklik */
    }

    .close-button:hover,
    .close-button:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-actions {
        margin-top: 20px;
        text-align: right;
        /* Atau left jika tombol ingin di kiri */
        display: flex;
        /* Agar tombol berdampingan */
        justify-content: flex-end;
        /* Atau flex-start jika tombol ingin di kiri */
        gap: 10px;
        /* Jarak antar tombol */
    }


    .modal-actions button {
        /* Style untuk tombol (warna, padding, dll. bisa disesuaikan) */
    }

    /* Style untuk Konfirmasi Pembelian yang Baru */
    .konfirmasi-detail {
        margin-top: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .konfirmasi-produk {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .produk-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 15px;
        border: 1px solid #eee;
    }

    .produk-info {
        flex-grow: 1;
    }

    .produk-nama {
        font-size: 1.1em;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .konfirmasi-harga {
        margin-top: 10px;
    }

    .harga-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.95em;
        color: #555;
    }

    .harga-item.total {
        font-weight: bold;
        color: #28a745;
        /* Or any other prominent color */
        border-top: 1px solid #eee;
        padding-top: 10px;
        margin-top: 10px;
    }

    .harga-item.diskon {
        color: #dc3545;
        /* Red color for discount */
    }

    .modal-actions {
        margin-top: 25px;
        text-align: right;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .modal-actions button {
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.95em;
        cursor: pointer;
        transition: opacity 0.2s ease-in-out;
    }

    .modal-actions button:hover {
        opacity: 0.8;
    }

    .modal-actions .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }

    .modal-actions .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    .review-images {
        display: flex;
        flex-direction: row;
        overflow-x: auto;
        /* Aktifkan horizontal scroll jika konten meluap */
        white-space: nowrap;
        /* Mencegah gambar turun ke baris baru */
        margin-bottom: 10px;
        /* Berikan sedikit jarak di bawah area gambar */
        padding-bottom: 5px;
        /* Opsional: ruang di bawah gambar untuk scrollbar */
    }

    .review-images img {
        width: 70px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 10px;
        /* Jarak antar gambar */
        flex-shrink: 0;
        /* Mencegah gambar mengecil */
    }

    .review-images img:last-child {
        margin-right: 0;
        /* Hilangkan margin kanan pada gambar terakhir */
    }

    .user-reviews {
        margin-top: 15px;
        max-height: 680px;
        /* Sesuaikan tinggi maksimal sesuai kebutuhan Anda (misalnya, tinggi 3 ulasan + sedikit ruang) */
        overflow-y: auto;
        /* Aktifkan vertical scroll jika konten meluap */
        padding-right: 10px;
        /* Opsional: ruang untuk scrollbar */
    }

    .user-review {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .user-info .rating-stars .star {
        color: gold;
        font-size: 1em;
        margin-right: 2px;
    }

    .user-info .rating-stars .star-empty {
        color: #ccc;
        font-size: 1em;
        margin-right: 2px;
    }

    .review-content {
        display: flex;
        align-items: flex-start;
    }

    .review-images {
        display: flex;
        flex-direction: row;
        overflow-x: auto;
        white-space: nowrap;
        margin-bottom: 10px;
        padding-bottom: 5px;
    }

    .review-images img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 5px;
        flex-shrink: 0;
    }

    .review-images img:last-child {
        margin-right: 0;
    }

    .review-text {
        margin-top: 0;
    }

    .review-text a {
        font-weight: bold;
    }
</style>





<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeSelect = document.getElementById('ukuran_produk_select');
        const jumlahInput = document.getElementById('jumlahOrderInput');
        const plusButton = document.getElementById('plusButton');
        const minusButton = document.getElementById('minusButton');
        const hargaSatuanDisplay = document.getElementById('hargaSatuanDisplay');
        const summaryTotalPembayaran = document.getElementById('summaryTotalPembayaran');
        const summaryJumlahOrder = document.getElementById('summaryJumlahOrder');

        function updateHargaDanTotal() {
            const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
            const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
            const jumlah = parseInt(jumlahInput.value) || 1;
            if (hargaSatuanDisplay) hargaSatuanDisplay.innerText = harga.toLocaleString('id-ID');
            if (summaryTotalPembayaran) summaryTotalPembayaran.innerText = (harga * jumlah).toLocaleString('id-ID');
            if (summaryJumlahOrder) summaryJumlahOrder.innerText = jumlah;
        }

        sizeSelect.addEventListener('change', updateHargaDanTotal);
        jumlahInput.addEventListener('input', updateHargaDanTotal);
        if (plusButton) {
            plusButton.addEventListener('click', function () {
                jumlahInput.value = parseInt(jumlahInput.value) + 1;
                updateHargaDanTotal();
            });
        }
        if (minusButton) {
            minusButton.addEventListener('click', function () {
                if (parseInt(jumlahInput.value) > 1) {
                    jumlahInput.value = parseInt(jumlahInput.value) - 1;
                    updateHargaDanTotal();
                }
            });
        }
        // Initial update
        updateHargaDanTotal();
    });
</script>

<script>
    function highlightStars(selectedStar) {
        const stars = document.querySelectorAll('.star-filter');
        const rating = parseInt(selectedStar.getAttribute('data-rating'));

        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.style.color = 'gold'; // Warna emas yang lebih pekat adalah default
            } else {
                star.style.color = '#ccc';
            }
        });
    }
</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('ukuran_produk_select');
            const customInput = document.getElementById('ukuran_produk_custom');
            const hiddenInput = document.getElementById('ukuran_produk_final');

            function updateFinalValue() {
                if (select.value === 'custom') {
                    customInput.classList.remove('d-none');
                    hiddenInput.value = customInput.value;
                } else {
                    customInput.classList.add('d-none');
                    hiddenInput.value = select.value;
                }
            }

            select.addEventListener('change', updateFinalValue);
            customInput.addEventListener('input', function () {
                hiddenInput.value = customInput.value;
            });

            // Panggil saat pertama kali jika old value adalah custom
            if (select.value === 'custom') {
                customInput.classList.remove('d-none');
            }
        });
    </script>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectEl = document.getElementById('ukuran_produk_select');
        const customInput = document.getElementById('ukuran_produk_custom');
        const hiddenFinal = document.getElementById('ukuran_produk_final');

        function handleChange() {
            const selectedValue = selectEl.value;
            if (selectedValue === 'custom') {
                customInput.classList.remove('d-none');
                hiddenFinal.value = customInput.value; // Isi sementara
            } else {
                customInput.classList.add('d-none');
                hiddenFinal.value = selectedValue;
            }
        }

        selectEl.addEventListener('change', handleChange);

        // Update hidden field saat user mengetik ukuran custom
        customInput.addEventListener('input', function () {
            hiddenFinal.value = this.value;
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const beliSekarangBtn = document.getElementById('beliSekarangBtn');
        const sizeSelect = document.getElementById('ukuran_produk_select');
        const fileInput = document.getElementById('uploadFile');
        
        // Initially disable the button
        beliSekarangBtn.disabled = true;
        
        // Function to check if all required fields are filled
        function validateForm() {
            const isSizeSelected = sizeSelect && sizeSelect.value !== '';
            const isFileSelected = fileInput && fileInput.files.length > 0;
            
            // Enable button only if both size and file are selected
            beliSekarangBtn.disabled = !(isSizeSelected && isFileSelected);
            
            // Add visual feedback
            if (!isSizeSelected) {
                sizeSelect.classList.add('is-invalid');
            } else {
                sizeSelect.classList.remove('is-invalid');
            }
            
            if (!isFileSelected) {
                fileInput.classList.add('is-invalid');
            } else {
                fileInput.classList.remove('is-invalid');
            }
        }
        
        // Add event listeners for validation
        if (sizeSelect) {
            sizeSelect.addEventListener('change', validateForm);
        }
        
        if (fileInput) {
            fileInput.addEventListener('change', validateForm);
        }
        
        // Add click handler for the button
        beliSekarangBtn.addEventListener('click', function() {
            // Validate again before proceeding
            if (beliSekarangBtn.disabled) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Lengkapi Data',
                    text: 'Silakan pilih ukuran dan upload file desain terlebih dahulu',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Get product details
            const productId = '{{ $produk->IdRoster }}';
                            const productName = '{{ $produk->NamaRoster }}';
            const productPrice = {{ $produk->HargaProduk }};
            const productImg = '{{ $produk->Img }}';
            const quantity = document.getElementById('jumlahOrderInput').value;
            const size = sizeSelect.value;
            const designFile = fileInput.files[0];
            
            // Calculate subtotal
            var subtotal = productPrice * quantity;
            
            // Create form data
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('id', productId);
            formData.append('nama', productName);
            formData.append('harga', productPrice);
            formData.append('img', productImg);
            formData.append('quantity', quantity);
            formData.append('ukuran', size);
            formData.append('ukuran_label', sizeSelect.value === 'custom' ? customInput.value : sizeSelect.options[sizeSelect.selectedIndex].text);
            formData.append('subtotal', subtotal);
            if (designFile) {
                formData.append('design_file', designFile);
            }
            
            // Show loading state
            beliSekarangBtn.disabled = true;
            beliSekarangBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            
            // Add to cart via AJAX
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                if (response.ok) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk berhasil ditambahkan ke keranjang',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Redirect to cart page
                        window.location.href = '{{ route("cart") }}';
                    });
                } else {
                    let errorMsg = 'Terjadi kesalahan saat menambahkan ke keranjang';
                    if (response.status === 422) {
                        const data = await response.json();
                        errorMsg = Object.values(data.errors).join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: errorMsg
                    });
                    modalBeliSekarangBtn.disabled = false;
                    modalBeliSekarangBtn.innerHTML = 'Beli Sekarang';
                }
            })
            .catch(async error => {
                let errorMsg = error.message || 'Terjadi kesalahan saat menambahkan ke keranjang';
                if (error.response && error.response.status === 422) {
                    const data = await error.response.json();
                    errorMsg = Object.values(data.errors).join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: errorMsg
                });
                modalBeliSekarangBtn.disabled = false;
                modalBeliSekarangBtn.innerHTML = 'Beli Sekarang';
            });
        });
    });
</script>

<script>
function updateHargaSatuan() {
    var select = document.getElementById('ukuran_produk_select');
    var harga = select.options[select.selectedIndex].getAttribute('data-harga');
    harga = parseInt(harga) || 0;

    var jumlah = parseInt(document.getElementById('jumlahOrderInput').value) || 1;

    // Calculate total
    var total = harga * jumlah;

    // Update all price displays
    document.getElementById('displayHargaSatuan').innerText = harga.toLocaleString('id-ID');
    var hargaSatuanDisplay = document.getElementById('hargaSatuanDisplay');
    if (hargaSatuanDisplay) {
        hargaSatuanDisplay.innerText = harga.toLocaleString('id-ID');
    }

    // Disable Beli Sekarang if harga is 0
    var beliBtn = document.getElementById('beliSekarangBtn');
    if (beliBtn) {
        beliBtn.disabled = (harga === 0);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateHargaSatuan();
    document.getElementById('ukuran_produk_select').addEventListener('change', updateHargaSatuan);
    document.getElementById('jumlahOrderInput').addEventListener('input', updateHargaSatuan);
});
</script>