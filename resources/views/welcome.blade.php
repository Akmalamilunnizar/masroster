<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Cerdas Pengelolaan Pesanan dan Produksi Percetakan Berbasis Digital - CIME Citra Media">

    <title>CIME | Citra Media - Sistem Manajemen Percetakan Digital</title>

  <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4318FF',
                        secondary: '#1D1E94',
                        accent: '#F53003',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        @keyframes fadeInUp {
            from {
        opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
    }
  </style>
</head>

<body class="font-sans antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="#" class="flex items-center space-x-3">
                        <img src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" alt="CIME Logo" class="h-8 w-8">
                        <span class="text-xl font-bold text-gray-900">CIME</span>
                        <span class="text-sm text-gray-500">Citra Media</span>
                    </a>
  </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition-colors">Features</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 transition-colors">Contact</a>
              </div>

                <!-- Auth Section -->
                <div class="flex items-center space-x-4">
              @auth
                        <!-- Welcome Message -->
                        <div class="hidden sm:flex items-center space-x-2 bg-blue-50 px-3 py-1 rounded-full">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                            <span class="text-sm text-blue-800 font-medium">
                                {{ Auth::user()->f_name ?? Auth::user()->username }}
                            </span>
                        </div>

                        <!-- Dashboard Button -->
              @if(auth()->user()->hasRole('admin'))
                            <a href="{{ url('/admin/dashboard') }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                Dashboard
              </a>
              @else
                            <a href="{{ url('/tokodashboard') }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                Dashboard
              </a>
              @endif

                        <!-- Logout Button -->
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                            <button type="button" id="logout-button" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                  Logout
                </button>
              </form>
              @else
                        <a href="{{ route('login') }}" 
                           class="text-gray-700 hover:text-blue-600 transition-colors text-sm font-medium">
                Login
              </a>
                        <a href="{{ route('register') }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                  Register
                </a>
              @endauth

                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100">
                        <i class="fas fa-bars"></i>
                    </button>
            </div>
          </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section id="home" class="gradient-bg py-20 lg:py-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                        Sistem Cerdas
                        <span class="block text-blue-200">Manajemen Percetakan</span>
                    </h1>
                    <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                        Optimasi manajemen stok di industri percetakan roster menggunakan prediksi penjualan. 
                        Kelola pesanan, produksi, dan inventori dengan efisien.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ url('/admin/dashboard') }}" 
                                   class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    Admin Dashboard
                                </a>
                            @else
                                <a href="{{ url('/tokodashboard') }}" 
                                   class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" 
                               class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                                <i class="fas fa-rocket mr-2"></i>
                                Get Started
                            </a>
                            <a href="{{ route('login') }}" 
                               class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </a>
                        @endauth
          </div>
        </div>

                <!-- Hero Image -->
                <div class="mt-16 text-center">
                    <div class="inline-block p-8 bg-white/10 backdrop-blur-sm rounded-2xl">
                        <img src="{{ asset('dashboard2/assets/img/imgtoko/print2.png') }}" 
                             alt="Printing System" 
                             class="h-64 w-auto mx-auto">
      </div>
    </div>
          </div>
        </section>

        <!-- Product Catalog Section -->
        <section id="catalog" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Katalog <span class="text-blue-600">Produk</span>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Jelajahi berbagai produk percetakan berkualitas tinggi dengan harga yang kompetitif
                    </p>
      </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                            <div class="aspect-w-16 aspect-h-12">
            <img src="{{ asset('storage/' . ($item->Img ?? 'assets/images/poster1.jpeg')) }}"
              alt="{{ $item->NamaProduk }}"
                                     class="w-full h-48 object-cover"
              onerror="this.onerror=null; this.src='{{ asset('assets/images/poster1.jpeg') }}';">
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $item->NamaProduk }}</h3>
                                <p class="text-gray-600 text-sm mb-4">Digital Printing</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xl font-bold text-blue-600">
                                        Rp {{ number_format($minHarga, 0, ',', '.') }}
                                    </span>
                                    <a href="{{ route('detail.produk', ['id' => $item->IdRoster]) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                        Pesan
                                    </a>
              </div>
            </div>
          </div>
          @endforeach
        </div>

                <!-- View All Button -->
                <div class="text-center mt-12">
                    <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                        Lihat Semua Produk
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
      </div>
    </div>
  </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Keunggulan Utama <span class="text-blue-600">Sistem CIME</span>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Solusi lengkap untuk manajemen percetakan digital yang efisien dan modern
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
          </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Pemesanan Online Praktis</h3>
                        <p class="text-gray-600">
                            Pelanggan dapat memesan produk percetakan kapan saja dan di mana saja melalui website
                        </p>
      </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-box text-green-600 text-2xl"></i>
            </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Katalog Produk Lengkap</h3>
                        <p class="text-gray-600">
                            Menampilkan berbagai produk percetakan dengan informasi harga dan deskripsi yang jelas
                        </p>
        </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check-circle text-purple-600 text-2xl"></i>
            </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Status Real-time</h3>
                        <p class="text-gray-600">
                            Pelanggan bisa memantau status pesanan secara langsung dari proses desain hingga pengiriman
                        </p>
        </div>

                    <!-- Feature 4 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-cogs text-orange-600 text-2xl"></i>
            </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Manajemen Admin Efisien</h3>
                        <p class="text-gray-600">
                            Admin dapat dengan mudah mengelola pesanan, produk, stok, dan data pelanggan
                        </p>
        </div>

                    <!-- Feature 5 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-mobile-alt text-red-600 text-2xl"></i>
            </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Antarmuka Ramah Pengguna</h3>
                        <p class="text-gray-600">
                            Tampilan sistem yang responsif dan mudah digunakan untuk semua kalangan pelanggan
                        </p>
        </div>

                    <!-- Feature 6 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-chart-line text-indigo-600 text-2xl"></i>
            </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Analisis & Prediksi</h3>
                        <p class="text-gray-600">
                            Sistem prediksi penjualan untuk optimasi manajemen stok dan perencanaan produksi
                        </p>
        </div>
      </div>
    </div>
  </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                            Mengenal Sistem Cerdas
                            <span class="text-blue-600">Manajemen Percetakan</span>
                        </h2>
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                Sistem Cerdas Pengelolaan Pesanan dan Produksi Percetakan Berbasis Digital adalah sistem yang mengintegrasikan
                teknologi untuk mengelola dan memonitor pesanan serta proses produksi percetakan secara otomatis.
                        </p>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                Sistem ini dapat mengoptimalkan alur kerja dari pemesanan produk percetakan, pengaturan jadwal produksi,
                            hingga pengiriman, dengan pemantauan status pesanan secara real-time dan prediksi penjualan untuk
                            optimasi manajemen stok.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            @auth
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ url('/admin/dashboard') }}" 
                                       class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                        <i class="fas fa-tachometer-alt mr-2"></i>
                                        Admin Dashboard
                                    </a>
                                @else
                                    <a href="{{ url('/tokodashboard') }}" 
                                       class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                        <i class="fas fa-tachometer-alt mr-2"></i>
                                        Dashboard
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('register') }}" 
                                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                    <i class="fas fa-rocket mr-2"></i>
                                    Mulai Sekarang
                                </a>
                            @endauth
                            <a href="#contact" 
                               class="border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors font-semibold">
                                <i class="fas fa-phone mr-2"></i>
                                Hubungi Kami
                            </a>
            </div>
          </div>
                    <div class="text-center">
                        <div class="inline-block p-8 bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl">
                            <img src="{{ asset('dashboard2/assets/img/imgtoko/print3.png') }}" 
                                 alt="Printing System" 
                                 class="h-80 w-auto mx-auto">
            </div>
          </div>
        </div>
      </div>
        </section>

        <!-- Location Section -->
        <section id="location" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="text-center">
                            <img src="{{ asset('assets/images/about/cimelocations.png') }}" 
                                 alt="CIME Location" 
                                 class="h-80 w-auto mx-auto rounded-xl shadow-lg">
          </div>
          </div>
                    <div class="order-1 lg:order-2">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                            Lokasi Kami
                            <span class="text-blue-600">Percetakan Citra Media</span>
                        </h2>
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            Citra Media adalah usaha percetakan di Pamekasan yang melayani berbagai kebutuhan cetak seperti 
                            undangan, brosur, banner, dan kartu nama. Kami hadir untuk memberikan hasil cetak berkualitas 
                            dengan harga bersahabat dan pelayanan cepat.
                        </p>
                        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
              </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-2">Alamat</h3>
                                    <p class="text-gray-600">
                                        Sekarputih, Laden, Kec. Pamekasan,<br>
                                        Kabupaten Pamekasan, Jawa Timur 69317
                                    </p>
            </div>
          </div>
            </div>
                        <a href="https://www.google.com/maps/place/Percetakan+Citra+Media/@-7.1693678,113.4758272,17z/data=!4m14!1m7!3m6!1s0x2dd77e7512343c49:0x82e78bef3d99a4fc!2sPercetakan+Citra+Media!8m2!3d-7.169467!4d113.4758246!16s%2Fg%2F11g9jgjf93!3m5!1s0x2dd77e7512343c49:0x82e78bef3d99a4fc!8m2!3d-7.169467!4d113.4758246!16s%2Fg%2F11g9jgjf93?entry=ttu&g_ep=EgoyMDI1MDUxNS4xIKXMDSoJLDEwMjExNDUzSAFQAw%3D%3D" 
                           target="_blank" 
                           class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Temukan Lokasi Kami
                </a>
              </div>
            </div>
          </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" alt="CIME Logo" class="h-10 w-10">
                        <div>
                            <h3 class="text-xl font-bold">CIME</h3>
                            <p class="text-gray-400 text-sm">Citra Media</p>
        </div>
      </div>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Citra Media adalah usaha percetakan di Pamekasan yang melayani berbagai kebutuhan cetak seperti 
                        undangan, brosur, banner, dan kartu nama. Kami hadir untuk memberikan hasil cetak berkualitas 
                        dengan harga bersahabat dan pelayanan cepat.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://www.instagram.com/genks.the/" 
                           class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/genks.the/" 
                           class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com/genks.the/" 
                           class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.instagram.com/genks.the/" 
                           class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center hover:bg-blue-800 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
              </div>
            </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Contact Us</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-phone text-blue-400 mt-1"></i>
                            <div>
                                <p class="text-gray-300">0896 2716 0919</p>
                </div>
              </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-envelope text-blue-400 mt-1"></i>
                            <div>
                                <p class="text-gray-300">Citramedia@gmail.com</p>
            </div>
          </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt text-blue-400 mt-1"></i>
                            <div>
                                <p class="text-gray-300">
                                    Kabupaten Pamekasan,<br>
                                    Jawa Timur 68121<br>
                                    Indonesia
                  </p>
                </div>
              </div>
            </div>
          </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#features" class="text-gray-300 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#about" class="text-gray-300 hover:text-white transition-colors">About</a></li>
                        <li><a href="#location" class="text-gray-300 hover:text-white transition-colors">Location</a></li>
                        @guest
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors">Login</a></li>
                            <li><a href="{{ route('register') }}" class="text-gray-300 hover:text-white transition-colors">Register</a></li>
                        @endguest
                    </ul>
        </div>
      </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        © {{ date('Y') }} Percetakan Citra Media. All rights reserved.
                    </p>
                    <p class="text-gray-400 text-sm mt-2 md:mt-0">
                        Kabupaten Pamekasan, Jawa Timur
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" id="back-to-top" class="fixed bottom-8 right-8 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-colors opacity-0 pointer-events-none">
        <i class="fas fa-chevron-up"></i>
    </a>

    <!-- Scripts -->
    <script>
        // Logout functionality
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.getElementById('logout-button');
            if (logoutButton) {
                logoutButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: "Anda yakin ingin keluar?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            }

            // Back to top functionality
            const backToTopButton = document.getElementById('back-to-top');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                    backToTopButton.classList.add('opacity-100');
                } else {
                    backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                    backToTopButton.classList.remove('opacity-100');
                }
            });

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
            behavior: 'smooth'
          });
        });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
      });
    </script>

</body>
</html>


