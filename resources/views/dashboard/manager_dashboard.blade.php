@extends('layouts.app')

@section('content')
<div class="container-fluid"> {{-- Menggunakan container-fluid untuk lebar penuh --}}
    <div class="row">
        {{-- Sidebar (seperti di gambar) --}}
        <div class="col-md-2 d-none d-md-block sidebar"> {{-- Sembunyikan di mobile, tampil di desktop --}}
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
                        <a class="nav-link" href="{{ route('report.stock') }}"> {{-- Link ke Laporan Stok Barang --}}
                            <i class="fas fa-boxes"></i>Laporan Stok Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('order.items') }}"> {{-- Pastikan href ini benar --}}
                            <i class="fas fa-shopping-cart"></i>Pemesanan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users-cog"></i>Akun Pegawai
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

                        {{-- Form logout tersembunyi --}}
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf {{-- Pastikan ini ada untuk perlindungan CSRF --}}
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

            {{-- Konten Dashboard Utama --}}
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">Selamat Datang di Dashboard Manajer!</h2>
                    <p class="lead">Gunakan navigasi di samping untuk mengakses fitur-fitur manajemen.</p>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>Untuk melihat laporan stok dan tren barang, silakan klik "Laporan Stok Barang" di sidebar. Untuk pemesanan barang, klik "Pemesanan Barang".</div>
                    </div>
                </div>
                {{-- Tambahkan widget atau ringkasan lain untuk dashboard utama di sini --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Ringkasan Stok Barang</h5>
                            <p class="card-text">Total barang masuk: {{ $incomingItems->sum('jumlah_barang') }} unit</p>
                            <p class="card-text">Total barang keluar: {{ $outgoingItems->sum('jumlah_barang') }} unit</p>
                            <a href="{{ route('report.stock') }}" class="btn btn-primary btn-sm">Lihat Detail Laporan</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users"></i> Manajemen Pengguna</h5>
                            <p class="card-text">Kelola akun pegawai dan hak akses mereka.</p>
                            <a href="#" class="btn btn-secondary btn-sm">Kelola Akun</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Tambahan CSS khusus untuk Manager Dashboard */
    .main-content {
        padding-left: 1.5rem; /* Tambahkan padding agar tidak terlalu mepet sidebar */
        padding-right: 1.5rem; /* Tambahkan padding kanan juga */
        margin-left: 16.66666667%; /* Sesuaikan margin-left untuk mengimbangi col-md-2 */
        width: 83.33333333%; /* Sesuaikan lebar untuk col-md-10 */
    }

    @media (max-width: 767.98px) {
        .main-content {
            margin-left: 0; /* Hapus margin di mobile */
            width: 100%; /* Lebar penuh di mobile */
            padding-left: 1rem; /* Sesuaikan padding mobile */
            padding-right: 1rem; /* Sesuaikan padding mobile */
        }
        .sidebar {
            position: relative; /* Sidebar menjadi relatif di mobile */
            min-height: auto; /* Tinggi otomatis di mobile */
            width: 100%; /* Lebar penuh di mobile */
            padding-bottom: 1rem;
        }
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
    .card-header .nav-link {
        font-weight: 600;
        color: var(--secondary-color);
        border: none;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .card-header .nav-link.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background-color: transparent; /* Override Bootstrap's active background */
    }
    .card-header .nav-link:hover {
        border-bottom-color: var(--primary-color);
    }
    .card-header-tabs {
        border-bottom: none;
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
    });
</script>
@endsection
