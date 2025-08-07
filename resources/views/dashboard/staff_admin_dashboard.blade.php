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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-2">Dashboard Admin Statistik</h2>
                            <p class="lead mb-0">Monitor data transaksi dan pergerakan barang berdasarkan tanggal</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <label for="dashboardDatePicker" class="form-label me-2 mb-0">
                                    <i class="fas fa-calendar-alt"></i> Pilih Tanggal:
                                </label>
                                <input type="date" id="dashboardDatePicker" class="form-control" 
                                       value="{{ date('Y-m-d') }}" style="min-width: 150px;">
                            </div>
                            <button type="button" id="refreshDashboard" class="btn btn-primary btn-sm">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            Data statistik menampilkan informasi untuk tanggal: 
                            <strong id="selectedDateDisplay">{{ date('d M Y') }}</strong>
                            <span class="badge bg-primary ms-2" id="loadingIndicator" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Memuat...
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Card: Jumlah Total Barang Masuk Hari Ini --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Barang Masuk</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalIncomingToday">{{ $incomingToday }}</div>
                                    <div class="text-xs text-gray-300">Unit</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
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
                                        Barang Keluar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalOutgoingToday">{{ $outgoingToday }}</div>
                                    <div class="text-xs text-gray-300">Unit</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Total Stok Keseluruhan --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Stok</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalStock">{{ $totalStock }}</div>
                                    <div class="text-xs text-gray-300">Unit</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Barang Stok Rendah --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Stok Rendah</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="lowStockItems">{{ $lowStockItems }}</div>
                                    <div class="text-xs text-gray-300">Barang</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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

    /* Enhanced Card Styles - keeping original colors */
    .card {
        transition: all 0.3s ease;
        transform: scale(1);
        border: 1px solid #e3e6f0;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    /* Animation for cards */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Datepicker styling */
    .date-picker-container {
        animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
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

        // Dashboard Date Picker and Real-time Updates
        const datePicker = document.getElementById('dashboardDatePicker');
        const refreshBtn = document.getElementById('refreshDashboard');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const selectedDateDisplay = document.getElementById('selectedDateDisplay');

        // Update date display
        function updateDateDisplay(date) {
            const options = { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                locale: 'id-ID'
            };
            selectedDateDisplay.textContent = new Date(date).toLocaleDateString('id-ID', options);
        }

        // Show loading state
        function showLoading() {
            loadingIndicator.style.display = 'inline-block';
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }

        // Hide loading state
        function hideLoading() {
            loadingIndicator.style.display = 'none';
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }

        // Fetch dashboard data for selected date
        async function fetchDashboardData(selectedDate) {
            showLoading();
            
            try {
                const url = `/admin/dashboard/data?date=${encodeURIComponent(selectedDate)}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    updateDashboardCards(data.data);
                    updateDateDisplay(selectedDate);
                    
                    // Show success message
                    showAlert('success', `Data berhasil diperbarui untuk tanggal ${new Date(selectedDate).toLocaleDateString('id-ID')}`);
                } else {
                    throw new Error(data.message || 'Gagal mengambil data dashboard');
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                showAlert('error', 'Gagal mengambil data dashboard: ' + error.message);
            } finally {
                hideLoading();
            }
        }

        // Update dashboard cards with new data
        function updateDashboardCards(data) {
            // Update card values with animation
            updateCardValue('totalIncomingToday', data.total_incoming_today || 0);
            updateCardValue('totalOutgoingToday', data.total_outgoing_today || 0);
            updateCardValue('totalStock', data.total_stock || 0);
            updateCardValue('lowStockItems', data.low_stock_items || 0);
        }

        // Animate card value update
        function updateCardValue(elementId, newValue) {
            const element = document.getElementById(elementId);
            if (element) {
                // Add update animation
                element.style.transform = 'scale(1.1)';
                element.style.transition = 'transform 0.3s ease';
                
                setTimeout(() => {
                    element.textContent = newValue;
                    element.style.transform = 'scale(1)';
                }, 150);
            }
        }

        // Show alert message
        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.dashboard-alert');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show dashboard-alert`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            alertDiv.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Event listeners
        datePicker.addEventListener('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                fetchDashboardData(selectedDate);
            }
        });

        refreshBtn.addEventListener('click', function() {
            const selectedDate = datePicker.value || new Date().toISOString().split('T')[0];
            fetchDashboardData(selectedDate);
        });

        // Initialize with current date
        updateDateDisplay(datePicker.value);

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
