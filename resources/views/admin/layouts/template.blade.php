<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard2/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>@yield('page_title')</title>

    <meta name="description" content="" />


    <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard2/assets/vendor/fonts/boxicons.css') }}" />

    <link rel="stylesheet" href="{{ asset('dashboard2/assets/vendor/css/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard2/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard2/assets/css/demo.css') }}" />

    <link rel="stylesheet"
        href="{{ asset('dashboard2/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard2/assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/sass/style.scss'])
    <script src="{{ asset('dashboard2/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('dashboard2/assets/js/config.js') }}"></script>

    {{-- Kustom CSS untuk Layout dan Scrolling --}}
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        .layout-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .layout-container {
            display: flex;
            flex-grow: 1;
        }

        .layout-page {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .layout-navbar {
            flex-shrink: 0;
            height: 65px;
            z-index: 10;
            position: sticky;
            top: 0;
            width: 100%;
        }

        .content-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 20px;
            box-sizing: border-box;
        }

        /* Override padding bawaan Bootstrap/Theme pada container-p-y */
        .container-xxl.container-p-y {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: var(--bs-gutter-x, 1.5rem);
            padding-right: var(--bs-gutter-x, 1.5rem);
        }

        /* --- Perbaikan CSS Sidebar --- */
        #layout-menu {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f0f4f8;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            width: 280px !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            z-index: 1000 !important;
            overflow-y: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #layout-menu .menu-inner {
            flex-grow: 1;
            overflow-y: auto;
            padding-left: 10px;
            padding-right: 10px;
        }

        /* Essential layout fixes */
        .layout-page {
            margin-left: 280px !important;
            width: calc(100% - 280px) !important;
            position: relative !important;
            z-index: 1 !important;
            min-height: 100vh !important;
            background-color: rgb(240, 246, 250) !important;
            margin-top: 0 !important;
            margin-right: 0 !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
        }
        
        /* Ensure content is not hidden */
        .layout-container {
            display: flex !important;
            min-height: 100vh !important;
            width: 100% !important;
        }
        
        /* Force content to be visible */
        .container-xxl {
            margin-left: 0 !important;
            padding-left: 0.5rem !important;
            width: 100% !important;
            max-width: none !important;
        }
        
        /* Ensure form content is properly positioned */
        .card {
            position: relative !important;
            z-index: 2 !important;
            margin-left: 0 !important;
            width: 100% !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
        
        /* Override any conflicting styles */
        body {
            overflow-x: hidden !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Fix for form elements */
        .form-control, .form-select {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Ensure proper spacing */
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100% !important;
        }
        
        .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            box-sizing: border-box !important;
        }
        
        /* Fix navbar positioning */
        .layout-navbar {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
            background: linear-gradient(135deg,rgb(255, 255, 255),rgb(255, 255, 255)) !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Fix content wrapper */
        .content-wrapper {
            flex: 1 !important;
            padding: 0.5rem !important;
            overflow-y: auto !important;
            background-color: rgb(240, 246, 250) !important;
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        /* Ensure proper form layout */
        .container-p-y {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
        
        /* Reduce excessive spacing */
        .py-3 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 0.75rem !important;
        }
        
        .mb-3 {
            margin-bottom: 0.5rem !important;
        }
        
        /* Fix card spacing */
        .card {
            margin-bottom: 0.75rem !important;
        }
        
        .card-body {
            padding: 0.75rem !important;
        }
        
        .card-header {
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Fix form row spacing */
        .row {
            margin-left: -0.375rem !important;
            margin-right: -0.375rem !important;
            width: 100% !important;
        }
        
        .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
            padding-left: 0.375rem !important;
            padding-right: 0.375rem !important;
            box-sizing: border-box !important;
        }
        
        /* Fix input groups */
        .input-group {
            width: 100% !important;
        }
        
        .input-group-text {
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
        }
        
        /* Fix form elements spacing */
        .form-label {
            margin-bottom: 0.25rem !important;
            font-weight: 500 !important;
        }
        
        .form-control, .form-select {
            margin-bottom: 0.5rem !important;
        }
        
        /* Fix page header spacing */
        .page-header {
            margin-bottom: 0.75rem !important;
            padding: 0.75rem !important;
        }
        
        /* Fix alert spacing */
        .alert {
            margin-bottom: 0.75rem !important;
        }
        
        /* Fix table spacing */
        .table th, .table td {
            padding: 0.5rem 0.375rem !important;
            vertical-align: middle !important;
        }
        
        .table thead th {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
        }
        
        .table tbody td {
            font-size: 0.875rem !important;
        }
        
        /* Fix container spacing */
        .container-xxl {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        
        /* CRITICAL: Fix sidebar overlap for transaction pages */
        .layout-page {
            margin-left: 280px !important;
            width: calc(100% - 280px) !important;
            padding-left: 0.5rem !important;
        }
        
        .container-xxl.flex-grow-1.container-p-y {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            margin-left: 0 !important;
        }

        /* Tombol Logout */
        #layout-menu .menu-item.mt-auto {
            padding: 0 20px;
            margin-top: auto;
        }

        /* Scrollbar styling untuk sidebar (terapkan pada .menu-inner) */
        #layout-menu .menu-inner::-webkit-scrollbar {
            width: 6px;
        }

        #layout-menu .menu-inner::-webkit-scrollbar-track {
            background: transparent;
        }

        #layout-menu .menu-inner::-webkit-scrollbar-thumb {
            background-color: rgba(0, 123, 255, 0.3);
            border-radius: 10px;
        }

        /* Menu link style (tetap) */
        #layout-menu .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333 !important;
            font-weight: 600;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 1rem;
        }

        #layout-menu .menu-link i {
            margin-right: 12px;
            font-size: 1.25rem;
        }

        #layout-menu .menu-item.active .menu-link {
            background-color: #d0e7ff;
            color: #007bff !important;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
        }

        #layout-menu .menu-link:hover {
            background-color: #e8f4fd;
            color: #007bff !important;
            text-decoration: none;
        }

        #layout-menu .menu-inner li {
            margin-bottom: 8px;
        }

        /* Brand logo (tetap) */
        .app-brand-link img {
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .app-brand-link img:hover {
            transform: scale(1.05);
        }

        @keyframes zoomOut {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(0.8);
            }
        }

        .logo-zoom-out {
            animation: zoomOut 1.5s ease-in-out infinite alternate;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" style="background-color: #f0f4f8; box-shadow: 2px 0 5px rgba(0,0,0,0.05); height: 100vh;">
                <div class="main-sidebar sidebar-style-2" style="padding: 20px; display: flex; justify-content: center; border-bottom: 1px solid #d1d9e6;">
                    <a href="http://127.0.0.1:8000" class="app-brand-link" style="display: flex; justify-content: center; width: 100%;">
                        <img
                            src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}"
                            alt="Logo"
                            class="logo-zoom-out"
                            style="width: 140px; height: auto; border-radius: 8px;" />
                    </a>
                </div>

                <ul class="menu-inner py-3" style="padding-left: 10px; padding-right: 10px; margin-bottom: 100px;">
                    <li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admindashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/all-produk*') || request()->is('admin/add-produk*') || request()->is('admin/edit-produk') ? 'active' : '' }}">
                        <a href="{{ route('allproduk') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-package'></i>
                            <div>Daftar Roster</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/all-type*') || request()->is('admin/edit-type*') || request()->is('admin/add-type') ? 'active' : '' }}">
                        <a href="{{ route('alltype') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-package'></i>
                            <div>Daftar Jenis Produk</div>
                        </a>
                    </li>                    
                    <li class="menu-item {{ request()->is('admin/all-tipe-roster*') || request()->is('admin/edit-tipe-roster*') || request()->is('admin/add-tipe-roster') ? 'active' : '' }}">
                        <a href="{{ route('alltiperoster') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-package'></i>
                            <div>Daftar Tipe Produk</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/all-motif*') || request()->is('admin/edit-motif*') || request()->is('admin/add-motif') ? 'active' : '' }}">
                        <a href="{{ route('allmotif') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-package'></i>
                            <div>Daftar Motif Produk</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/all-ukuran*') || request()->is('admin/edit-ukuran*') || request()->is('admin/add-ukuran') ? 'active' : '' }}">
                        <a href="{{ route('allukuran') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-ruler'></i>
                            <div>Daftar Ukuran</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/all-customer*') || request()->is('customer*') ? 'active' : '' }}">
                        <a href="{{ route('allcustomer') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-group"></i>
                            <div>Daftar Customer</div>
                        </a>
                    </li>
                    
                    <li class="menu-item {{ request()->is('admin/all-transaksi*') ? 'active' : '' }}">
                        <a href="{{ route('alltransaksi') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-collection"></i>
                            <div>Data Transaksi</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/forecast*') ? 'active' : '' }}">
                        <a href="{{ route('forecast.form') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-line-chart'></i>
                            <div>Forecasting</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/detail-harga*') ? 'active' : '' }}">
                        <a href="{{ route('detailharga.index') }}" class="menu-link">
                            <i class='menu-icon tf-icons bx bx-dollar-circle'></i>
                            <div>Detail Harga</div>
                        </a>
                    </li>

                </ul>
                <!-- Tombol Logout di bagian bawah sidebar -->
                <div style="position: fixed; bottom: 20px; left: 0; width: 280px; padding: 0 20px; background-color: #f0f4f8;">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                    <a href="#" onclick="confirmLogout()"
                        style="display: block; width: 100%; background-color: #2f80ed; color: white; text-align: center; border-radius: 8px; padding: 12px; font-weight: bold; font-size: 16px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        Logout
                    </a>
                </div>

                <script>
                    function confirmLogout() {
                        Swal.fire({
                            title: 'Apakah kamu yakin ingin keluar?',
                            text: "Kamu akan keluar dari sesi ini.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, keluar!',
                            cancelButtonText: 'Tidak',
                            customClass: {
                                confirmButton: 'btn btn-outline-primary',
                                cancelButton: 'btn btn-outline-danger'
                            },
                            buttonsStyling: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('logout-form').submit();
                            }
                        })
                    }
                </script>
            </aside>

            <div class="layout-page">

                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <div class="dropdown">
                            <a class="nav-item nav-link px-0 me-xl-4 dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown">
                                <i class="bx bx-menu bx-sm"></i>
                            </a>
                        </div>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        @yield('search')

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ Auth::user() ? asset('uploads/users/' . Auth::user()->img) : asset('dashboard2/assets/img/avatars/default-avatar.png') }}" alt="user-avatar"
                                            class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile') }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ Auth::user() ? asset('uploads/users/' . Auth::user()->img) : asset('dashboard2/assets/img/avatars/default-avatar.png') }}"
                                                            alt="user-avatar" class="d-block rounded" height="100"
                                                            width="100" id="uploadedAvatar" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    @auth
                                                    <span class="fw-medium d-block">{{ Auth::user()->f_name }}</span>
                                                    @endauth
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="content-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- Removed broken script references -->
    <script src="{{ asset('dashboard2/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('dashboard2/assets/vendor/js/menu.js') }}"></script>
    @yield('js')

    <script src="{{ asset('dashboard2/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <script src="{{ asset('dashboard2/assets/js/main.js') }}"></script>

    <script src="{{ asset('dashboard2/assets/js/dashboards-analytics.js') }}"></script>

    <script async defer src="https://buttons.github.io/buttons.js"></script>
    @stack('scripts')
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>




</html>