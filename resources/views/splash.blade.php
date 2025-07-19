<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usaha Distributor Keluarga Sehati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Pastikan html dan body mengisi seluruh tinggi viewport dan tidak ada overflow global */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Mencegah scrollbar pada level html/body */
        }

        body {
            /* Warna latar belakang diubah menjadi #A41524 */
            background: #A41524;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            overflow: hidden; /* Pastikan body juga tidak memiliki scrollbar */
        }
        
        .splash-container {
            text-align: center;
            color: white;
            padding: 3rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            width: 90%;
            /* Menambahkan overflow hidden agar konten di dalam container tidak menyebabkan scroll */
            overflow: hidden; 
            /* Menyesuaikan tinggi agar tidak melebihi viewport */
            max-height: calc(100vh - 2rem); /* 2rem untuk padding body */
            display: flex; /* Menggunakan flexbox untuk menengahkan konten */
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .company-title {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: slideInUp 1s ease-out;
        }
        
        .logo-container {
            margin: 2rem 0;
            animation: zoomIn 1s ease-out 0.3s both;
        }
        
        .logo-image {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        
        .logo-image:hover {
            transform: scale(1.05);
        }
        
        .company-subtitle {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            animation: slideInUp 1s ease-out 0.6s both;
        }
        
        .enter-btn {
            /* Warna tombol disesuaikan dengan tema #A41524 */
            background: #A41524; 
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            animation: slideInUp 1s ease-out 0.9s both;
            font-weight: 600;
        }
        
        .enter-btn:hover {
            transform: translateY(-3px);
            /* Warna shadow tombol disesuaikan */
            box-shadow: 0 10px 25px rgba(164, 21, 36, 0.4); 
            color: white;
        }
        
        .countdown-timer {
            font-size: 0.9rem;
            margin-top: 1rem;
            opacity: 0.8;
            animation: slideInUp 1s ease-out 1.2s both;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .loading-dots {
            display: inline-block;
            margin-top: 1rem;
        }
        
        .loading-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            margin: 0 2px;
            animation: loading 1.4s infinite ease-in-out;
        }
        
        .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots span:nth-child(2) { animation-delay: -0.16s; }
        .loading-dots span:nth-child(3) { animation-delay: 0s; }
        
        @keyframes loading {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1.2); opacity: 1; }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .company-title {
                font-size: 1.8rem;
            }
            
            .company-subtitle {
                font-size: 1.6rem;
            }
            
            .logo-image {
                max-width: 120px;
                max-height: 120px;
            }
            
            .splash-container {
                padding: 2rem 1.5rem;
                max-height: calc(100vh - 1rem); /* Menyesuaikan untuk padding body di mobile */
            }
        }

        @media (max-width: 480px) {
            .company-title {
                font-size: 1.5rem;
            }
            
            .company-subtitle {
                font-size: 1.3rem;
            }
            
            .logo-image {
                max-width: 100px;
                max-height: 100px;
            }
            
            .splash-container {
                padding: 1.5rem 1rem;
                max-height: calc(100vh - 0.5rem); /* Menyesuaikan untuk padding body di mobile */
            }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <h1 class="company-title">Usaha Distributor</h1>
        
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Keluarga Sehati" class="logo-image">
        </div>
        
        <h2 class="company-subtitle">Keluarga Sehati</h2>
        
        <a href="{{ route('enter') }}" class="enter-btn" id="enterBtn">
            Masuk Aplikasi
        </a>
        
        <div class="countdown-timer" id="countdownTimer">
            Otomatis masuk dalam <span id="countdown">3</span> detik
        </div>
        
        <div class="loading-dots" id="loadingDots" style="display: none;">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <script>
        let countdownValue = 3;
        const countdownElement = document.getElementById('countdown');
        const timerElement = document.getElementById('countdownTimer');
        const enterBtn = document.getElementById('enterBtn');
        const loadingDots = document.getElementById('loadingDots');

        // Countdown timer
        const countdownInterval = setInterval(() => {
            countdownValue--;
            countdownElement.textContent = countdownValue;
            
            if (countdownValue <= 0) {
                clearInterval(countdownInterval);
                autoRedirect();
            }
        }, 1000);

        // Auto redirect function
        function autoRedirect() {
            enterBtn.style.display = 'none';
            timerElement.style.display = 'none';
            loadingDots.style.display = 'inline-block';
            
            setTimeout(() => {
                window.location.href = "{{ route('enter') }}";
            }, 800);
        }

        // Manual click handler
        enterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(countdownInterval);
            
            this.style.display = 'none';
            timerElement.style.display = 'none';
            loadingDots.style.display = 'inline-block';
            
            setTimeout(() => {
                window.location.href = "{{ route('enter') }}";
            }, 800);
        });

        // Hide countdown after animation completes
        setTimeout(() => {
            timerElement.style.display = 'block';
        }, 1500);
    </script>
</body>
</html>
