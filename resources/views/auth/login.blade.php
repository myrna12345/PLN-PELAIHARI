<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventaris</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .logo-img {
            max-width: 140px; 
            height: auto;
            margin-bottom: 15px;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i.icon-left {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .input-group i.toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .input-group i.toggle-password:hover {
            color: #5a8dee;
        }

        .form-control {
            width: 100%;
            padding: 12px 40px 12px 40px; 
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            outline: none;
        }

        .form-control:focus {
            border-color: #5a8dee;
            box-shadow: 0 0 0 3px rgba(90, 141, 238, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #5a8dee;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login:hover {
            background-color: #4a77ce;
        }

        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
            border: 1px solid #ef9a9a;
        }

        .remember-me {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }

        .remember-me input {
            margin-right: 8px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .auth-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <img src="{{ asset('images/logo-pln.png') }}" alt="Logo PLN" class="logo-img">
            <h2>Selamat Datang</h2>
            <p>Silakan login untuk masuk ke sistem</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> 
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            {{-- Input Username (Diubah dari Email) --}}
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <i class="fas fa-user icon-left"></i> {{-- Icon diganti ke user --}}
                    <input type="text" name="username" id="username" 
                           class="form-control" 
                           placeholder="Masukkan username" 
                           value="{{ old('username') }}" required autofocus>
                </div>
            </div>

            {{-- Input Password --}}
            <div class="form-group" style="margin-bottom: 10px;">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="password" 
                           class="form-control" 
                           placeholder="••••••••" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <div class="auth-footer">
                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember" style="margin:0; cursor:pointer;">Ingat Saya</label>
                </div>
                <div style="text-align: right;">
                    <a href="{{ route('password.request') }}" style="font-size: 13px; color: #5a8dee; text-decoration: none;">
                        Lupa Password?
                    </a>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Masuk <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
            </button>

            <div style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
                <a href="{{ route('google.login') }}" 
                   style="display: flex; align-items: center; justify-content: center; gap: 10px; background-color: #fff; color: #757575; border: 1px solid #ddd; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google Logo">
                    Masuk dengan Google
                </a>
            </div>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

</body>
</html>