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
                    {{-- Bagian bawah sidebar --}}
                    <li class="nav-item" style="margin-top: auto;">
                        <hr class="text-white-50">
                        <a class="nav-link text-white-50" href="#">
                            <i class="fas fa-info-circle"></i>Desain Oleh UD Keluarga Sehati
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Barang Masuk Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">250 Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Total Barang Keluar Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Barang Keluar Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">200 Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck-loading fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Transaksi Penjualan Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Transaksi Penjualan Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">10 Nota</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Transaksi Pembelian Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Transaksi Pembelian Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">3 Produk</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                <button id="prevWeek" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-chevron-left"></i> Sebelumnya</button>
                                <span id="currentWeekPeriod" class="text-muted me-2">1 - 7 Juli 2025</span>
                                <button id="nextWeek" class="btn btn-sm btn-outline-secondary">Berikutnya <i class="fas fa-chevron-right"></i></button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
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

        // Weekly Bar Chart
        const ctx = document.getElementById('weeklyBarChart').getContext('2d');
        let currentWeekStart = new Date();
        // Set to the most recent Monday
        currentWeekStart.setDate(currentWeekStart.getDate() - (currentWeekStart.getDay() + 6) % 7);

        const weeklyData = {
            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
            datasets: [{
                label: 'Barang Masuk',
                backgroundColor: 'rgba(54, 162, 235, 0.8)', // Biru terang
                borderColor: 'rgba(54, 162, 235, 1)',
                data: [150, 180, 160, 200, 170, 190, 140], // Dummy data
                borderRadius: 5,
            }, {
                label: 'Barang Keluar',
                backgroundColor: 'rgba(255, 99, 132, 0.8)', // Merah muda
                borderColor: 'rgba(255, 99, 132, 1)',
                data: [120, 150, 130, 170, 140, 160, 110], // Dummy data
                borderRadius: 5,
            }]
        };

        const weeklyBarChart = new Chart(ctx, {
            type: 'bar',
            data: weeklyData,
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        ticks: {
                            min: 0,
                            max: 300, // Max value for Y-axis
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                return value + ' Pcs'; // Add 'Pcs' to Y-axis labels
                            }
                        },
                        grid: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    },
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            fontColor: '#858796'
                        }
                    },
                    tooltip: {
                        titleMarginBottom: 10,
                        titleFontColor: '#ffffff',
                        titleFontSize: 14,
                        backgroundColor: "rgb(0,0,0)",
                        bodyFontColor: "#ffffff",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' Pcs';
                                return label;
                            }
                        }
                    },
                },
                responsive: true
            }
        });

        function updateChartPeriod() {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const endDate = new Date(currentWeekStart);
            endDate.setDate(currentWeekStart.getDate() + 6);
            document.getElementById('currentWeekPeriod').innerText =
                `${currentWeekStart.toLocaleDateString('id-ID', { day: 'numeric', month: 'long' })} - ${endDate.toLocaleDateString('id-ID', options)}`;
        }

        document.getElementById('prevWeek').addEventListener('click', function() {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            updateChartPeriod();
            // In a real application, you would fetch new data here
            // For now, we'll just update the period display
        });

        document.getElementById('nextWeek').addEventListener('click', function() {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            updateChartPeriod();
            // In a real application, you would fetch new data here
            // For now, we'll just update the period display
        });

        updateChartPeriod(); // Initial update
    });
</script>
@endpush
