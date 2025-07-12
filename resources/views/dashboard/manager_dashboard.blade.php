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
                        <a class="nav-link" href="#">
                            <i class="fas fa-boxes"></i>Laporan Stok Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
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
                    <i class="fas fa-bars"></i>
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

            {{-- Card Stok Barang Masuk --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs" id="stockTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming-stock" type="button" role="tab" aria-controls="incoming-stock" aria-selected="true">
                                Barang Masuk
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing-stock" type="button" role="tab" aria-controls="outgoing-stock" aria-selected="false">
                                Barang Keluar
                            </button>
                        </li>
                    </ul>
                    <h5 class="mb-0 ms-auto">Stok Barang Masuk</h5>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="stockTabsContent">
                        <div class="tab-pane fade show active" id="incoming-stock" role="tabpanel" aria-labelledby="incoming-tab">
                            {{-- Filter dan Pencarian --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Semua Item</option>
                                        <option value="1">Item 1</option>
                                        <option value="2">Item 2</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Pilih Kategori Barang</option>
                                        <option value="1">Kategori A</option>
                                        <option value="2">Kategori B</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Mencari">
                                        <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Stok Barang Masuk --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori Barang</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Jumlah</th>
                                            <th>Lokasi Rak</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($incomingItems as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->kategori_barang }}</td>
                                                <td>{{ $item->tanggal_masuk_barang->format('d M Y') }}</td>
                                                <td>{{ $item->jumlah_barang }}</td>
                                                <td>{{ $item->lokasi_rak_barang }}</td>
                                                <td>
                                                    @if ($item->status_barang == 'Banyak')
                                                        <span class="badge bg-success">{{ $item->status_barang }}</span>
                                                    @elseif ($item->status_barang == 'Sedikit')
                                                        <span class="badge bg-warning">{{ $item->status_barang }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ $item->status_barang }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                    <button class="btn btn-sm btn-primary"><i class="fas fa-search-plus"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data barang masuk.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- Paginasi (placeholder) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga {{ $incomingItems->count() }} dari {{ $incomingItems->count() }} tabel</small>
                                <div>
                                    <button class="btn btn-sm btn-light">Sebelumnya</button>
                                    <button class="btn btn-sm btn-light">Berikutnya</button>
                                </div>
                            </div>
                            {{-- Tombol Laporan --}}
                            <div class="mt-3 text-start">
                                <button class="btn btn-danger me-2"><i class="fas fa-file-pdf"></i> Cetak PDF</button>
                                <button class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Cetak Excel</button>
                                <button class="btn btn-info"><i class="fas fa-warehouse"></i> Lihat Kondisi Distribusi Barang Gudang</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="outgoing-stock" role="tabpanel" aria-labelledby="outgoing-tab">
                            {{-- Filter dan Pencarian untuk Barang Keluar --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Semua Item</option>
                                        <option value="1">Item 1</option>
                                        <option value="2">Item 2</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Pilih Kategori Barang</option>
                                        <option value="1">Kategori A</option>
                                        <option value="2">Kategori B</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Mencari">
                                        <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Stok Barang Keluar --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori Barang</th>
                                            <th>Tanggal Keluar</th>
                                            <th>Jumlah</th>
                                            <th>Tujuan Distribusi</th>
                                            <th>Lokasi Rak</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($outgoingItems as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->kategori_barang }}</td>
                                                <td>{{ $item->tanggal_keluar_barang->format('d M Y') }}</td>
                                                <td>{{ $item->jumlah_barang }}</td>
                                                <td>{{ $item->tujuan_distribusi }}</td>
                                                <td>{{ $item->lokasi_rak_barang }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                    <button class="btn btn-sm btn-primary"><i class="fas fa-search-plus"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data barang keluar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- Paginasi (placeholder) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga {{ $outgoingItems->count() }} dari {{ $outgoingItems->count() }} tabel</small>
                                <div>
                                    <button class="btn btn-sm btn-light">Sebelumnya</button>
                                    <button class="btn btn-sm btn-light">Berikutnya</button>
                                </div>
                            </div>
                            {{-- Tombol Laporan --}}
                            <div class="mt-3 text-start">
                                <button class="btn btn-danger me-2"><i class="fas fa-file-pdf"></i> Cetak PDF</button>
                                <button class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Cetak Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Tren Pembelian Barang (Grafik) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tren Pembelian Barang</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Periode : 1-7 Januari 2025</span>
                        <button class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-calendar-alt"></i> Detail Kalender</button>
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i> Minggu Berikutnya</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center" style="height: 300px; background-color: #f8f9fa; border-radius: 8px;">
                        {{-- Placeholder untuk grafik batang --}}
                        {{-- Anda akan mengintegrasikan Chart.js atau library grafik lain di sini --}}
                        <img src="{{ asset('images/chart_placeholder.png') }}" alt="Chart Placeholder" style="max-width: 90%; max-height: 90%; opacity: 0.7;">
                    </div>
                    <div class="mt-3 text-end">
                        <small class="text-muted">
                            <i class="fas fa-square" style="color: #3498db;"></i> Produk
                            <i class="fas fa-square ms-3" style="color: #27ae60;"></i> Jumlah Terbeli
                            <i class="fas fa-square ms-3" style="color: #f39c12;"></i> Hari
                        </small>
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
