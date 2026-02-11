<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password - SIMAS PLN</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 10px; }
        .header { text-align: center; border-bottom: 2px solid #5a8dee; padding-bottom: 10px; }
        .content { padding: 20px 0; }
        .button { display: inline-block; padding: 12px 25px; background-color: #5a8dee; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #5a8dee;">SIMAS PLN Pelaihari</h2>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
            <p>Silakan klik tombol di bawah ini untuk melanjutkan proses reset password:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('reset-password/'.$token.'?email='.$email) }}" class="button">Reset Password Sekarang</a>
            </div>
            <p>Link reset password ini akan kadaluarsa dalam 60 menit.</p>
            <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 SIMAS PLN Pelaihari. Sistem Inventaris Material & Aset Siaga.</p>
        </div>
    </div>
</body>
</html>