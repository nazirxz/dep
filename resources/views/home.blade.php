@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Welcome Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="mb-0">Dashboard Usaha Distributor Keluarga Sehati</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="dashboard-logo">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="stat-icon text-primary mb-2">
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                            <h5 class="card-title">Total Produk</h5>
                            <h3 class="text-primary">125</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="stat-icon text-success mb-2">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                            <h5 class="card-title">Pesanan Hari Ini</h5>
                            <h3 class="text-success">23</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="stat-icon text-warning mb-2">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h5 class="card-title">Total Pelanggan</h5>
                            <h3 class="text-warning">89</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="stat-icon text-info mb-2">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h5 class="card-title">Penjualan Bulan Ini</h5>
                            <h3 class="text-info">Rp 45,2M</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Pesanan Terbaru
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Pesanan</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-2025-001</td>
                                            <td>Budi Santoso</td>
                                            <td>Rp 1,250,000</td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-2025-002</td>
                                            <td>Siti Rahayu</td>
                                            <td>Rp 890,000</td>
                                            <td><span class="badge bg-success">Selesai</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-2025-003</td>
                                            <td>Ahmad Wijaya</td>
                                            <td>Rp 2,100,000</td>
                                            <td><span class="badge bg-info">Proses</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>Aksi Cepat
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Produk
                                </button>
                                <button class="btn btn-success">
                                    <i class="fas fa-user-plus me-2"></i>Tambah Pelanggan
                                </button>
                                <button class="btn btn-info">
                                    <i class="fas fa-chart-bar me-2"></i>Lihat Laporan
                                </button>
                                <button class="btn btn-warning">
                                    <i class="fas fa-cog me-2"></i>Pengaturan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informasi Sistem
                            </h5>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <div class="mb-2">
                                    <strong>Login terakhir:</strong><br>
                                    {{ now()->format('d M Y, H:i') }} WIB
                                </div>
                                <div class="mb-2">
                                    <strong>Versi Aplikasi:</strong><br>
                                    v1.0.0
                                </div>
                                <div>
                                    <strong>Status Server:</strong><br>
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.3);
    }
    
    .card {
        border-radius: 10px;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    
    .stat-icon {
        background: rgba(0,123,255,0.1);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .btn {
        border-radius: 6px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
</style>
@endsection