<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .content {
            line-height: 1.6;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #2563eb; margin: 0;">UDKS</h1>
            <h2 style="color: #64748b; margin: 10px 0 0 0;">Verifikasi Email</h2>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $fullName }}</strong>,</p>
            
            <p>Terima kasih telah mendaftar di aplikasi UDKS. Untuk menyelesaikan proses registrasi, silakan gunakan kode verifikasi berikut:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>Kode verifikasi ini berlaku selama <strong>10 menit</strong>. Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
            
            <p>Untuk keamanan akun Anda, jangan berikan kode ini kepada siapa pun.</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} UDKS. Semua hak dilindungi.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>