<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIMAS-PLN</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .header h2 { color: #333; margin-bottom: 10px; }
        .header p { color: #666; font-size: 14px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; }
        .input-group { position: relative; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .form-control { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .form-control:focus { border-color: #5a8dee; box-shadow: 0 0 0 3px rgba(90, 141, 238, 0.1); }
        .btn-submit { width: 100%; padding: 12px; background: #5a8dee; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #4a77ce; }
        .back-link { display: block; margin-top: 20px; font-size: 14px; color: #5a8dee; text-decoration: none; }
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; border: 1px solid #c8e6c9; text-align: left; }
        .alert-error { background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: left; border: 1px solid #ef9a9a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-lock-open" style="font-size: 40px; color: #5a8dee; margin-bottom: 15px;"></i>
            <h2>Lupa Password?</h2>
            <p>Masukkan email Anda dan kami akan mengirimkan instruksi reset password.</p>
        </div>

        {{-- Alert Sukses --}}
        @if (session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Alert Error jika email tidak ditemukan --}}
        @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            <button type="submit" class="btn-submit">Kirim Link Reset</button>
        </form>
        
        <a href="{{ route('login') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </div>
</body>
</html>