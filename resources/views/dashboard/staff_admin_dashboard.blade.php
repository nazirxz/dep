@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar untuk Staff Admin --}}
        <div class="col-md-2 d-none d-md-block sidebar">
            <div class="position-sticky">
                <div class="d-flex align-items-center mb-4 mt-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo KS" class="img-fluid rounded-circle me-2" style="width: 40px; height: 40px;">
                    <h5 class="mb-0 text-white">UD KELUARGA SEHATI</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('home') }}">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('staff.items.index') }}">
                            <i class="fas fa-boxes"></i>Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('staff.item.management') }}">
                            <i class="fas fa-cogs"></i>Pengelolaan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('staff.users') }}">
                            <i class="fas fa-users"></i>Users
                        </a>
                    </li>
                    {{-- Bagian bawah sidebar --}}
                    <li class="nav-item" style="margin-top: auto;">
                        <hr class="text-white-50">
                        <a class="nav-link text-white-50" href="#">
                            <i class="fas fa-info-circle"></i>Desain Oleh UD Keluarga Sehati
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); window.handleLogout();">
                            <i class="fas fa-sign-out-alt"></i>Keluar
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Main Content untuk Staff Admin Dashboard --}}
        <div class="col-md-10 offset-md-2 main-content">
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> {{ Auth::user()->role === 'admin' ? 'Staff Admin' : 'User' }}</span>
                {{-- Tombol toggle untuk sidebar di mobile --}}
                <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fas fa-bars"></span>
                </button>
            </div>

            {{-- Menampilkan pesan sesi --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <div>{{ session('info')}}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Bagian Ringkasan (Summary Cards) --}}
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-4">Selamat Datang di Dashboard Staff Admin!</h2>
                    <p class="lead">Anda dapat mengelola operasi harian dan melihat laporan dasar.</p>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>Gunakan navigasi di samping untuk mengakses fitur-fitur yang tersedia.</div>
                    </div>
                </div>

                {{-- Card: Jumlah Total Barang Masuk Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Barang Masuk Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $incomingToday }} Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Total Barang Keluar Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Barang Keluar Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outgoingToday }} Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck-loading fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Transaksi Penjualan Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Transaksi Penjualan Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $salesTransactionsToday }} Nota</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-money-bill-wave fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Transaksi Pembelian Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Transaksi Pembelian Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $purchaseTransactionsToday }} Produk</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Grafik Mingguan --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Statistik Barang Masuk dan Keluar Mingguan</h6>
                            <div class="d-flex align-items-center">
                                <span id="currentWeekPeriod" class="text-muted me-2">{{ $chartPeriod }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="weeklyBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* CSS yang sama seperti sebelumnya */
    .main-content {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        margin-left: 16.66666667%;
        width: 83.33333333%;
    }

    @media (max-width: 767.98px) {
        .main-content {
            margin-left: 0;
            width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .sidebar {
            position: relative;
            min-height: auto;
            width: 100%;
            padding-bottom: 1rem;
        }
    }

    /* Styles for Summary Cards */
    .card.border-left-primary {
        border-left: .25rem solid #4e73df!important;
    }
    .card.border-left-success {
        border-left: .25rem solid #1cc88a!important;
    }
    .card.border-left-info {
        border-left: .25rem solid #36b9cc!important;
    }
    .card.border-left-warning {
        border-left: .25rem solid #f6c23e!important;
    }
    .text-xs {
        font-size: .7rem;
    }
    .font-weight-bold {
        font-weight: 700!important;
    }
    .text-primary {
        color: #4e73df!important;
    }
    .text-success {
        color: #1cc88a!important;
    }
    .text-info {
        color: #36b9cc!important;
    }
    .text-warning {
        color: #f6c23e!important;
    }
    .text-gray-800 {
        color: #5a5c69!important;
    }
    .text-gray-300 {
        color: #dddfeb!important;
    }
    .fa-2x {
        font-size: 2em;
    }

    /* New style for chart height */
    .chart-area {
        height: 400px; /* Adjust this value as needed */
        min-height: 300px; /* Ensure a minimum height */
    }
</style>

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Data from Laravel Controller
        const chartLabels = @json($chartLabels);
        const purchaseData = @json($purchaseTrendData);
        const salesData = @json($salesTrendData);

        const weeklyBarChartCtx = document.getElementById('weeklyBarChart');
        if (weeklyBarChartCtx) {
            new Chart(weeklyBarChartCtx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Barang Masuk',
                        data: purchaseData,
                        backgroundColor: '#4e73df',
                    }, {
                        label: 'Barang Keluar',
                        data: salesData,
                        backgroundColor: '#1cc88a',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah Unit' }
                        },
                        x: {
                            title: { display: true, text: 'Hari' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection
