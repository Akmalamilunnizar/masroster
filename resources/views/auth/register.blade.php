<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>CIME | Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

   <!-- Favicon -->
  <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans&display=swap" rel="stylesheet" />

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
      body {
        margin: 0;
        padding: 0;
        font-family: 'Public Sans', sans-serif;
        background: url('{{ asset('assets/images/baground1.png') }}') no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
      }


          .logo {
          margin-bottom: 30px;
          transform: scale(1.2); /* 20% lebih besar */
          animation: floatZoom 4s ease-in-out infinite;
      }

      .logo img {
        max-width: 300px;
        height: auto;
      }

      .register-card {
        background: #fff;
        border-radius: 12px;
        padding: 30px 40px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
        min-height: 650px;
      }

      .register-card h2 {
        text-align: center;
        margin-bottom: 25px;
        font-weight: bold;
        color: #333;
        font-family: 'Times New Roman', Times, serif;
      }

      .register-card input[type="text"],
      .register-card input[type="email"],
      .register-card input[type="password"],
      .register-card input[type="tel"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s ease;
        outline: none;
      }

      /* Efek animasi saat focus */
      .register-card input[type="text"]:focus,
      .register-card input[type="email"]:focus,
      .register-card input[type="password"]:focus,
      .register-card input[type="tel"]:focus {
        border-color: #80bdff;
        box-shadow: 0 0 8px rgba(128, 189, 255, 0.7);
      }

      .register-card .form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
      }

      .register-card button {
        width: 100%;
        padding: 12px;
        background-color: #3887ff;
        border: none;
        color: #fff;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 10px;
      }

      .register-card button:hover {
        background-color: #1e6fe2;
      }

      .register-card p {
        text-align: center;
        margin-top: 20px;
        font-size: 0.9rem;
      }

      .register-card a {
        color: #333;
        text-decoration: none;
      }

      .register-card a:hover {
        text-decoration: underline;
      }

      @keyframes floatZoom {
        0% {
          transform: translateY(0) scale(1);
        }

        50% {
          transform: translateY(-10px) scale(1.05);
        }

        100% {
          transform: translateY(0) scale(1);
        }
      }

      .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: -10px;
        margin-bottom: 10px;
      }

      .is-invalid {
        border-color: #dc3545;
      }
    </style>
</head>

<body>

  <!-- Logo Tengah -->
  <div class="logo">
    <img src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" alt="Logo">
  </div>

  <!-- Card Register -->
  <div class="register-card">
    <h2>Register</h2>
    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div class="mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
        @error('name')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
        @error('email')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="nomor_telepon" class="form-label">{{ __('Phone Number') }}</label>
        <input id="nomor_telepon" type="tel" class="form-control @error('nomor_telepon') is-invalid @enderror" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required autocomplete="tel">
        @error('nomor_telepon')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
        @error('password')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
      </div>

      <button type="submit">{{ __('Register') }}</button>

      @if($errors->any())
        <script>
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 9000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
          });

          Toast.fire({
            icon: 'error',
            title: 'Registrasi Gagal!',
            text: '{{ $errors->first() }}'
          });
        </script>
      @endif

      <p>
        Sudah punya akun? <a href="{{ route('login') }}">Login</a>
      </p>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
