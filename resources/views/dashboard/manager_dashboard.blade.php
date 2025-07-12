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
        <div class="col-md-10 offset-md-2 main-content"> {{-- Sesuaikan offset karena sidebar --}}
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> {{ Auth::user()->role === 'manager' ? 'Manajer' : 'Admin' }}</span>
                <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

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

                            {{-- Tabel Stok --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori Barang</th>
                                            <th>Tanggal Masuk Barang</th>
                                            <th>Jumlah Barang</th>
                                            <th>Status Barang</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Laptop ASUS ROG</td>
                                            <td>Elektronik</td>
                                            <td>01 Jan 2025</td>
                                            <td>10</td>
                                            <td><span class="badge bg-success">Banyak</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-search-plus"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Meja Gaming</td>
                                            <td>Furniture</td>
                                            <td>02 Jan 2025</td>
                                            <td>3</td>
                                            <td><span class="badge bg-warning">Sedikit</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-search-plus"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Kursi Ergonomis</td>
                                            <td>Furniture</td>
                                            <td>03 Jan 2025</td>
                                            <td>0</td>
                                            <td><span class="badge bg-danger">Habis</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-search-plus"></i></button>
                                            </td>
                                        </tr>
                                        {{-- Tambahkan baris lain sesuai kebutuhan --}}
                                    </tbody>
                                </table>
                            </div>
                            {{-- Paginasi (placeholder) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga 3 dari 100 tabel</small>
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
                            <p>Konten untuk Barang Keluar akan tampil di sini.</p>
                            {{-- Anda bisa duplikasi struktur tabel dari incoming-stock di sini --}}
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
@endsection