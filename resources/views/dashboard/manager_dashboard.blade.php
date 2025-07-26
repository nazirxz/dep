@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar untuk Manajer --}}
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
                        <a class="nav-link" href="{{ route('report.stock') }}">
                            <i class="fas fa-chart-line"></i>Laporan Stok Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('order.items') }}">
                            <i class="fas fa-shopping-basket"></i>Pemesanan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employee.accounts') }}">
                            <i class="fas fa-users"></i>Akun Pegawai
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

        {{-- Main Content Dashboard --}}
        <div class="col-md-10 offset-md-2 main-content"> {{-- offset-md-2 tetap dipertahankan untuk mengimbangi sidebar --}}
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> {{ Auth::user()->role === 'manager' ? 'Manajer' : 'Admin' }}</span>
                {{-- Tombol toggle untuk sidebar di mobile --}}
                <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fas fa-bars"></span>
                </button>
            </div>

            {{-- Menampilkan pesan sesi di dalam konten utama --}}
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

            {{-- Notifikasi Stok Barang --}}
            @if($outOfStockNotifications->count() > 0)
                <div class="alert alert-danger alert-dismissible fade show shadow-sm stock-notification" role="alert">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>STOK HABIS!</strong> {{ $outOfStockNotifications->count() }} Barang: 
                                @foreach($outOfStockNotifications->take(2) as $index => $item)
                                    {{ $item->nama_barang }}@if($index < 1 && $outOfStockNotifications->count() > 1), @endif
                                @endforeach
                                @if($outOfStockNotifications->count() > 2)
                                    dan {{ $outOfStockNotifications->count() - 2 }} lainnya
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if($lowStockNotifications->count() > 0)
                <div class="alert alert-warning alert-dismissible fade show shadow-sm stock-notification" role="alert">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div>
                                <strong>STOK RENDAH!</strong> {{ $lowStockNotifications->count() }} Barang: 
                                @foreach($lowStockNotifications->take(2) as $index => $item)
                                    {{ $item->nama_barang }} ({{ $item->jumlah_barang }})@if($index < 1 && $lowStockNotifications->count() > 1), @endif
                                @endforeach
                                @if($lowStockNotifications->count() > 2)
                                    dan {{ $lowStockNotifications->count() - 2 }} lainnya
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- Konten Dashboard Utama --}}
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">Selamat Datang di Dashboard Manajer!</h2>
                    <p class="lead">Gunakan navigasi di samping untuk mengakses fitur-fitur manajemen.</p>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>Untuk melihat laporan stok dan tren barang, silakan klik "Laporan Stok Barang" di sidebar. Untuk pemesanan barang, klik "Pemesanan Barang". Untuk mengelola akun pegawai, klik "Akun Pegawai".</div>
                    </div>
                </div>

                {{-- Bagian Ringkasan Statistik (Summary Cards) --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Jumlah Barang Masuk Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $incomingToday }} Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c757d;">
                                        Jumlah Barang Keluar Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outgoingToday }} Pcs</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck fa-2x" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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

                {{-- Bagian Grafik Tren Penjualan dan Pembelian (Chart Mingguan) dan Kondisi Stok Barang (Horizontal Bar Chart) --}}
                <div class="col-lg-6 mb-4"> {{-- Menggunakan col-lg-6 untuk berdampingan di layar besar --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Tren Penjualan dan Pembelian</h6>
                            <div class="d-flex align-items-center">
                                <span id="currentWeekPeriodSales" class="text-muted me-2">{{ $chartPeriod }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="max-height: 60vh; min-height: 350px;"> {{-- Sesuaikan tinggi agar pas berdampingan --}}
                                <canvas id="salesPurchaseBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4"> {{-- Menggunakan col-lg-6 untuk berdampingan di layar besar --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Top 10 Barang Stok Terendah</h6>
                            <span class="text-muted">Item yang perlu segera di-restock</span>
                        </div>
                        <div class="card-body">
                            <div class="chart-bar-horizontal" style="max-height: 60vh; min-height: 350px;"> {{-- Sesuaikan tinggi agar pas berdampingan --}}
                                <canvas id="stockHorizontalBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Detail Notifikasi Stok (Collapsible) --}}
                @if($outOfStockNotifications->count() > 0 || $lowStockNotifications->count() > 0)
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-dark">
                                    <i class="fas fa-bell me-2"></i>Detail Notifikasi Stok Barang
                                </h6>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#stockNotificationDetails" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i> Lihat Detail
                                </button>
                            </div>
                        </div>
                        <div class="collapse" id="stockNotificationDetails">
                            <div class="card-body py-3">
                                <div class="row">
                                    @if($outOfStockNotifications->count() > 0)
                                    <div class="col-lg-6">
                                        <h6 class="text-danger mb-2">
                                            <i class="fas fa-exclamation-triangle"></i> Stok Habis ({{ $outOfStockNotifications->count() }} Item)
                                        </h6>
                                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead class="bg-danger text-white">
                                                    <tr>
                                                        <th>Nama Barang</th>
                                                        <th>Kategori</th>
                                                        <th>Lokasi Rak</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($outOfStockNotifications as $item)
                                                    <tr>
                                                        <td><strong>{{ $item->nama_barang }}</strong></td>
                                                        <td>{{ $item->kategori_barang }}</td>
                                                        <td>{{ $item->lokasi_rak_barang ?? 'Tidak diketahui' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif

                                    @if($lowStockNotifications->count() > 0)
                                    <div class="col-lg-6">
                                        <h6 class="text-warning mb-2">
                                            <i class="fas fa-exclamation-circle"></i> Stok Rendah ({{ $lowStockNotifications->count() }} Item)
                                        </h6>
                                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead class="bg-warning text-dark">
                                                    <tr>
                                                        <th>Nama Barang</th>
                                                        <th>Kategori</th>
                                                        <th>Sisa Stok</th>
                                                        <th>Lokasi Rak</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($lowStockNotifications as $item)
                                                    <tr class="{{ $item->jumlah_barang <= 5 ? 'table-danger' : '' }}">
                                                        <td><strong>{{ $item->nama_barang }}</strong></td>
                                                        <td>{{ $item->kategori_barang }}</td>
                                                        <td>
                                                            <span class="badge {{ $item->jumlah_barang <= 5 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                                {{ $item->jumlah_barang }} unit
                                                            </span>
                                                        </td>
                                                        <td>{{ $item->lokasi_rak_barang ?? 'Tidak diketahui' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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
    .card.bg-white {
        background-color: #fff !important;
    }
    .card.shadow {
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
    }
    .text-xs {
        font-size: .7rem;
    }
    .font-weight-bold {
        font-weight: 700!important;
    }
    .text-gray-800 {
        color: #5a5c69!important;
    }
    /* Neutral gray for icons */
    .text-gray-600 {
        color: #6c757d !important;
    }
    .fa-2x {
        font-size: 2em;
    }

    /* Chart area styling */
    .chart-area, .chart-bar-horizontal {
        position: relative;
        /* Removed fixed height, using max-height and min-height for responsiveness */
    }

    /* Notifikasi Stok Styling */
    .stock-notification {
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        border-radius: 0.5rem;
        border-left: 4px solid;
        font-size: 0.9rem;
    }
    
    .alert-danger.stock-notification {
        border-left-color: #dc3545;
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .alert-warning.stock-notification {
        border-left-color: #ffc107;
        background-color: #fff3cd;
        color: #856404;
    }

    .stock-notification .btn-close {
        padding: 0.25rem;
        font-size: 0.8rem;
    }

    .stock-notification i {
        font-size: 1rem;
    }

    /* Styling untuk tabel notifikasi */
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(0, 0, 0, 0.05);
    }

    /* Badge styling */
    .badge {
        font-size: 0.75em;
        padding: 0.25em 0.5em;
    }

    /* Enhanced alert styling */
    .alert {
        position: relative;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.5rem;
    }

    .alert h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
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

        // Data from Laravel Controller for Sales and Purchase Chart
        const salesPurchaseChartLabels = @json($chartLabels);
        const purchaseData = @json($purchaseTrendData);
        const salesData = @json($salesTrendData);

        // Data from Laravel Controller for Stock Condition Chart
        const stockItemLabels = @json($stockItemLabels);
        const stockItemData = @json($stockItemData);

        // Data untuk notifikasi stok
        const outOfStockCount = {{ $outOfStockNotifications->count() }};
        const lowStockCount = {{ $lowStockNotifications->count() }};

        // Show notification badges if there are stock issues
        if (outOfStockCount > 0 || lowStockCount > 0) {
            updatePageTitle();
        }

        // Function to update page title with notification count
        function updatePageTitle() {
            const totalNotifications = outOfStockCount + lowStockCount;
            if (totalNotifications > 0) {
                document.title = `(${totalNotifications}) Dashboard Manajer - UD Keluarga Sehati`;
            }
        }

        // Auto-refresh notifikasi setiap 5 menit (opsional)
        setInterval(function() {
            // Hanya reload jika ada notifikasi stok untuk menghindari reload yang tidak perlu
            if (outOfStockCount > 0 || lowStockCount > 0) {
                console.log('Checking for stock updates...');
                // AJAX call untuk update notifikasi secara real-time
                updateStockNotifications();
            }
        }, 300000); // 5 menit

        // Function to update stock notifications via AJAX
        function updateStockNotifications() {
            fetch('/api/dashboard/notifications', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || '')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const notifications = data.data;
                    const newOutOfStockCount = notifications.out_of_stock.count;
                    const newLowStockCount = notifications.low_stock.count;
                    
                    // Update badge counts if changed
                    if (newOutOfStockCount !== outOfStockCount || newLowStockCount !== lowStockCount) {
                        console.log('Stock notifications updated - reloading page...');
                        location.reload(); // Reload untuk update UI
                    }
                }
            })
            .catch(error => {
                console.log('Error updating stock notifications:', error);
            });
        }

        // Handle collapse toggle for stock notification details
        const stockNotificationToggle = document.querySelector('[data-bs-target="#stockNotificationDetails"]');
        const stockNotificationCollapse = document.getElementById('stockNotificationDetails');
        
        if (stockNotificationToggle && stockNotificationCollapse) {
            stockNotificationCollapse.addEventListener('show.bs.collapse', function () {
                const icon = stockNotificationToggle.querySelector('i');
                icon.className = 'fas fa-chevron-up';
                stockNotificationToggle.innerHTML = '<i class="fas fa-chevron-up"></i> Sembunyikan Detail';
            });

            stockNotificationCollapse.addEventListener('hide.bs.collapse', function () {
                const icon = stockNotificationToggle.querySelector('i');
                icon.className = 'fas fa-chevron-down';
                stockNotificationToggle.innerHTML = '<i class="fas fa-chevron-down"></i> Lihat Detail';
            });
        }
        const itemColors = [
            '#e74c3c', '#e67e22', '#f1c40f', '#f39c12', '#3498db',
            '#9b59b6', '#1abc9c', '#2ecc71', '#34495e', '#795548'
        ];

        // Sales and Purchase Bar Chart
        const salesPurchaseCtx = document.getElementById('salesPurchaseBarChart');
        if (salesPurchaseCtx) {
            new Chart(salesPurchaseCtx, {
                type: 'bar',
                data: {
                    labels: salesPurchaseChartLabels,
                    datasets: [{
                        label: 'Pembelian',
                        data: purchaseData,
                        backgroundColor: '#3498db',
                    }, {
                        label: 'Penjualan',
                        data: salesData,
                        backgroundColor: '#27ae60',
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

        // Stock Condition Horizontal Bar Chart
        const stockHorizontalCtx = document.getElementById('stockHorizontalBarChart');
        if (stockHorizontalCtx) {
            const itemColors = [
                '#e74c3c', '#e67e22', '#f1c40f', '#f39c12', '#3498db',
                '#9b59b6', '#1abc9c', '#2ecc71', '#34495e', '#795548'
            ];
            new Chart(stockHorizontalCtx, {
                type: 'bar',
                data: {
                    labels: stockItemLabels,
                    datasets: [{
                        label: 'Jumlah Stok',
                        data: stockItemData,
                        backgroundColor: itemColors,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah Unit (Pcs)' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection
