<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Usaha Distributor Keluarga Sehati') }}</title>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            --box-shadow-lg: 0 4px 20px rgba(0,0,0,0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #ffffff;
            overflow-x: hidden; /* Mencegah scroll horizontal */
        }
        
        /* Alert Styles */
        .alert {
            border: none;
            border-radius: var(--border-radius-lg);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert i {
            margin-top: 2px;
            font-size: 1.1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid var(--info-color);
        }

        .alert-dismissible .btn-close {
            padding: 0.75rem;
            opacity: 0.7;
            transition: var(--transition);
        }

        .alert-dismissible .btn-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* Main Content */
        main {
            min-height: 100vh; /* Mengisi seluruh tinggi viewport */
            padding: 0; /* Hapus padding jika sidebar akan mengisi penuh tinggi */
        }

        /* Footer Styles (jika masih digunakan, sesuaikan posisinya) */
        footer {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #34495e 100%);
            color: rgba(255, 255, 255, 0.8);
            /* margin-top: auto; Hapus atau sesuaikan jika layout berubah */
        }

        footer .container {
            padding: 2rem 0;
        }

        footer a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        footer a:hover {
            color: white;
            text-decoration: underline;
        }

        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: var(--transition);
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info-color), #138496);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            main {
                padding: 1rem 0;
            }

            .alert {
                padding: 0.75rem 1rem;
                flex-direction: column;
                text-align: center;
            }

            .alert i {
                margin-bottom: 0.5rem;
            }
        }

        /* Loading Animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fa-spin {
            animation: spin 1s linear infinite;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus Styles */
        .btn:focus,
        .nav-link:focus,
        .dropdown-item:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
            outline: none;
        }

        /* Dashboard specific styles (added for this task) */
        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            min-height: 100vh; /* Sidebar mengisi seluruh tinggi viewport */
            padding-top: 1rem;
            position: fixed; /* Sidebar tetap di tempatnya saat scroll */
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000; /* Pastikan sidebar di atas konten lain */
            overflow-y: auto; /* Aktifkan scroll jika konten sidebar terlalu panjang */
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white !important;
            transform: translateX(5px);
            box-shadow: var(--box-shadow);
        }
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px; /* Align icons */
        }
    </style>
</head>
<body>
    <div id="app">
        <main>
            @yield('content')
        </main>

        <footer class="text-center text-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-2">
                            <strong>&copy; {{ date('Y') }} Usaha Distributor Keluarga Sehati</strong>
                        </p>
                        <p class="mb-1">
                            <small>
                                Platform manajemen distribusi terpercaya untuk mengembangkan bisnis Anda
                            </small>
                        </p>
                        <p class="mb-0">
                            <small>
                                <a href="#" class="me-3">Tentang Kami</a>
                                <a href="#" class="me-3">Kontak</a>
                                <a href="#" class="me-3">Bantuan</a>
                                <a href="#">Kebijakan Privasi</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to logout form
            const logoutForms = document.querySelectorAll('form#logout-form');
            logoutForms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const logoutBtn = form.querySelector('a[onclick*="logout-form"]');
                    if (logoutBtn) {
                        logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                        logoutBtn.style.pointerEvents = 'none';
                    }
                });
            });

            // Enhanced dropdown behavior (if any dropdowns remain, e.g., in sidebar)
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    const dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu) {
                        dropdownMenu.style.animation = 'fadeIn 0.3s ease';
                    }
                });
            });
        });

        // Add CSS animation for dropdown
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    {{-- PENTING: Ini akan merender semua script yang didorong dari tampilan anak --}}
    @stack('scripts')
</body>
</html>
