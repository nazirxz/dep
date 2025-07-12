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
            overflow-x: hidden;
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
            font-size: 1.1rem;
            margin-top: 0.1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border-left: 4px solid var(--info-color);
        }

        /* Button Styles */
        .btn {
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: white;
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
        .btn:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
            outline: none;
        }
    </style>
</head>
<body>
    <div id="app">
        <main style="height: 100vh; overflow: hidden;">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (!alert.querySelector('.btn-close')) {
                    setTimeout(function() {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(function() {
                            alert.remove();
                        }, 300);
                    }, 5000);
                }
                
                // Add slide in animation
                alert.style.transform = 'translateY(-20px)';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.opacity = '1';
                    alert.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>