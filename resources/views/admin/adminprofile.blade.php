@extends('admin.layouts.template')
@section('page_title')
CIME | Halaman Profil Admin
@endsection
@section('search')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
        @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <!-- Pesan Sukses -->
                            @if (session('success'))
                                <div class="alert alert-success mt-3">
                                    {{ session('success') }}
                                </div>
                            @endif
            @if(!Auth::check())
                <div class="alert alert-danger">
                    You are not logged in. Please <a href="{{ route('login') }}">login</a> first.
                </div>
            @else
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-pills flex-column flex-md-row mb-3">
                        </ul>
                        <div class="card mb-4">
                            <h5 class="card-header fw-bold">Detail Profil</h5>
                            <form method="POST" action="{{ route('storeprofile') }}" enctype="multipart/form-data" id="profileForm">
                                @csrf
                                <div class="card-body">
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="{{ Auth::user()->img ? asset('uploads/users/' . Auth::user()->img) : asset('uploads/users/images/default-avatar.png') }}" alt="user-avatar"
                                            class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                                        <div class="button-wrapper">
                                            <label for="img" class="btn btn-primary me-2 mb-4" tabindex="0">
                                                <span>Unggah Foto</span>
                                            </label>
                                            <input type="file" id="img" name="img" class="form-control" style="display: none;" accept="image/*" />
                                            <p class="text-muted mb-0">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-0" />
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Kolom Kiri -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="f_name" class="form-label">Nama <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" id="f_name" name="f_name"
                                                    value="{{ old('f_name', Auth::user()->f_name) }}" required />
                                                @error('f_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input class="form-control" type="email" id="email" name="email"
                                                    value="{{ old('email', Auth::user()->email) }}" required />
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Kolom Kanan -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="currentPassword" class="form-label">Password Saat Ini</label>
                                                <input type="password" class="form-control" id="currentPassword"
                                                    name="currentPassword" placeholder="Masukkan password saat ini" />
                                                @error('currentPassword')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="newPassword" class="form-label">Password Baru</label>
                                                <input type="password" class="form-control" id="newPassword" name="newPassword"
                                                    placeholder="Masukkan password baru" />
                                                @error('newPassword')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="newPassword_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                                <input type="password" class="form-control" id="newPassword_confirmation"
                                                    name="newPassword_confirmation" placeholder="Konfirmasi password baru" />
                                            </div>
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-outline-primary w-100"  style="width: 100%; background-color: #2f80ed; color: white; border: none; border-radius: 8px; padding: 12px; font-weight: bold; font-size: 16px; cursor: pointer;">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- Notifikasi Error -->
                            
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('img');
            const image = document.getElementById('uploadedAvatar');
            const form = document.getElementById('profileForm');
            const nameInput = document.getElementById('f_name');

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                // Validasi ukuran file (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    input.value = '';
                    return;
                }

                // Validasi tipe file
                if (!file.type.match('image.*')) {
                    alert('File harus berupa gambar.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                }
                reader.readAsDataURL(file);
            });

            form.addEventListener('submit', function(e) {
                if (!nameInput.value.trim()) {
                    e.preventDefault();
                    alert('Nama wajib diisi');
                    nameInput.focus();
                }
            });
        });
    </script>
    @endpush
@endsection
