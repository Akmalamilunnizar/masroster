<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>CIME | Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

   <!-- Favicon -->
  <link rel="shortcut icon" href="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" type="image/png" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans&display=swap" rel="stylesheet" />

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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

      .login-card {
        background: #fff;
        border-radius: 12px;
        padding: 30px 40px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        min-height: 500px;
      }

      .login-card h2 {
        text-align: center;
        margin-bottom: 25px;
        font-weight: bold;
        color: #333;
        font-family: 'Times New Roman', Times, serif;
      }

      .login-card input[type="text"],
      .login-card input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
      }

      .login-card .form-check {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
      }

      .login-card .form-check input {
        margin-right: 8px;
      }

      .login-card .actions {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        font-size: 0.9rem;
      }

      .login-card button {
        width: 100%;
        padding: 12px;
        background-color: #3887ff;
        border: none;
        color: #fff;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
      }

      .login-card button:hover {
        background-color: #1e6fe2;
      }

      .login-card p {
        text-align: center;
        margin-top: 20px;
        font-size: 0.9rem;
      }

      .login-card a {
        color: #333;
        text-decoration: none;
      }

      .login-card a:hover {
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

      .login-card input[type="text"],
      .login-card input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s ease;
        outline: none;
      }

      /* Efek animasi saat focus */
      .login-card input[type="text"]:focus,
      .login-card input[type="password"]:focus {
        border-color: #80bdff;
        box-shadow: 0 0 8px rgba(128, 189, 255, 0.7);
      }
    </style>
</head>

<body>

  <!-- Logo Tengah -->
  <div class="logo">
    <img src="{{ asset('dashboard2/assets/img/icons/logocime.png') }}" alt="Logo">
  </div>

  <!-- Card Login -->
  <div class="login-card">
    <h2>Login</h2>
    <form action="{{ route('login') }}" method="POST">
      @csrf
      <input type="text" name="email" placeholder="Email" required autofocus>
      @error('email')
      @enderror

      <input type="password" name="password" placeholder="Password" required>
      @error('password')
      @enderror

      <div class="actions">
        <div class="form-check">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Ingat saya</label>
        </div>
        <a href="{{ route('password.request') }}">Lupa Password?</a>
      </div>

      <button type="submit">Login</button>

      @if(session('status') || $errors->any())
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

                                @if(session('status'))
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: '{{ session('status') }}'
                                    });
                                @elseif($errors->any())
                                    @if($errors->has('email') || $errors->has('password'))
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Login Gagal !',
                                            text: 'Email atau kata sandi salah !'
                                        });
                                    @else
                                        Toast.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            text: '{{ $errors->first() }}'
                                        });
                                    @endif
                                @endif
                            </script>
                        @endif

      <p>
        Tidak punya akun? <a href="register">Register</a>
      </p>
    </form>
  </div>
</body>


</html>
