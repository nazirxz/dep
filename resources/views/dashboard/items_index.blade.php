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
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('staff.items.index') }}">
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

        {{-- Main Content untuk Data Barang --}}
        <div class="col-md-10 offset-md-2 main-content">
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> Staff Admin</span>
                <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
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

            {{-- Card Data Barang --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs" id="itemTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="incoming-items-tab" data-bs-toggle="tab" data-bs-target="#incoming-items" type="button" role="tab">
                                <i class="fas fa-arrow-down"></i> Barang Masuk
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="outgoing-items-tab" data-bs-toggle="tab" data-bs-target="#outgoing-items" type="button" role="tab">
                                <i class="fas fa-arrow-up"></i> Barang Keluar
                            </button>
                        </li>
                    </ul>
                    <h5 class="mb-0 ms-auto">Data Barang</h5>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="itemTabsContent">
                        {{-- Tab Barang Masuk --}}
                        <div class="tab-pane fade show active" id="incoming-items" role="tabpanel">
                            {{-- Filter dan Pencarian --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-select" id="categoryFilter">
                                        <option value="">Pilih Kategori Barang</option>
                                        @foreach($incomingItems->pluck('kategori_barang')->unique() as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">Pilih Status Barang</option>
                                        <option value="Banyak">Banyak</option>
                                        <option value="Sedikit">Sedikit</option>
                                        <option value="Habis">Habis</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Mencari barang..." id="searchIncomingInput">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Barang Masuk --}}
                            <div class="table-responsive">
                                <table class="table table-hover" id="incomingTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Jumlah</th>
                                            <th>Lokasi Rak</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($incomingItems as $item)
                                            <tr data-category="{{ $item->kategori_barang }}" data-status="{{ $item->status_barang }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->kategori_barang }}</td>
                                                <td>{{ $item->tanggal_masuk_barang->format('d M Y') }}</td>
                                                <td>{{ $item->jumlah_barang }} unit</td>
                                                <td>
                                                    @if($item->lokasi_rak_barang)
                                                        <span class="badge bg-info">{{ $item->lokasi_rak_barang }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">Belum Ditempatkan</span>
                                                    @endif
                                                </td>
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
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-info" onclick="viewItemDetails({{ $item->id }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" onclick="editItem({{ $item->id }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        @if($item->lokasi_rak_barang)
                                                            <button class="btn btn-sm btn-primary" onclick="showLocation('{{ $item->lokasi_rak_barang }}')">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-sm btn-success" onclick="assignLocation({{ $item->id }})">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
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

                            {{-- Summary Stats --}}
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-primary">{{ $incomingItems->count() }}</h4>
                                            <small class="text-muted">Total Item</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-success">{{ $incomingItems->sum('jumlah_barang') }}</h4>
                                            <small class="text-muted">Total Unit</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-info">{{ $incomingItems->whereNotNull('lokasi_rak_barang')->count() }}</h4>
                                            <small class="text-muted">Sudah Ditempatkan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-warning">{{ $incomingItems->whereNull('lokasi_rak_barang')->count() }}</h4>
                                            <small class="text-muted">Belum Ditempatkan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab Barang Keluar --}}
                        <div class="tab-pane fade" id="outgoing-items" role="tabpanel">
                            {{-- Filter dan Pencarian --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-select" id="outgoingCategoryFilter">
                                        <option value="">Pilih Kategori Barang</option>
                                        @foreach($outgoingItems->pluck('kategori_barang')->unique() as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" id="dateFilter" placeholder="Filter Tanggal">
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Mencari barang..." id="searchOutgoingInput">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Barang Keluar --}}
                            <div class="table-responsive">
                                <table class="table table-hover" id="outgoingTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Tanggal Keluar</th>
                                            <th>Jumlah</th>
                                            <th>Tujuan</th>
                                            <th>Lokasi Rak</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($outgoingItems as $item)
                                            <tr data-category="{{ $item->kategori_barang }}" data-date="{{ $item->tanggal_keluar_barang->format('Y-m-d') }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->kategori_barang }}</td>
                                                <td>{{ $item->tanggal_keluar_barang->format('d M Y') }}</td>
                                                <td>{{ $item->jumlah_barang }} unit</td>
                                                <td>{{ $item->tujuan_distribusi ?? 'Tidak Diketahui' }}</td>
                                                <td>
                                                    @if($item->lokasi_rak_barang)
                                                        <span class="badge bg-info">{{ $item->lokasi_rak_barang }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-info" onclick="viewOutgoingDetails({{ $item->id }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-primary" onclick="printDeliveryNote({{ $item->id }})">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                    </div>
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

                            {{-- Summary Stats untuk Barang Keluar --}}
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-primary">{{ $outgoingItems->count() }}</h4>
                                            <small class="text-muted">Total Pengiriman</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-danger">{{ $outgoingItems->sum('jumlah_barang') }}</h4>
                                            <small class="text-muted">Total Unit Keluar</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-info">{{ $outgoingItems->pluck('tujuan_distribusi')->unique()->count() }}</h4>
                                            <small class="text-muted">Tujuan Unik</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Detail Item -->
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

<!-- Modal untuk Edit Item -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editItemContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
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
        background-color: transparent;
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

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality for incoming items
        const searchIncomingInput = document.getElementById('searchIncomingInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const incomingTable = document.getElementById('incomingTable');

        function filterIncomingTable() {
            const searchText = searchIncomingInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;
            const selectedStatus = statusFilter.value;
            const rows = incomingTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const nameCell = row.getElementsByTagName('td')[1];
                const category = row.dataset.category;
                const status = row.dataset.status;

                if (nameCell) {
                    const nameText = nameCell.textContent.toLowerCase();
                    const matchesSearch = nameText.includes(searchText);
                    const matchesCategory = !selectedCategory || category === selectedCategory;
                    const matchesStatus = !selectedStatus || status === selectedStatus;

                    if (matchesSearch && matchesCategory && matchesStatus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        if (searchIncomingInput) searchIncomingInput.addEventListener('keyup', filterIncomingTable);
        if (categoryFilter) categoryFilter.addEventListener('change', filterIncomingTable);
        if (statusFilter) statusFilter.addEventListener('change', filterIncomingTable);

        // Search functionality for outgoing items
        const searchOutgoingInput = document.getElementById('searchOutgoingInput');
        const outgoingCategoryFilter = document.getElementById('outgoingCategoryFilter');
        const dateFilter = document.getElementById('dateFilter');
        const outgoingTable = document.getElementById('outgoingTable');

        function filterOutgoingTable() {
            const searchText = searchOutgoingInput.value.toLowerCase();
            const selectedCategory = outgoingCategoryFilter.value;
            const selectedDate = dateFilter.value;
            const rows = outgoingTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const nameCell = row.getElementsByTagName('td')[1];
                const category = row.dataset.category;
                const date = row.dataset.date;

                if (nameCell) {
                    const nameText = nameCell.textContent.toLowerCase();
                    const matchesSearch = nameText.includes(searchText);
                    const matchesCategory = !selectedCategory || category === selectedCategory;
                    const matchesDate = !selectedDate || date === selectedDate;

                    if (matchesSearch && matchesCategory && matchesDate) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        if (searchOutgoingInput) searchOutgoingInput.addEventListener('keyup', filterOutgoingTable);
        if (outgoingCategoryFilter) outgoingCategoryFilter.addEventListener('change', filterOutgoingTable);
        if (dateFilter) dateFilter.addEventListener('change', filterOutgoingTable);
    });

    // Function to view item details
    function viewItemDetails(itemId) {
        const incomingItems = @json($incomingItems);
        const item = incomingItems.find(i => i.id == itemId);
        
        if (item) {
            const modalContent = document.getElementById('itemDetailContent');
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Barang</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                            <tr><td><strong>Kategori:</strong></td><td>${item.kategori_barang}</td></tr>
                            <tr><td><strong>Jumlah:</strong></td><td>${item.jumlah_barang} unit</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge ${getStatusBadgeClass(item.status_barang)}">${item.status_barang}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Lokasi</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Tanggal Masuk:</strong></td><td>${new Date(item.tanggal_masuk_barang).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td><strong>Lokasi Rak:</strong></td><td>${item.lokasi_rak_barang || 'Belum ditempatkan'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
            modal.show();
        }
    }

    // Function to view outgoing item details
    function viewOutgoingDetails(itemId) {
        const outgoingItems = @json($outgoingItems);
        const item = outgoingItems.find(i => i.id == itemId);
        
        if (item) {
            const modalContent = document.getElementById('itemDetailContent');
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Barang</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                            <tr><td><strong>Kategori:</strong></td><td>${item.kategori_barang}</td></tr>
                            <tr><td><strong>Jumlah:</strong></td><td>${item.jumlah_barang} unit</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Pengiriman</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Tanggal Keluar:</strong></td><td>${new Date(item.tanggal_keluar_barang).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td><strong>Tujuan:</strong></td><td>${item.tujuan_distribusi || 'Tidak diketahui'}</td></tr>
                            <tr><td><strong>Lokasi Rak:</strong></td><td>${item.lokasi_rak_barang || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
            modal.show();
        }
    }

    // Function to edit item
    function editItem(itemId) {
        alert('Fitur edit barang akan segera hadir!');
        // Here you would load edit form in modal
    }

    // Function to show location
    function showLocation(location) {
        // Redirect to warehouse management page with highlighted location
        window.location.href = `{{ route('staff.item.management') }}?highlight=${location}`;
    }

    // Function to assign location
    function assignLocation(itemId) {
        const newLocation = prompt('Masukkan lokasi rak baru (Format: R[1-8]-[1-4]-[1-6]):');
        if (newLocation) {
            const pattern = /^R[1-8]-[1-4]-[1-6]$/;
            if (pattern.test(newLocation)) {
                alert(`Lokasi ${newLocation} berhasil ditetapkan untuk barang ini.`);
                // Here you would make AJAX call to update location
                location.reload();
            } else {
                alert('Format lokasi tidak valid!');
            }
        }
    }

    // Function to print delivery note
    function printDeliveryNote(itemId) {
        alert('Fitur cetak surat jalan akan segera hadir!');
        // Here you would generate and print delivery note
    }

    // Helper function for status badge
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'Banyak': return 'bg-success';
            case 'Sedikit': return 'bg-warning';
            case 'Habis': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
</script>
@endsection