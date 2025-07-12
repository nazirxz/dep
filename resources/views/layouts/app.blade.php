<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Usaha Distributor Keluarga Sehati') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
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
        }
        
        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: var(--box-shadow);
            border: none;
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
        }

        .navbar-brand img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .navbar-brand:hover img {
            border-color: rgba(255, 255, 255, 0.8);
            transform: scale(1.05);
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .navbar-nav .nav-link i {
            font-size: 0.9rem;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-lg);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            transform: translateX(5px);
        }

        .dropdown-item.text-danger:hover {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            color: white;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #e9ecef;
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
            min-height: calc(100vh - 160px);
            padding: 2rem 0;
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #34495e 100%);
            color: rgba(255, 255, 255, 0.8);
            margin-top: auto;
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
            .navbar-brand {
                font-size: 1.2rem;
            }

            .navbar-brand img {
                width: 30px;
                height: 30px;
            }

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
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" 
                         onerror="this.style.display='none'">
                    <span>Keluarga Sehati</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">
                                    <i class="fas fa-home"></i>Dashboard
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i>{{ __('Masuk') }}
                                </a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-user-circle me-2"></i>
                                    <span>{{ Auth::user()->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('home') }}">
                                        <i class="fas fa-home"></i>Dashboard
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user"></i>Profil
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cog"></i>Pengaturan
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt"></i>{{ __('Keluar') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="container mt-3">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="container mt-3">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="text-center text-light mt-5">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Add smooth fade-in animation for alerts
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '1';
                    alert.style.transform = 'translateY(0)';
                }, 100);
            });

            // Enhanced navbar behavior
            const navbar = document.querySelector('.navbar');
            let lastScrollTop = 0;

            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 100) {
                    navbar.style.backdropFilter = 'blur(10px)';
                    navbar.style.background = 'linear-gradient(135deg, rgba(52, 152, 219, 0.95) 0%, rgba(41, 128, 185, 0.95) 100%)';
                } else {
                    navbar.style.backdropFilter = 'none';
                    navbar.style.background = 'linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%)';
                }
                
                lastScrollTop = scrollTop;
            });

            // Add loading state to logout form
            const logoutLinks = document.querySelectorAll('a[onclick*="logout-form"]');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                    this.style.pointerEvents = 'none';
                });
            });

            // Enhanced dropdown behavior
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
</body>
</html>