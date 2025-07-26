<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - UD Keluarga Sehati</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #A41524;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: #A41524;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        .company-name {
            color: #A41524;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            color: #666;
            margin: 5px 0 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #A41524;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 20px;
            line-height: 1.8;
        }
        .reset-button {
            display: inline-block;
            background: #A41524;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .reset-button:hover {
            background: #8F1220;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .alternative-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #A41524;
        }
        .alternative-text strong {
            color: #A41524;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .contact-info {
            margin-top: 15px;
        }
        .link {
            color: #A41524;
            word-break: break-all;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .company-name {
                font-size: 20px;
            }
            .reset-button {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-store" style="font-family: FontAwesome;">🏪</i>
            </div>
            <h1 class="company-name">UD KELUARGA SEHATI</h1>
            <p class="subtitle">Sistem Manajemen Inventory</p>
        </div>

        <div class="content">
            <p class="greeting">Halo, {{ $user->name }}!</p>
            
            <div class="message">
                <p>Anda menerima email ini karena kami mendapat permintaan reset password untuk akun Anda di sistem UD Keluarga Sehati.</p>
                
                <p>Untuk mereset password Anda, silakan klik tombol di bawah ini:</p>
            </div>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>

            <div class="alternative-text">
                <p><strong>Jika tombol tidak berfungsi</strong>, silakan salin dan tempel link berikut di browser Anda:</p>
                <p class="link">{{ $resetUrl }}</p>
            </div>

            <div class="warning">
                <p><strong>⚠️ Penting untuk diketahui:</strong></p>
                <ul>
                    <li>Link reset password ini akan <strong>kedaluwarsa dalam 60 menit</strong></li>
                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                    <li>Untuk keamanan, jangan bagikan link ini kepada siapa pun</li>
                    <li>Setelah reset password berhasil, link ini tidak dapat digunakan lagi</li>
                </ul>
            </div>

            <div class="message">
                <p>Setelah mengklik link di atas, Anda akan diarahkan ke halaman untuk membuat password baru. Pastikan password baru Anda:</p>
                <ul>
                    <li>Minimal 8 karakter</li>
                    <li>Kombinasi huruf besar, huruf kecil, dan angka</li>
                    <li>Mudah diingat namun sulit ditebak orang lain</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>UD Keluarga Sehati</strong><br>
            Sistem Manajemen Inventory</p>
            
            <div class="contact-info">
                <p>Email ini dikirim secara otomatis, mohon jangan membalas email ini.</p>
                <p>Jika Anda mengalami kesulitan, silakan hubungi administrator sistem.</p>
            </div>
            
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                © {{ date('Y') }} UD Keluarga Sehati. Semua hak dilindungi undang-undang.
            </p>
        </div>
    </div>
</body>
</html>