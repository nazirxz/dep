@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar (seperti di gambar) --}}
        <div class="col-md-2 d-none d-md-block sidebar">
            <div class="position-sticky">
                <div class="d-flex align-items-center mb-4 mt-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo KS" class="img-fluid rounded-circle me-2" style="width: 40px; height: 40px;">
                    <h5 class="mb-0 text-white">UD KELUARGA SEHATI</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}"> {{-- Link ke Dashboard Utama --}}
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('report.stock') }}"> {{-- Link aktif ke Laporan Stok Barang --}}
                            <i class="fas fa-boxes"></i>Laporan Stok Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('order.items') }}"> {{-- Link ke Pemesanan Barang --}}
                            <i class="fas fa-shopping-cart"></i>Pemesanan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employee.accounts') }}"> {{-- Link ke Akun Pegawai --}}
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

        {{-- Main Content untuk Laporan Stok Barang --}}
        <div class="col-md-10 offset-md-2 main-content">
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
                                        {{-- Anda bisa mengisi opsi ini secara dinamis dari database jika diperlukan --}}
                                        <option value="1">Item 1</option>
                                        <option value="2">Item 2</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Pilih Kategori Barang</option>
                                        @if(isset($incomingItems))
                                            @foreach($incomingItems->pluck('kategori_barang')->unique()->filter() as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
                                            @endforeach
                                        @endif
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
                                            <th>Nama Pengecer</th>
                                            <th>Metode Bayar</th>
                                            <th>Pembayaran Transaksi</th>
                                            <th>Nota Transaksi</th>
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
                                                <td>{{ $item->lokasi_rak_barang ?? '-' }}</td>
                                                <td>{{ $item->nama_pengecer ?? '-' }}</td>
                                                <td>{{ $item->metode_bayar ?? '-' }}</td>
                                                <td>Rp{{ number_format($item->pembayaran_transaksi, 2, ',', '.') }}</td>
                                                <td>{{ $item->nota_transaksi ?? '-' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info me-1" onclick="viewItemDetails({{ $item->id }})"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                    {{-- Tombol lokasi rak (jika perlu ditampilkan di laporan) --}}
                                                    @if($item->lokasi_rak_barang)
                                                        <button class="btn btn-sm btn-primary" onclick="showLocation('{{ $item->lokasi_rak_barang }}')"><i class="fas fa-search-plus"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center">Tidak ada data barang masuk.</td>
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
                                <button class="btn btn-info" onclick="window.open('{{ route("staff.warehouse_monitor") }}', '_blank')"><i class="fas fa-warehouse"></i> Lihat Kondisi Distribusi Barang Gudang</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="outgoing-stock" role="tabpanel" aria-labelledby="outgoing-tab">
                            {{-- Filter dan Pencarian untuk Barang Keluar --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Semua Item</option>
                                        {{-- Anda bisa mengisi opsi ini secara dinamis dari database jika diperlukan --}}
                                        <option value="1">Item 1</option>
                                        <option value="2">Item 2</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select">
                                        <option selected>Pilih Kategori Barang</option>
                                        @if(isset($outgoingItems))
                                            @foreach($outgoingItems->pluck('kategori_barang')->unique()->filter() as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
                                            @endforeach
                                        @endif
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
                                            <th>Nama Pengecer</th>
                                            <th>Metode Bayar</th>
                                            <th>Pembayaran Transaksi</th>
                                            <th>Nota Transaksi</th>
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
                                                <td>{{ $item->tujuan_distribusi ?? 'Tidak Diketahui' }}</td>
                                                <td>{{ $item->lokasi_rak_barang ?? '-' }}</td>
                                                <td>{{ $item->nama_pengecer ?? '-' }}</td>
                                                <td>{{ $item->metode_bayar ?? '-' }}</td>
                                                <td>Rp{{ number_format($item->pembayaran_transaksi, 2, ',', '.') }}</td>
                                                <td>{{ $item->nota_transaksi ?? '-' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info me-1" onclick="viewOutgoingDetails({{ $item->id }})"><i class="fas fa-eye"></i> Lihat Detail</button>
                                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center">Tidak ada data barang keluar.</td>
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
                <div class="card-header bg-white d-flex justify-content-between align-items-center" id="chartCardHeader">
                    <h5 class="mb-0">Tren Pembelian & Penjualan Barang</h5> {{-- Judul awal --}}
                    <div class="d-flex align-items-center">
                        <span class="me-2">Periode : {{ $chartPeriod }}</span> {{-- Tampilkan periode dari controller --}}
                        <button class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-calendar-alt"></i> Detail Kalender</button>
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i> Minggu Berikutnya</button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Canvas untuk Chart.js --}}
                    <div style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="mt-3 text-end">
                        <small class="text-muted">
                            <i class="fas fa-square" style="color: #3498db;"></i> Pembelian
                            <i class="fas fa-square ms-3" style="color: #27ae60;"></i> Penjualan
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal untuk Detail Item --}}
<div class="modal fade" id="itemDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="itemDetailContent">
                <!-- Content will be loaded here -->
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

        // Data dari Laravel Controller
        const chartLabels = @json($chartLabels);
        const purchaseTrendData = @json($purchaseTrendData);
        const salesTrendData = @json($salesTrendData);
        const chartPeriod = @json($chartPeriod);

        // Update periode di HTML
        document.querySelector('.card-header .me-2').textContent = 'Periode : ' + chartPeriod;

        let salesChart; // Deklarasikan variabel chart di scope yang lebih luas

        // Fungsi untuk merender/update grafik
        function renderChart() {
            // Hapus chart yang sudah ada jika ada
            if (salesChart) {
                salesChart.destroy();
            }

            const datasets = [];

            // Dataset untuk Pembelian (Incoming)
            datasets.push({
                label: 'Pembelian',
                data: purchaseTrendData,
                backgroundColor: '#3498db', // Biru untuk pembelian
                borderColor: '#3498db',
                borderWidth: 1,
            });

            // Dataset untuk Penjualan (Outgoing)
            datasets.push({
                label: 'Penjualan',
                data: salesTrendData,
                backgroundColor: '#27ae60', // Hijau untuk penjualan
                borderColor: '#27ae60',
                borderWidth: 1,
            });

            const chartTitleText = 'Tren Pembelian & Penjualan Barang'; // Judul gabungan
            document.querySelector('#chartCardHeader h5').textContent = chartTitleText;


            const chartData = {
                labels: chartLabels,
                datasets: datasets // Gunakan kedua dataset
            };

            const salesConfig = {
                type: 'bar', // Tipe grafik utama
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Unit' // Label sumbu Y yang lebih umum
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hari'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true, // Tampilkan legend karena ada 2 dataset
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return 'Hari: ' + context[0].label;
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + ' unit';
                                }
                            }
                        }
                    }
                }
            };

            const salesChartCtx = document.getElementById('salesChart');
            if (salesChartCtx) {
                salesChart = new Chart(salesChartCtx, salesConfig);
            }
        }

        // Render grafik awal saat halaman dimuat
        renderChart();


        // Helper function to format numbers as currency.
        // Duplikasi dari partials/items_content.blade.php agar berfungsi di sini juga.
        window.number_format = function(amount, decimals, decPoint, thousandsSep) {
            amount = (amount + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+amount) ? 0 : +amount,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousandsSep === 'undefined') ? '.' : thousandsSep,
                dec = (typeof decPoint === 'undefined') ? ',' : decPoint,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };

            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Fungsi untuk melihat detail item masuk
        window.viewItemDetails = function(itemId) {
            const incomingItems = @json($incomingItems);
            const item = incomingItems.find(i => i.id == itemId);
            
            if (item) {
                const modalContent = document.getElementById('itemDetailContent');
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-primary">Informasi Barang Masuk</h6>
                            <table class="table table-borderless">
                                <tr><td width="30%"><strong>ID Barang:</strong></td><td>#${item.id}</td></tr>
                                <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                                <tr><td><strong>Kategori:</strong></td><td>${item.kategori_barang}</td></tr>
                                <tr><td><strong>Jumlah:</strong></td><td>${item.jumlah_barang} unit</td></tr>
                                <tr><td><strong>Tanggal Masuk:</strong></td><td>${new Date(item.tanggal_masuk_barang).toLocaleDateString('id-ID')}</td></tr>
                                <tr><td><strong>Lokasi Rak:</strong></td><td>${item.lokasi_rak_barang || 'Belum ditempatkan'}</td></tr>
                                <tr><td><strong>Nama Pengecer:</strong></td><td>${item.nama_pengecer ?? '-'}</td></tr>
                                <tr><td><strong>Metode Bayar:</strong></td><td>${item.metode_bayar ?? '-'}</td></tr>
                                <tr><td><strong>Pembayaran Transaksi:</strong></td><td>Rp${window.number_format(item.pembayaran_transaksi, 2, ',', '.')}</td></tr>
                                <tr><td><strong>Nota Transaksi:</strong></td><td>${item.nota_transaksi ?? '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
                modal.show();
            }
        }

        // Fungsi untuk melihat detail item keluar
        window.viewOutgoingDetails = function(itemId) {
            const outgoingItems = @json($outgoingItems);
            const item = outgoingItems.find(i => i.id == itemId);
            
            if (item) {
                const modalContent = document.getElementById('itemDetailContent');
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-primary">Informasi Barang Keluar</h6>
                            <table class="table table-borderless">
                                <tr><td width="30%"><strong>ID Barang:</strong></td><td>#${item.id}</td></tr>
                                <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                                <tr><td><strong>Kategori:</strong></td><td>${item.kategori_barang}</td></tr>
                                <tr><td><strong>Jumlah:</strong></td><td>${item.jumlah_barang} unit</td></tr>
                                <tr><td><strong>Tanggal Keluar:</strong></td><td>${new Date(item.tanggal_keluar_barang).toLocaleDateString('id-ID')}</td></tr>
                                <tr><td><strong>Tujuan Distribusi:</strong></td><td>${item.tujuan_distribusi ?? 'Tidak Diketahui'}</td></tr>
                                <tr><td><strong>Lokasi Rak Asal:</strong></td><td>${item.lokasi_rak_barang ?? '-'}</td></tr>
                                <tr><td><strong>Nama Pengecer:</strong></td><td>${item.nama_pengecer ?? '-'}</td></tr>
                                <tr><td><strong>Metode Bayar:</strong></td><td>${item.metode_bayar ?? '-'}</td></tr>
                                <tr><td><strong>Pembayaran Transaksi:</strong></td><td>Rp${window.number_format(item.pembayaran_transaksi, 2, ',', '.')}</td></tr>
                                <tr><td><strong>Nota Transaksi:</strong></td><td>${item.nota_transaksi ?? '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
                modal.show();
            }
        }

        // Dummy function for showLocation (can be linked to actual warehouse monitor if available)
        window.showLocation = function(location) {
            alert('Lokasi Rak: ' + location + '. Fitur peta gudang akan segera hadir!');
            // You might want to redirect to the warehouse monitor page with this location as a parameter
            // window.location.href = `{{ route('staff.warehouse_monitor') }}?highlight=${location}`;
        }

    });
</script>
@endsection
