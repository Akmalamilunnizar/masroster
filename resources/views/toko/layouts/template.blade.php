<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('page_title') | Toko Percetakan</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/toko.css') }}" />


  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom Modal CSS -->
  <link rel="stylesheet" href="{{ asset('css/custom-modal.css') }}">

  <!-- Tambah CSS custom jika perlu -->
  @yield('styles')
  <style>
    .navbar {
      background: linear-gradient(90deg, #f5f8ff);
      border-bottom: 1px solid #828282;
    }

    .navbar-brand,
    .nav-link,
    footer {
      color: white !important;
    }

    footer {
      background-color: #343a40;
      padding: 1rem;
      text-align: center;
      margin-top: 50px;
      color: white;
    }

    .nav-link {
      color: #4318ff !important;
      font-size: 0.95rem;
    }

    /* Padding untuk body agar konten tidak tertutup navbar */

    body {
      background-color: #f5f8ff !important;
    }

    /* Main content spacing */
    .main-content {
      margin-top: 80px;
      padding: 0 15px;
    }

    /* Navbar styling */
    .navbar {
      padding: 0.75rem 0;
    }

    .navbar-brand img {
      transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
      transform: scale(1.05);
    }

    .nav-link {
      color: #4318ff !important;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .nav-link:hover {
      color: #2d1b69 !important;
    }

    /* Search form styling */
    .input-group .form-control {
      border: 2px solid #e2e8f0;
      transition: all 0.3s ease;
    }

    .input-group .form-control:focus {
      border-color: #4318ff;
      box-shadow: 0 0 0 0.2rem rgba(67, 24, 255, 0.25);
    }

    .input-group .btn {
      border: 2px solid #e2e8f0;
      border-left: none;
      transition: all 0.3s ease;
    }

    .input-group .btn:hover {
      background-color: #4318ff;
      border-color: #4318ff;
      color: white;
    }

    /* Cart badge styling */
    .badge {
      font-size: 0.7rem;
      padding: 0.25em 0.5em;
    }

    /* Profile dropdown positioning */
    .dropdown-menu {
      right: 0 !important;
      left: auto !important;
      transform: none !important;
      margin-top: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      min-width: 200px;
    }

    /* Ensure right alignment */
    .ms-auto {
      margin-left: auto !important;
    }

    /* Desktop and large screens */
    @media (min-width: 992px) {
      .navbar-collapse {
        display: flex !important;
        flex-basis: auto;
        flex-grow: 1;
        align-items: center;
        justify-content: space-between;
      }

      .input-group {
        max-width: 350px;
        min-width: 250px;
        flex-shrink: 0;
      }

      .navbar-nav {
        flex-direction: row;
        margin: 0;
      }

      .navbar-nav .nav-item {
        margin: 0 0.5rem;
      }

      .d-flex.gap-3 {
        gap: 1rem !important;
        margin: 0;
        flex-shrink: 0;
      }
    }

    /* Responsive design */
    @media (max-width: 991px) {
      .main-content {
        margin-top: 70px;
      }

      .input-group {
        max-width: 100% !important;
        margin: 0.5rem 0;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        margin-top: 60px;
        padding: 0 10px;
      }

      .navbar-brand img {
        width: 100px;
      }

      .navbar {
        padding: 0.5rem 0;
      }

      .navbar-collapse {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 8px;
        margin-top: 0.5rem;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .input-group {
        margin: 0.75rem 0;
      }

      .navbar-nav {
        text-align: center;
        margin: 1rem 0;
      }

      .navbar-nav .nav-item {
        margin: 0.25rem 0;
      }

      .d-flex.gap-3 {
        justify-content: center;
        margin-top: 1rem;
        gap: 36rem !important;
      }
    }

    @media (max-width: 576px) {
      .main-content {
        margin-top: 50px;
        padding: 0 5px;
      }

      .navbar-brand img {
        width: 80px;
      }

      .navbar {
        padding: 0.4rem 0;
      }

      .navbar-toggler {
        padding: 0.25rem 0.5rem;
        font-size: 0.9rem;
      }

      .navbar-collapse {
        padding: 0.75rem;
        margin-top: 0.25rem;
      }

      .input-group {
        margin: 0.5rem 0;
      }

      .input-group .form-control {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
      }

      .input-group .btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
      }

      .nav-link {
        font-size: 0.95rem;
        padding: 0.5rem 0.75rem;
      }

      .d-flex.gap-3 {
        gap: 1rem !important;
        margin-top: 0.75rem;
      }

      .fs-4 {
        font-size: 1.2rem !important;
      }

      .badge {
        font-size: 0.6rem;
        padding: 0.2em 0.4em;
      }
    }

    /* Extra small devices */
    @media (max-width: 480px) {
      .navbar-brand img {
        width: 70px;
      }

      .navbar-toggler {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
      }

      .input-group .form-control {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
      }

      .input-group .btn {
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
      }

      .nav-link {
        font-size: 0.9rem;
        padding: 0.4rem 0.6rem;
      }

      .fs-4 {
        font-size: 1.1rem !important;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg fixed-top" style="background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); z-index: 1030;">
    <div class="container">
      <!-- Brand Logo -->
      <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
        <img src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" alt="Logo" width="120" height="auto">
      </a>

      <!-- Mobile Toggle Button -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Content -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Search Form - Left Side -->
        <form class="d-flex me-4" action="{{ route('tokodashboard') }}" method="GET">
          <div class="input-group" style="max-width: 350px; min-width: 250px;">
            <input type="text" name="search" class="form-control" placeholder="Cari Produk..." style="border-radius: 25px 0 0 25px; border-right: none;">
            <button class="btn btn-outline-secondary" type="submit" style="border-radius: 0 25px 25px 0; border-left: none;">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </form>

        <!-- Navigation Menu - Center -->
        <ul class="navbar-nav me-4">
          <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('tokodashboard') }}">Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('cart') }}">Cart</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('pesanan') }}">Pesanan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold" href="{{ route('kontak') }}">Kontak</a>
          </li>
        </ul>

        <!-- Right Side Icons - Far Right -->
        <div class="d-flex align-items-center gap-3 ms-auto">
          <!-- Cart Icon -->
          <a href="{{ route('cart') }}" class="position-relative text-decoration-none">
            <i class="bi bi-cart3 fs-4 text-primary"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
              {{ session()->get('cart') ? array_sum(array_column(session()->get('cart'), 'quantity')) : 0 }}
            </span>
          </a>

          <!-- Profile Dropdown -->
          <div class="dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              @auth
                <img src="{{ Auth::user()->img ? asset('storage/' . Auth::user()->img) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->f_name) . '&color=FFFFFF&background=C2185B' }}"
                     alt="Profile" class="rounded-circle" width="40" height="40">
              @else
                <img src="https://ui-avatars.com/api/?name=Guest&color=FFFFFF&background=C2185B"
                     alt="Guest" class="rounded-circle" width="40" height="40">
              @endauth
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('userprofile') }}">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="dropdown-item">Logout</button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="main-content">
    @yield('content')
  </div>


  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom Modal System -->
  <script src="{{ asset('js/custom-modal.js') }}"></script>

  <!-- Session Flash Messages Handler -->
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          @if(session()->has('message'))
              CustomModal.success('{{ session()->get("message") }}', 'Berhasil!');
          @endif

          @if(session()->has('error'))
              CustomModal.error('{{ session()->get("error") }}', 'Error!');
          @endif

          @if(session()->has('warning'))
              CustomModal.warning('{{ session()->get("warning") }}', 'Peringatan!');
          @endif

          @if(session()->has('info'))
              CustomModal.info('{{ session()->get("info") }}', 'Informasi');
          @endif
      });
  </script>

  <!-- JS Bootstrap -->
  @yield('js')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
