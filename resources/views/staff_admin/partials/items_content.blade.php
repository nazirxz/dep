{{-- items_content.blade.php - Partial content untuk pengelolaan barang staff admin --}}

{{-- Menampilkan pesan sesi (tetap dipertahankan untuk pesan dari controller) --}}
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="add-items-tab" data-bs-toggle="tab" data-bs-target="#add-items" type="button" role="tab">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </li>
        </ul>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="exportData('pdf')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-primary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="tab-content" id="itemTabsContent">
            {{-- Tab Barang Masuk --}}
            <div class="tab-pane fade show active" id="incoming-items" role="tabpanel">
                {{-- Filter dan Pencarian --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="categoryFilter">
                            <option value="">Pilih Kategori Barang</option>
                            @if(isset($incomingItems))
                                @foreach($incomingItems->pluck('kategori_barang')->unique()->filter() as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Pilih Status Barang</option>
                            <option value="Banyak">Banyak</option>
                            <option value="Sedikit">Sedikit</option>
                            <option value="Habis">Habis</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dateIncomingFilter" placeholder="Filter Tanggal">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Mencari barang..." id="searchIncomingInput">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Bulk --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="checkbox" id="selectAllIncoming" class="form-check-input">
                            <label for="selectAllIncoming" class="form-check-label me-3">Pilih Semua</label>
                            <button class="btn btn-sm btn-warning" onclick="bulkEditLocation()" disabled id="bulkEditBtn">
                                <i class="fas fa-edit"></i> Edit Lokasi Terpilih
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="bulkDelete()" disabled id="bulkDeleteBtn">
                                <i class="fas fa-trash"></i> Hapus Terpilih
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabel Barang Masuk --}}
                <div class="table-responsive">
                    <table class="table table-hover" id="incomingTable">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAllIncomingHeader">
                                </th>
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
                            @if(isset($incomingItems) && $incomingItems->count() > 0)
                                @foreach ($incomingItems as $item)
                                    <tr data-id="{{ $item->id }}"
                                        data-category="{{ $item->kategori_barang }}" 
                                        data-status="{{ $item->status_barang }}"
                                        data-date="{{ $item->tanggal_masuk_barang->format('Y-m-d') }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input item-checkbox" 
                                                   value="{{ $item->id }}" onchange="updateBulkActions()">
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="item-icon me-2">
                                                    <i class="fas fa-box text-primary"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $item->nama_barang }}</strong>
                                                    <small class="text-muted d-block">ID: #{{ $item->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->kategori_barang }}</span>
                                        </td>
                                        <td>{{ $item->tanggal_masuk_barang->format('d M Y') }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $item->jumlah_barang }}</span> unit
                                        </td>
                                        <td>
                                            @if($item->lokasi_rak_barang)
                                                <span class="badge bg-info">{{ $item->lokasi_rak_barang }}</span>
                                                <button class="btn btn-sm btn-outline-info ms-1" 
                                                        onclick="showRackLocation('{{ $item->lokasi_rak_barang }}')"
                                                        title="Lihat di Peta Gudang">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            @else
                                                <span class="badge bg-secondary">Belum Ditempatkan</span>
                                                <button class="btn btn-sm btn-outline-success ms-1" 
                                                        onclick="quickAssignLocation({{ $item->id }})"
                                                        title="Tetapkan Lokasi Cepat">
                                                    <i class="fas fa-plus"></i>
                                                </button>
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
                                                <button class="btn btn-sm btn-info" onclick="viewItemDetails({{ $item->id }})" 
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editIncomingItem({{ $item->id }})" 
                                                        title="Edit Item">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="moveToOutgoing({{ $item->id }})" 
                                                        title="Pindah ke Barang Keluar">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteIncomingItem({{ $item->id }})" 
                                                        title="Hapus Item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data barang masuk.</p>
                                        <button class="btn btn-primary" onclick="addNewIncomingItem()">
                                            <i class="fas fa-plus"></i> Tambah Barang Baru
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Menampilkan {{ isset($incomingItems) ? $incomingItems->count() : 0 }} item dari total {{ isset($incomingItems) ? $incomingItems->count() : 0 }} item
                    </small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" disabled>Sebelumnya</button>
                        <button class="btn btn-sm btn-outline-secondary" disabled>Berikutnya</button>
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-box-open fa-2x text-primary mb-2"></i>
                                <h4 class="text-primary">{{ isset($incomingItems) ? $incomingItems->count() : 0 }}</h4>
                                <small class="text-muted">Total Item</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-cubes fa-2x text-success mb-2"></i>
                                <h4 class="text-success">{{ isset($incomingItems) ? $incomingItems->sum('jumlah_barang') : 0 }}</h4>
                                <small class="text-muted">Total Unit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-warehouse fa-2x text-info mb-2"></i>
                                <h4 class="text-info">{{ isset($incomingItems) ? $incomingItems->whereNotNull('lokasi_rak_barang')->count() : 0 }}</h4>
                                <small class="text-muted">Sudah Ditempatkan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-question-circle fa-2x text-warning mb-2"></i>
                                <h4 class="text-warning">{{ isset($incomingItems) ? $incomingItems->whereNull('lokasi_rak_barang')->count() : 0 }}</h4>
                                <small class="text-muted">Belum Ditempatkan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Barang Keluar --}}
            <div class="tab-pane fade" id="outgoing-items" role="tabpanel">
                {{-- Filter dan Pencarian untuk Barang Keluar --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="outgoingCategoryFilter">
                            <option value="">Pilih Kategori Barang</option>
                            @if(isset($outgoingItems))
                                @foreach($outgoingItems->pluck('kategori_barang')->unique()->filter() as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="destinationFilter">
                            <option value="">Pilih Tujuan</option>
                            @if(isset($outgoingItems))
                                @foreach($outgoingItems->pluck('tujuan_distribusi')->unique()->filter() as $destination)
                                    <option value="{{ $destination }}">{{ $destination }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dateOutgoingFilter" placeholder="Filter Tanggal">
                    </div>
                    <div class="col-md-3">
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
                                <th>Lokasi Rak Asal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($outgoingItems) && $outgoingItems->count() > 0)
                                @foreach ($outgoingItems as $item)
                                    <tr data-id="{{ $item->id }}"
                                        data-category="{{ $item->kategori_barang }}" 
                                        data-date="{{ $item->tanggal_keluar_barang->format('Y-m-d') }}"
                                        data-destination="{{ $item->tujuan_distribusi }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="item-icon me-2">
                                                    <i class="fas fa-shipping-fast text-danger"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $item->nama_barang }}</strong>
                                                    <small class="text-muted d-block">ID: #{{ $item->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->kategori_barang }}</span>
                                        </td>
                                        <td>{{ $item->tanggal_keluar_barang->format('d M Y') }}</td>
                                        <td>
                                            <span class="fw-bold text-danger">{{ $item->jumlah_barang }}</span> unit
                                        </td>
                                        <td>{{ $item->tujuan_distribusi ?? 'Tidak Diketahui' }}</td>
                                        <td>
                                            @if($item->lokasi_rak_barang)
                                                <span class="badge bg-info">{{ $item->lokasi_rak_barang }}</span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Selesai</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" onclick="viewItemDetails({{ $item->id }}, 'outgoing')" 
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editOutgoingItem({{ $item->id }})" 
                                                        title="Edit Item Keluar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="printDeliveryNote({{ $item->id }})" 
                                                        title="Cetak Surat Jalan">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteOutgoingItem({{ $item->id }})" 
                                                        title="Hapus Item Keluar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data barang keluar.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Summary Stats untuk Barang Keluar --}}
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-shipping-fast fa-2x text-primary mb-2"></i>
                                <h4 class="text-primary">{{ isset($outgoingItems) ? $outgoingItems->count() : 0 }}</h4>
                                <small class="text-muted">Total Pengiriman</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-box-open fa-2x text-danger mb-2"></i>
                                <h4 class="text-danger">{{ isset($outgoingItems) ? $outgoingItems->sum('jumlah_barang') : 0 }}</h4>
                                <small class="text-muted">Total Unit Keluar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-map-marker-alt fa-2x text-info mb-2"></i>
                                <h4 class="text-info">{{ isset($outgoingItems) ? $outgoingItems->pluck('tujuan_distribusi')->unique()->count() : 0 }}</h4>
                                <small class="text-muted">Tujuan Unik</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Tambah Barang --}}
            <div class="tab-pane fade" id="add-items" role="tabpanel">
                <div class="row">
                    {{-- Form Tambah Barang Masuk --}}
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-plus"></i> Tambah Barang Masuk</h6>
                            </div>
                            <div class="card-body">
                                <form id="addIncomingForm" onsubmit="handleItemCrudSubmit(event, 'incoming', 'add')">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="incoming_nama_barang" class="form-label">Nama Barang *</label>
                                        <input type="text" class="form-control" id="incoming_nama_barang" 
                                               name="nama_barang" required placeholder="Masukkan nama barang">
                                    </div>
                                    <div class="mb-3">
                                        <label for="incoming_kategori_barang" class="form-label">Kategori Barang *</label>
                                        <select class="form-select" id="incoming_kategori_barang" name="kategori_barang" required>
                                            <option value="">Pilih Kategori</option>
                                            <option value="Makanan">Makanan</option>
                                            <option value="Minuman">Minuman</option>
                                            <option value="Elektronik">Elektronik</option>
                                            <option value="Pakaian">Pakaian</option>
                                            <option value="Alat Tulis">Alat Tulis</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="incoming_jumlah_barang" class="form-label">Jumlah Barang *</label>
                                                <input type="number" class="form-control" id="incoming_jumlah_barang" 
                                                       name="jumlah_barang" required min="1" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="incoming_tanggal_masuk" class="form-label">Tanggal Masuk *</label>
                                                <input type="date" class="form-control" id="incoming_tanggal_masuk" 
                                                       name="tanggal_masuk_barang" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="incoming_lokasi_rak" class="form-label">Lokasi Rak</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="incoming_lokasi_rak" 
                                                   name="lokasi_rak_barang" pattern="R[1-8]-[1-4]-[1-6]" placeholder="R1-1-1 (opsional)">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="showRackSelector('incoming_lokasi_rak')">
                                                <i class="fas fa-map"></i> Pilih
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Format: R[1-8]-[1-4]-[1-6]</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" id="submitIncomingBtn">
                                            <i class="fas fa-save"></i> Simpan Barang Masuk
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Form Tambah Barang Keluar --}}
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fas fa-arrow-up"></i> Tambah Barang Keluar</h6>
                            </div>
                            <div class="card-body">
                                <form id="addOutgoingForm" onsubmit="handleItemCrudSubmit(event, 'outgoing', 'add')">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="outgoing_nama_barang" class="form-label">Nama Barang *</label>
                                        <select class="form-select" id="outgoing_nama_barang" name="nama_barang" required>
                                            <option value="">Pilih dari stok tersedia</option>
                                            @if(isset($incomingItems))
                                                @foreach($incomingItems->where('jumlah_barang', '>', 0) as $item)
                                                    <option value="{{ $item->nama_barang }}" data-category="{{ $item->kategori_barang }}" 
                                                            data-available="{{ $item->jumlah_barang }}" data-location="{{ $item->lokasi_rak_barang }}">
                                                        {{ $item->nama_barang }} ({{ $item->jumlah_barang }} unit tersedia)
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="outgoing_kategori_barang" class="form-label">Kategori Barang</label>
                                        <input type="text" class="form-control" id="outgoing_kategori_barang" 
                                               name="kategori_barang" readonly placeholder="Otomatis terisi">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="outgoing_jumlah_barang" class="form-label">Jumlah Keluar *</label>
                                                <input type="number" class="form-control" id="outgoing_jumlah_barang" 
                                                       name="jumlah_barang" required min="1" placeholder="0">
                                                <small class="form-text text-muted" id="availableStock"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="outgoing_tanggal_keluar" class="form-label">Tanggal Keluar *</label>
                                                <input type="date" class="form-control" id="outgoing_tanggal_keluar" 
                                                       name="tanggal_keluar_barang" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="outgoing_tujuan" class="form-label">Tujuan Distribusi *</label>
                                        <input type="text" class="form-control" id="outgoing_tujuan" 
                                               name="tujuan_distribusi" required placeholder="Nama toko/pelanggan">
                                    </div>
                                    <div class="mb-3">
                                        <label for="outgoing_lokasi_asal" class="form-label">Lokasi Rak Asal</label>
                                        <input type="text" class="form-control" id="outgoing_lokasi_asal" 
                                               name="lokasi_rak_barang" readonly placeholder="Otomatis dari stok">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-danger" id="submitOutgoingBtn">
                                            <i class="fas fa-arrow-up"></i> Proses Barang Keluar
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-primary w-100 mb-2" onclick="importFromCSV()">
                                            <i class="fas fa-file-csv"></i> Import dari CSV
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-success w-100 mb-2" onclick="generateBarcode()">
                                            <i class="fas fa-barcode"></i> Generate Barcode
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-warning w-100 mb-2" onclick="stockOpname()">
                                            <i class="fas fa-clipboard-check"></i> Stock Opname
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-info w-100 mb-2" onclick="viewWarehouse()">
                                            <i class="fas fa-warehouse"></i> Lihat Gudang
                                        </button>
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

{{-- Modal untuk Detail Item --}}
<div class="modal fade" id="itemDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="itemDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printItemDetails()">
                    <i class="fas fa-print"></i> Cetak Detail
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk Tambah/Edit Barang (Generic CRUD Modal) --}}
<div class="modal fade" id="itemCrudModal" tabindex="-1" aria-labelledby="itemCrudModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemCrudModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemCrudForm">
                    @csrf
                    {{-- Hidden field for method spoofing for PUT requests --}}
                    <input type="hidden" name="_method" value="POST" id="formMethod">
                    {{-- Hidden field for item ID in case of editing --}}
                    <input type="hidden" name="id" id="crudItemId">

                    <div id="itemCrudFormContent">
                        {{-- Form fields will be injected here by JavaScript --}}
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="itemCrudSubmitBtn">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk Pemilihan Lokasi Rak --}}
<div class="modal fade" id="rackSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Lokasi Rak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="warehouse-selector-grid" id="warehouseSelector">
                    <!-- Warehouse grid will be generated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmRackSelection()" disabled id="confirmRackBtn">
                    <i class="fas fa-check"></i> Pilih Lokasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk Import CSV --}}
<div class="modal fade" id="importCSVModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data dari CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="csvImportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">File CSV</label>
                        <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                        <small class="form-text text-muted">
                            Format: nama_barang, kategori_barang, jumlah_barang, tanggal_masuk_barang, lokasi_rak_barang
                        </small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hasHeader" checked>
                            <label class="form-check-label" for="hasHeader">
                                File memiliki header
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Template CSV:</strong>
                        <a href="#" onclick="downloadCSVTemplate()" class="alert-link">Download template CSV</a>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="processCSVImport()">
                    <i class="fas fa-upload"></i> Import Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Kustom --}}
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-labelledby="customConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="customConfirmModalLabel"><i class="fas fa-question-circle"></i> Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="customConfirmMessage">
                <!-- Message will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="customConfirmActionBtn">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>


<style>
/* Additional CSS for enhanced functionality */
.warehouse-selector-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    max-height: 400px;
    overflow-y: auto;
}

.warehouse-rack-selector {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    background: #f8f9fa;
}

.rack-selector-header {
    text-align: center;
    margin-bottom: 0.5rem;
    padding: 0.25rem;
    background: #6c757d;
    color: white;
    border-radius: 4px;
    font-weight: bold;
}

.rack-selector-grid {
    display: grid;
    gap: 2px;
}

.rack-selector-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 2px;
}

.rack-selector-cell {
    aspect-ratio: 1;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    min-height: 30px;
    background: #ffffff;
}

.rack-selector-cell.occupied {
    background: #dc3545;
    color: white;
    cursor: not-allowed;
}

.rack-selector-cell.selected {
    background: #28a745;
    color: white;
    transform: scale(1.1);
    z-index: 10;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
}

.rack-selector-cell:hover:not(.occupied) {
    background: #e9ecef;
    transform: scale(1.05);
}

.item-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(13, 110, 253, 0.1);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table-responsive {
    border-radius: 8px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .warehouse-selector-grid {
        grid-template-columns: 1fr;
    }
    
    .rack-selector-row {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }
}
</style>

<script>
// Fungsi-fungsi yang akan dipanggil dari HTML (onclick) harus berada di lingkup global
// atau diakses melalui window.namaFungsi. Untuk kemudahan, kita akan membuatnya global.

/**
 * Menginisialisasi fungsionalitas halaman setelah DOM dimuat.
 */
function initializePage() {
    // Setup filter functionality
    setupFilters();
    
    // Setup bulk actions
    setupBulkActions();
    
    // Setup outgoing item selector
    setupOutgoingItemSelector();
}

/**
 * Mengatur fungsionalitas filter dan pencarian untuk tabel barang.
 */
function setupFilters() {
    // Incoming items filters
    const searchIncomingInput = document.getElementById('searchIncomingInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dateIncomingFilter = document.getElementById('dateIncomingFilter');

    function filterIncomingTable() {
        const searchText = searchIncomingInput ? searchIncomingInput.value.toLowerCase() : '';
        const selectedCategory = categoryFilter ? categoryFilter.value : '';
        const selectedStatus = statusFilter ? statusFilter.value : '';
        const selectedDate = dateIncomingFilter ? dateIncomingFilter.value : '';
        
        const rows = document.querySelectorAll('#incomingTable tbody tr');

        rows.forEach(row => {
            if (row.cells.length < 2) return; // Skip empty rows
            
            const nameCell = row.cells[2]; // Nama Barang column
            const category = row.dataset.category || '';
            const status = row.dataset.status || '';
            const date = row.dataset.date || '';

            if (nameCell) {
                const nameText = nameCell.textContent.toLowerCase();
                const matchesSearch = nameText.includes(searchText);
                const matchesCategory = !selectedCategory || category === selectedCategory;
                const matchesStatus = !selectedStatus || status === selectedStatus;
                const matchesDate = !selectedDate || date === selectedDate;

                if (matchesSearch && matchesCategory && matchesStatus && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    if (searchIncomingInput) searchIncomingInput.addEventListener('keyup', filterIncomingTable);
    if (categoryFilter) categoryFilter.addEventListener('change', filterIncomingTable);
    if (statusFilter) statusFilter.addEventListener('change', filterIncomingTable);
    if (dateIncomingFilter) dateIncomingFilter.addEventListener('change', filterIncomingTable);

    // Outgoing items filters
    const searchOutgoingInput = document.getElementById('searchOutgoingInput');
    const outgoingCategoryFilter = document.getElementById('outgoingCategoryFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const dateOutgoingFilter = document.getElementById('dateOutgoingFilter');

    function filterOutgoingTable() {
        const searchText = searchOutgoingInput ? searchOutgoingInput.value.toLowerCase() : '';
        const selectedCategory = outgoingCategoryFilter ? outgoingCategoryFilter.value : '';
        const selectedDestination = destinationFilter ? destinationFilter.value : '';
        const selectedDate = dateOutgoingFilter ? dateOutgoingFilter.value : '';
        
        const rows = document.querySelectorAll('#outgoingTable tbody tr');

        rows.forEach(row => {
            if (row.cells.length < 2) return; // Skip empty rows
            
            const nameCell = row.cells[1]; // Nama Barang column
            const category = row.dataset.category || '';
            const destination = row.dataset.destination || '';
            const date = row.dataset.date || '';

            if (nameCell) {
                const nameText = nameCell.textContent.toLowerCase();
                const matchesSearch = nameText.includes(searchText);
                const matchesCategory = !selectedCategory || category === selectedCategory;
                const matchesDestination = !selectedDestination || destination === selectedDestination;
                const matchesDate = !selectedDate || date === selectedDate;

                if (matchesSearch && matchesCategory && matchesDestination && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    if (searchOutgoingInput) searchOutgoingInput.addEventListener('keyup', filterOutgoingTable);
    if (outgoingCategoryFilter) outgoingCategoryFilter.addEventListener('change', filterOutgoingTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterOutgoingTable);
    if (dateOutgoingFilter) dateOutgoingFilter.addEventListener('change', filterOutgoingTable);
}

/**
 * Mengatur fungsionalitas aksi bulk (pilih semua, edit/hapus terpilih).
 */
function setupBulkActions() {
    const selectAllIncoming = document.getElementById('selectAllIncoming');
    const selectAllIncomingHeader = document.getElementById('selectAllIncomingHeader');
    
    if (selectAllIncoming) {
        selectAllIncoming.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    if (selectAllIncomingHeader) {
        selectAllIncomingHeader.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
}

/**
 * Mengatur fungsionalitas pemilih barang keluar (mengisi kategori dan lokasi otomatis).
 */
function setupOutgoingItemSelector() {
    const outgoingItemSelect = document.getElementById('outgoing_nama_barang');
    const outgoingCategoryInput = document.getElementById('outgoing_kategori_barang');
    const outgoingLocationInput = document.getElementById('outgoing_lokasi_asal');
    const availableStockSpan = document.getElementById('availableStock');
    const outgoingQuantityInput = document.getElementById('outgoing_jumlah_barang');
    
    if (outgoingItemSelect) {
        outgoingItemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const category = selectedOption.dataset.category || '';
                const available = selectedOption.dataset.available || '0';
                const location = selectedOption.dataset.location || '';
                
                if (outgoingCategoryInput) outgoingCategoryInput.value = category;
                if (outgoingLocationInput) outgoingLocationInput.value = location;
                if (availableStockSpan) availableStockSpan.textContent = `Stok tersedia: ${available} unit`;
                if (outgoingQuantityInput) {
                    outgoingQuantityInput.max = available;
                    outgoingQuantityInput.placeholder = `Max: ${available}`;
                }
            } else {
                if (outgoingCategoryInput) outgoingCategoryInput.value = '';
                if (outgoingLocationInput) outgoingLocationInput.value = '';
                if (availableStockSpan) availableStockSpan.textContent = '';
                if (outgoingQuantityInput) {
                    outgoingQuantityInput.max = '';
                    outgoingQuantityInput.placeholder = '0';
                }
            }
        });
    }
}

/**
 * Memperbarui status tombol aksi bulk berdasarkan item yang dipilih.
 */
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkEditBtn = document.getElementById('bulkEditBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    const hasSelection = checkedBoxes.length > 0;
    
    if (bulkEditBtn) bulkEditBtn.disabled = !hasSelection;
    if (bulkDeleteBtn) bulkDeleteBtn.disabled = !hasSelection;
}

/**
 * Menampilkan modal detail barang (masuk atau keluar).
 * @param {number} itemId - ID barang.
 * @param {string} itemType - 'incoming' atau 'outgoing'.
 */
window.viewItemDetails = function(itemId, itemType = 'incoming') {
    let itemsData = [];
    if (itemType === 'incoming') {
        itemsData = @json($incomingItems ?? []);
    } else {
        itemsData = @json($outgoingItems ?? []);
    }

    const item = itemsData.find(i => i.id == itemId);

    if (item) {
        const modalContent = document.getElementById('itemDetailContent');
        let htmlContent = '';

        if (itemType === 'incoming') {
            htmlContent = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Barang</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr><td width="30%"><strong>ID Barang:</strong></td><td>#${item.id}</td></tr>
                                    <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                                    <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-secondary">${item.kategori_barang}</span></td></tr>
                                    <tr><td><strong>Jumlah:</strong></td><td><span class="fw-bold">${item.jumlah_barang}</span> unit</td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="badge ${getStatusBadgeClass(item.status_barang)}">${item.status_barang}</span></td></tr>
                                    <tr><td><strong>Tanggal Masuk:</strong></td><td>${new Date(item.tanggal_masuk_barang).toLocaleDateString('id-ID', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</td></tr>
                                    <tr><td><strong>Lokasi Rak:</strong></td><td>${item.lokasi_rak_barang ? '<span class="badge bg-info">' + item.lokasi_rak_barang + '</span>' : '<span class="badge bg-secondary">Belum ditempatkan</span>'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-cog"></i> Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning btn-sm" onclick="editIncomingItem(${item.id})">
                                        <i class="fas fa-edit"></i> Edit Barang
                                    </button>
                                    <button class="btn btn-info btn-sm" onclick="showRackSelector('crud_lokasi_rak', ${item.id})">
                                        <i class="fas fa-arrows-alt"></i> Pindah Lokasi
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="duplicateItem(${item.id})">
                                        <i class="fas fa-copy"></i> Duplikat Barang
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="generateQR(${item.id})">
                                        <i class="fas fa-qrcode"></i> QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-history"></i> Riwayat</h6>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">
                                    <div class="mb-2">
                                        <i class="fas fa-plus text-success"></i>
                                        Ditambahkan ${new Date(item.created_at || item.tanggal_masuk_barang).toLocaleDateString('id-ID')}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-edit text-warning"></i>
                                        Terakhir diubah ${new Date(item.updated_at || item.tanggal_masuk_barang).toLocaleDateString('id-ID')}
                                    </div>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else { // itemType === 'outgoing'
            htmlContent = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fas fa-shipping-fast"></i> Detail Pengiriman</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr><td width="30%"><strong>ID Pengiriman:</strong></td><td>#${item.id}</td></tr>
                                    <tr><td><strong>Nama Barang:</strong></td><td>${item.nama_barang}</td></tr>
                                    <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-secondary">${item.kategori_barang}</span></td></tr>
                                    <tr><td><strong>Jumlah:</strong></td><td><span class="fw-bold text-danger">${item.jumlah_barang}</span> unit</td></tr>
                                    <tr><td><strong>Tanggal Keluar:</strong></td><td>${new Date(item.tanggal_keluar_barang).toLocaleDateString('id-ID', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</td></tr>
                                    <tr><td><strong>Tujuan:</strong></td><td><span class="fw-bold">${item.tujuan_distribusi || 'Tidak diketahui'}</span></td></tr>
                                    <tr><td><strong>Lokasi Rak Asal:</strong></td><td>${item.lokasi_rak_barang ? '<span class="badge bg-info">' + item.lokasi_rak_barang + '</span>' : '<span class="badge bg-secondary">-</span>'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-tools"></i> Aksi</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="printDeliveryNote(${item.id})">
                                        <i class="fas fa-print"></i> Cetak Surat Jalan
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="trackDelivery(${item.id})">
                                        <i class="fas fa-truck"></i> Lacak Pengiriman
                                    </button>
                                    <button class="btn btn-info btn-sm" onclick="updateDeliveryStatus(${item.id})">
                                        <i class="fas fa-edit"></i> Update Status
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-clock"></i> Timeline</h6>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">
                                    <div class="mb-2">
                                        <i class="fas fa-plus text-success"></i>
                                        Dibuat ${new Date(item.created_at || item.tanggal_keluar_barang).toLocaleDateString('id-ID')}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-shipping-fast text-primary"></i>
                                        Dikirim ${new Date(item.tanggal_keluar_barang).toLocaleDateString('id-ID')}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-check text-success"></i>
                                        Status: <span class="badge bg-success">Selesai</span>
                                    </div>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        modalContent.innerHTML = htmlContent;
        const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
        modal.show();
    } else {
        showAlert('error', 'Data barang tidak ditemukan.');
    }
}

/**
 * Menampilkan modal form CRUD untuk menambah barang masuk baru.
 */
window.addNewIncomingItem = function() {
    renderItemCrudForm('incoming', 'add');
}

/**
 * Menampilkan modal form CRUD untuk mengedit barang masuk.
 * @param {number} itemId - ID barang masuk yang akan diedit.
 */
window.editIncomingItem = function(itemId) {
    fetch(`/staff/incoming-items/${itemId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderItemCrudForm('incoming', 'edit', result.data);
            } else {
                showAlert('error', result.message || 'Gagal memuat data barang.');
            }
        })
        .catch(error => {
            console.error('Error fetching incoming item for edit:', error);
            showAlert('error', 'Terjadi kesalahan saat memuat data barang.');
        });
}

/**
 * Menampilkan modal form CRUD untuk mengedit barang keluar.
 * @param {number} itemId - ID barang keluar yang akan diedit.
 */
window.editOutgoingItem = function(itemId) {
    fetch(`/staff/outgoing-items/${itemId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderItemCrudForm('outgoing', 'edit', result.data);
            } else {
                showAlert('error', result.message || 'Gagal memuat data barang keluar.');
            }
        })
        .catch(error => {
            console.error('Error fetching outgoing item for edit:', error);
            showAlert('error', 'Terjadi kesalahan saat memuat data barang keluar.');
        });
}

/**
 * Mengisi dan menampilkan modal form CRUD secara dinamis.
 * @param {string} itemType - 'incoming' atau 'outgoing'
 * @param {string} mode - 'add' atau 'edit'
 * @param {object} itemData - Data item jika dalam mode 'edit' atau 'moveToOutgoing'
 */
window.renderItemCrudForm = function(itemType, mode, itemData = null) {
    const modalLabel = document.getElementById('itemCrudModalLabel');
    const formContentDiv = document.getElementById('itemCrudFormContent');
    const crudItemIdField = document.getElementById('crudItemId');
    const formMethodField = document.getElementById('formMethod');
    const itemCrudForm = document.getElementById('itemCrudForm');
    const submitBtn = document.getElementById('itemCrudSubmitBtn');

    itemCrudForm.onsubmit = (event) => handleItemCrudSubmit(event, itemType, mode);

    let formHtml = '';
    itemCrudForm.reset(); // Reset form fields

    if (mode === 'add') {
        crudItemIdField.value = '';
        formMethodField.value = 'POST';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        submitBtn.classList.remove('btn-warning');
        submitBtn.classList.add('btn-primary');

        if (itemType === 'incoming') {
            modalLabel.textContent = 'Tambah Barang Masuk Baru';
            formHtml = `
                <div class="mb-3">
                    <label for="crud_nama_barang" class="form-label">Nama Barang *</label>
                    <input type="text" class="form-control" id="crud_nama_barang" name="nama_barang" required placeholder="Masukkan nama barang">
                </div>
                <div class="mb-3">
                    <label for="crud_kategori_barang" class="form-label">Kategori Barang *</label>
                    <select class="form-select" id="crud_kategori_barang" name="kategori_barang" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Elektronik">Elektronik</option>
                        <option value="Pakaian">Pakaian</option>
                        <option value="Alat Tulis">Alat Tulis</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_jumlah_barang" class="form-label">Jumlah Barang *</label>
                            <input type="number" class="form-control" id="crud_jumlah_barang" name="jumlah_barang" required min="1" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_tanggal_masuk" class="form-label">Tanggal Masuk *</label>
                            <input type="date" class="form-control" id="crud_tanggal_masuk" name="tanggal_masuk_barang" required value="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="crud_lokasi_rak" class="form-label">Lokasi Rak</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="crud_lokasi_rak" name="lokasi_rak_barang" pattern="R[1-8]-[1-4]-[1-6]" placeholder="R1-1-1 (opsional)">
                        <button type="button" class="btn btn-outline-secondary" onclick="showRackSelector('crud_lokasi_rak')">
                            <i class="fas fa-map"></i> Pilih
                        </button>
                    </div>
                    <small class="form-text text-muted">Format: R[1-8]-[1-4]-[1-6]</small>
                </div>
            `;
        } else { // outgoing - add mode (e.g., from "Pindah ke Barang Keluar")
            modalLabel.textContent = 'Proses Barang Keluar Baru';
            submitBtn.innerHTML = '<i class="fas fa-arrow-up"></i> Proses Barang Keluar';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-danger');

            let availableStockText = '';
            let defaultLocation = '';
            let defaultCategory = '';
            if (itemData) {
                availableStockText = `Stok tersedia: ${itemData.jumlah_barang} unit`;
                defaultLocation = itemData.lokasi_rak_barang || '';
                defaultCategory = itemData.kategori_barang || '';
            }

            formHtml = `
                <div class="mb-3">
                    <label for="crud_nama_barang" class="form-label">Nama Barang *</label>
                    <select class="form-select" id="crud_nama_barang" name="nama_barang" required onchange="updateOutgoingFormFields(this)">
                        <option value="">Pilih dari stok tersedia</option>
                        @if(isset($incomingItems))
                            @foreach($incomingItems->where('jumlah_barang', '>', 0) as $incItem)
                                <option value="{{ $incItem->nama_barang }}" 
                                        data-category="{{ $incItem->kategori_barang }}" 
                                        data-available="{{ $incItem->jumlah_barang }}" 
                                        data-location="{{ $incItem->lokasi_rak_barang }}"
                                        ${itemData && itemData.nama_barang === '{{ $incItem->nama_barang }}' ? 'selected' : ''}>
                                    {{ $incItem->nama_barang }} ({{ $incItem->jumlah_barang }} unit tersedia)
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="mb-3">
                    <label for="crud_kategori_barang" class="form-label">Kategori Barang</label>
                    <input type="text" class="form-control" id="crud_kategori_barang" name="kategori_barang" readonly placeholder="Otomatis terisi" value="${defaultCategory}">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_jumlah_barang" class="form-label">Jumlah Keluar *</label>
                            <input type="number" class="form-control" id="crud_jumlah_barang" name="jumlah_barang" required min="1" placeholder="0">
                            <small class="form-text text-muted" id="crud_availableStock">${availableStockText}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_tanggal_keluar" class="form-label">Tanggal Keluar *</label>
                            <input type="date" class="form-control" id="crud_tanggal_keluar" name="tanggal_keluar_barang" required value="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="crud_tujuan_distribusi" class="form-label">Tujuan Distribusi *</label>
                    <input type="text" class="form-control" id="crud_tujuan_distribusi" name="tujuan_distribusi" required placeholder="Nama toko/pelanggan">
                </div>
                <div class="mb-3">
                    <label for="crud_lokasi_asal" class="form-label">Lokasi Rak Asal</label>
                    <input type="text" class="form-control" id="crud_lokasi_asal" name="lokasi_rak_barang" readonly placeholder="Otomatis dari stok" value="${defaultLocation}">
                </div>
            `;
        }
    } else { // edit mode
        crudItemIdField.value = itemData.id;
        formMethodField.value = 'PUT';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-warning');

        if (itemType === 'incoming') {
            modalLabel.textContent = `Edit Barang Masuk (ID: #${itemData.id})`;
            formHtml = `
                <div class="mb-3">
                    <label for="crud_nama_barang" class="form-label">Nama Barang *</label>
                    <input type="text" class="form-control" id="crud_nama_barang" name="nama_barang" value="${itemData.nama_barang}" required>
                </div>
                <div class="mb-3">
                    <label for="crud_kategori_barang" class="form-label">Kategori Barang *</label>
                    <select class="form-select" id="crud_kategori_barang" name="kategori_barang" required>
                        <option value="Makanan" ${itemData.kategori_barang === 'Makanan' ? 'selected' : ''}>Makanan</option>
                        <option value="Minuman" ${itemData.kategori_barang === 'Minuman' ? 'selected' : ''}>Minuman</option>
                        <option value="Elektronik" ${itemData.kategori_barang === 'Elektronik' ? 'selected' : ''}>Elektronik</option>
                        <option value="Pakaian" ${itemData.kategori_barang === 'Pakaian' ? 'selected' : ''}>Pakaian</option>
                        <option value="Alat Tulis" ${itemData.kategori_barang === 'Alat Tulis' ? 'selected' : ''}>Alat Tulis</option>
                        <option value="Lainnya" ${itemData.kategori_barang === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_jumlah_barang" class="form-label">Jumlah Barang *</label>
                            <input type="number" class="form-control" id="crud_jumlah_barang" name="jumlah_barang" value="${itemData.jumlah_barang}" required min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_tanggal_masuk" class="form-label">Tanggal Masuk *</label>
                            <input type="date" class="form-control" id="crud_tanggal_masuk" name="tanggal_masuk_barang" value="${new Date(itemData.tanggal_masuk_barang).toISOString().split('T')[0]}" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="crud_lokasi_rak" class="form-label">Lokasi Rak</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="crud_lokasi_rak" name="lokasi_rak_barang" value="${itemData.lokasi_rak_barang || ''}" pattern="R[1-8]-[1-4]-[1-6]" placeholder="R1-1-1 (opsional)">
                        <button type="button" class="btn btn-outline-secondary" onclick="showRackSelector('crud_lokasi_rak')">
                            <i class="fas fa-map"></i> Pilih
                        </button>
                    </div>
                    <small class="form-text text-muted">Format: R[1-8]-[1-4]-[1-6]</small>
                </div>
            `;
        } else { // outgoing - edit mode
            modalLabel.textContent = `Edit Barang Keluar (ID: #${itemData.id})`;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
            submitBtn.classList.remove('btn-primary', 'btn-danger');
            submitBtn.classList.add('btn-warning');

            formHtml = `
                <div class="mb-3">
                    <label for="crud_nama_barang" class="form-label">Nama Barang *</label>
                    <input type="text" class="form-control" id="crud_nama_barang" name="nama_barang" value="${itemData.nama_barang}" required>
                </div>
                <div class="mb-3">
                    <label for="crud_kategori_barang" class="form-label">Kategori Barang *</label>
                    <input type="text" class="form-control" id="crud_kategori_barang" name="kategori_barang" value="${itemData.kategori_barang}" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_jumlah_barang" class="form-label">Jumlah Keluar *</label>
                            <input type="number" class="form-control" id="crud_jumlah_barang" name="jumlah_barang" value="${itemData.jumlah_barang}" required min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="crud_tanggal_keluar" class="form-label">Tanggal Keluar *</label>
                            <input type="date" class="form-control" id="crud_tanggal_keluar" name="tanggal_keluar_barang" value="${new Date(itemData.tanggal_keluar_barang).toISOString().split('T')[0]}" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="crud_tujuan_distribusi" class="form-label">Tujuan Distribusi *</label>
                    <input type="text" class="form-control" id="crud_tujuan_distribusi" name="tujuan_distribusi" value="${itemData.tujuan_distribusi}" required>
                </div>
                <div class="mb-3">
                    <label for="crud_lokasi_asal" class="form-label">Lokasi Rak Asal</label>
                    <input type="text" class="form-control" id="crud_lokasi_asal" name="lokasi_rak_barang" value="${itemData.lokasi_rak_barang || ''}" pattern="R[1-8]-[1-4]-[1-6]" placeholder="R1-1-1 (opsional)">
                </div>
            `;
        }
    }

    formContentDiv.innerHTML = formHtml;
    const itemCrudModal = new bootstrap.Modal(document.getElementById('itemCrudModal'));
    itemCrudModal.show();
}

/**
 * Menangani submit form CRUD (tambah/edit barang masuk atau keluar).
 */
window.handleItemCrudSubmit = async function(event, itemType, mode) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const itemId = document.getElementById('crudItemId').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const submitBtn = document.getElementById('itemCrudSubmitBtn');
    const originalBtnHtml = submitBtn.innerHTML;

    let url = '';
    let method = 'POST'; // Default for Laravel with _method spoofing

    if (itemType === 'incoming') {
        if (mode === 'add') {
            url = '{{ route("staff.incoming_items.store") }}';
        } else { // mode === 'edit'
            url = `/staff/incoming-items/${itemId}`;
            formData.append('_method', 'PUT'); // Spoof PUT method
        }
    } else { // itemType === 'outgoing'
        // Validate stock for outgoing items before sending
        if (mode === 'add') {
            const selectedItemName = document.getElementById('crud_nama_barang').value;
            const requestedQuantity = parseInt(document.getElementById('crud_jumlah_barang').value);
            const selectedOption = document.querySelector(`#crud_nama_barang option[value="${selectedItemName}"]`);
            const availableStock = parseInt(selectedOption ? selectedOption.dataset.available : '0');

            if (requestedQuantity > availableStock) {
                showAlert('error', `Jumlah yang diminta (${requestedQuantity}) melebihi stok yang tersedia (${availableStock}).`);
                return;
            }
            url = '{{ route("staff.outgoing_items.store") }}';
        } else { // mode === 'edit'
            url = `/staff/outgoing-items/${itemId}`;
            formData.append('_method', 'PUT'); // Spoof PUT method
        }
    }

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="loading-spinner"></div> Memproses...';

    try {
        const response = await fetch(url, {
            method: 'POST', // Always POST for Laravel with _method spoofing
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', data.message);
            const itemCrudModal = bootstrap.Modal.getInstance(document.getElementById('itemCrudModal'));
            itemCrudModal.hide();
            location.reload(); // Reload page to reflect changes
        } else {
            let errorMessage = data.message || 'Terjadi kesalahan.';
            if (data.errors) {
                for (const key in data.errors) {
                    errorMessage += `\n- ${data.errors[key][0]}`;
                }
            }
            showAlert('error', errorMessage);
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        showAlert('error', 'Terjadi kesalahan jaringan atau server: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    }
}

/**
 * Menangani penghapusan barang masuk.
 * @param {number} itemId - ID barang masuk yang akan dihapus.
 */
window.deleteIncomingItem = function(itemId) {
    showCustomConfirm('Apakah Anda yakin ingin menghapus barang masuk ini? Tindakan ini tidak dapat dibatalkan.', async () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        showAlert('info', 'Menghapus barang masuk...');

        try {
            const response = await fetch(`/staff/incoming-items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Gagal menghapus barang masuk.');
            }
        } catch (error) {
            console.error('Delete Error:', error);
            showAlert('error', 'Terjadi kesalahan jaringan saat menghapus barang masuk.');
        }
    });
}

/**
 * Menangani penghapusan barang keluar.
 * @param {number} itemId - ID barang keluar yang akan dihapus.
 */
window.deleteOutgoingItem = function(itemId) {
    showCustomConfirm('Apakah Anda yakin ingin menghapus barang keluar ini? Tindakan ini tidak dapat dibatalkan.', async () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        showAlert('info', 'Menghapus barang keluar...');

        try {
            const response = await fetch(`/staff/outgoing-items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Gagal menghapus barang keluar.');
            }
        } catch (error) {
            console.error('Delete Error:', error);
            showAlert('error', 'Terjadi kesalahan jaringan saat menghapus barang keluar.');
        }
    });
}

/**
 * Memperbarui field form barang keluar saat nama barang dipilih.
 * @param {HTMLSelectElement} selectElement - Elemen select nama barang.
 */
window.updateOutgoingFormFields = function(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const category = selectedOption.dataset.category || '';
    const available = selectedOption.dataset.available || '0';
    const location = selectedOption.dataset.location || '';
    
    document.getElementById('crud_kategori_barang').value = category;
    document.getElementById('crud_lokasi_asal').value = location;
    document.getElementById('crud_availableStock').textContent = `Stok tersedia: ${available} unit`;
    document.getElementById('crud_jumlah_barang').max = available;
    document.getElementById('crud_jumlah_barang').placeholder = `Max: ${available}`;
}

/**
 * Menampilkan selector rak gudang.
 * @param {string} targetInputId - ID input yang akan diisi dengan lokasi rak yang dipilih.
 * @param {number|null} itemId - ID item jika sedang memindahkan item yang sudah ada.
 */
window.showRackSelector = function(targetInputId, itemId = null) {
    window.currentTargetInput = targetInputId;
    window.currentItemIdForRack = itemId; // Store current item ID if moving an existing item
    generateWarehouseSelector();
    const modal = new bootstrap.Modal(document.getElementById('rackSelectorModal'));
    modal.show();
}

/**
 * Menghasilkan grid gudang di modal pemilihan rak.
 */
window.generateWarehouseSelector = async function() {
    const warehouseSelector = document.getElementById('warehouseSelector');
    if (!warehouseSelector) return;
    
    showAlert('info', 'Memuat data lokasi gudang...');

    try {
        const response = await fetch('{{ route("staff.locations.available") }}', {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();

        if (result.success) {
            const allLocations = result.data;
            let html = '';
            for (let rak = 1; rak <= 8; rak++) {
                html += `
                    <div class="warehouse-rack-selector">
                        <div class="rack-selector-header">Rak ${rak}</div>
                        <div class="rack-selector-grid">
                `;
                
                for (let row = 1; row <= 4; row++) {
                    html += '<div class="rack-selector-row">';
                    for (let col = 1; col <= 6; col++) {
                        const position = `R${rak}-${row}-${col}`;
                        const locationData = allLocations.find(loc => loc.location === position);
                        const isOccupied = locationData ? !locationData.available : true; // If not found, assume occupied or invalid
                        const currentItemLocation = document.getElementById(window.currentTargetInput)?.value;
                        const isCurrentItemLocation = currentItemLocation === position;

                        let cellClass = 'rack-selector-cell';
                        let cellContent = position.split('-').slice(1).join('-'); // Default content

                        if (isOccupied && !isCurrentItemLocation) {
                            cellClass += ' occupied';
                            cellContent = '<i class="fas fa-times"></i>'; // Mark as occupied
                        } else if (isCurrentItemLocation) {
                            cellClass += ' selected'; // Highlight current item's location
                            cellContent = '<i class="fas fa-check"></i>'; // Mark as current
                        }
                        
                        html += `
                            <div class="${cellClass}" data-position="${position}" 
                                 onclick="selectRackPosition('${position}', ${isOccupied && !isCurrentItemLocation})">
                                ${cellContent}
                            </div>
                        `;
                    }
                    html += '</div>';
                }
                
                html += '</div></div>';
            }
            warehouseSelector.innerHTML = html;
            showAlert('success', 'Data lokasi gudang berhasil dimuat.');
        } else {
            showAlert('error', result.message || 'Gagal memuat data lokasi gudang.');
        }
    } catch (error) {
        console.error('Error fetching available locations:', error);
        showAlert('error', 'Terjadi kesalahan jaringan saat memuat lokasi gudang.');
    }
}

/**
 * Memilih posisi rak di modal selector.
 * @param {string} position - Posisi rak yang dipilih.
 * @param {boolean} isOccupied - Apakah posisi rak sudah ditempati.
 */
window.selectRackPosition = function(position, isOccupied) {
    if (isOccupied) {
        showAlert('warning', 'Lokasi ini sudah ditempati barang lain.');
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.rack-selector-cell.selected').forEach(cell => {
        // Only remove 'selected' if it's not the current item's original location
        const currentItemLocation = document.getElementById(window.currentTargetInput)?.value;
        if (cell.dataset.position !== currentItemLocation) {
            cell.classList.remove('selected');
            cell.innerHTML = cell.dataset.position.split('-').slice(1).join('-'); // Reset content
        }
    });
    
    // Add selection to clicked cell
    const cell = document.querySelector(`[data-position="${position}"]`);
    if (cell) {
        cell.classList.add('selected');
        cell.innerHTML = '<i class="fas fa-check"></i>'; // Mark as selected
        window.selectedRackPosition = position;
        
        // Enable confirm button
        const confirmBtn = document.getElementById('confirmRackBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = `Pilih ${position}`;
        }
    }
}

/**
 * Mengkonfirmasi pemilihan rak dan mengisi input target.
 */
window.confirmRackSelection = async function() {
    if (window.selectedRackPosition && window.currentTargetInput) {
        const targetInput = document.getElementById(window.currentTargetInput);
        if (targetInput) {
            targetInput.value = window.selectedRackPosition;

            // If moving an existing item, send an update request
            if (window.currentItemIdForRack) {
                const itemId = window.currentItemIdForRack;
                const newLocation = window.selectedRackPosition;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                showAlert('info', `Memperbarui lokasi barang #${itemId} ke ${newLocation}...`);

                // Fetch current item data to send a complete update request
                try {
                    const itemResponse = await fetch(`/staff/incoming-items/${itemId}`);
                    const itemResult = await itemResponse.json();

                    if (!itemResult.success) {
                        showAlert('error', itemResult.message || 'Gagal memuat data barang untuk diperbarui lokasinya.');
                        return;
                    }
                    const existingItemData = itemResult.data;

                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('nama_barang', existingItemData.nama_barang);
                    formData.append('kategori_barang', existingItemData.kategori_barang);
                    formData.append('jumlah_barang', existingItemData.jumlah_barang);
                    formData.append('tanggal_masuk_barang', existingItemData.tanggal_masuk_barang);
                    formData.append('lokasi_rak_barang', newLocation); // Update only location

                    const response = await fetch(`/staff/incoming-items/${itemId}`, {
                        method: 'POST', // Laravel will interpret PUT via _method
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        location.reload(); // Reload untuk merefleksikan perubahan
                    } else {
                        showAlert('error', data.message || 'Gagal memperbarui lokasi barang.');
                    }
                } catch (error) {
                    console.error('Error updating item location:', error);
                    showAlert('error', 'Terjadi kesalahan jaringan saat memperbarui lokasi barang.');
                }
            }
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('rackSelectorModal'));
        modal.hide();
        
        // Reset selections
        window.selectedRackPosition = null;
        window.currentTargetInput = null;
        window.currentItemIdForRack = null;
    }
}

/**
 * Menampilkan lokasi rak di halaman pengelolaan barang.
 * @param {string} rackPosition - Posisi rak yang akan ditampilkan.
 */
window.showRackLocation = function(rackPosition) {
    @if(Route::has('staff.item.management'))
        window.location.href = `{{ route('staff.item.management') }}?highlight=${rackPosition}`;
    @else
        showAlert('info', `Lokasi barang: ${rackPosition}. Halaman pengelolaan gudang tidak tersedia.`);
    @endif
}

/**
 * Menetapkan lokasi rak dengan cepat untuk barang masuk.
 * @param {number} itemId - ID barang masuk.
 */
window.quickAssignLocation = function(itemId) {
    showCustomConfirm('Apakah Anda ingin menetapkan lokasi rak secara otomatis untuk barang ini?', async () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        showAlert('info', 'Menetapkan lokasi otomatis...');

        try {
            // Fetch current item data to send a complete update request
            const itemResponse = await fetch(`/staff/incoming-items/${itemId}`);
            const itemResult = await itemResponse.json();

            if (!itemResult.success) {
                showAlert('error', itemResult.message || 'Gagal memuat data barang untuk penetapan lokasi otomatis.');
                return;
            }
            const existingItemData = itemResult.data;

            // Call auto-assign endpoint for this specific item
            const response = await fetch('{{ route("staff.items.auto_assign_locations") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    items: [{ id: itemId }], // Send the specific item to be auto-assigned
                    // No need to send 'location' here, backend will find it
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Gagal menetapkan lokasi otomatis.');
            }
        } catch (error) {
            console.error('Quick assign error:', error);
            showAlert('error', 'Terjadi kesalahan jaringan saat menetapkan lokasi.');
        }
    });
}

/**
 * Memindahkan barang masuk ke daftar barang keluar.
 * @param {number} itemId - ID barang masuk.
 */
window.moveToOutgoing = function(itemId) {
    showCustomConfirm('Apakah Anda yakin ingin memindahkan barang ini ke daftar barang keluar? Ini akan mengurangi stok barang masuk dan menambahkannya ke barang keluar.', async () => {
        // Fetch incoming item details to pre-fill the outgoing form
        try {
            const response = await fetch(`/staff/incoming-items/${itemId}`);
            const result = await response.json();

            if (result.success) {
                const itemData = result.data;
                // Switch to add-items tab
                const addTabButton = document.getElementById('add-items-tab');
                if (addTabButton) {
                    addTabButton.click(); // Activate the "Tambah Barang" tab
                }
                
                // Render the outgoing form in 'add' mode with incoming item data
                renderItemCrudForm('outgoing', 'add', itemData);
                // Pre-select the item in the dropdown
                const outgoingItemSelect = document.getElementById('crud_nama_barang');
                if (outgoingItemSelect) {
                    // Find the option by value (nama_barang) and set it as selected
                    for (let i = 0; i < outgoingItemSelect.options.length; i++) {
                        if (outgoingItemSelect.options[i].value === itemData.nama_barang) {
                            outgoingItemSelect.selectedIndex = i;
                            outgoingItemSelect.dispatchEvent(new Event('change')); // Trigger change to populate other fields
                            break;
                        }
                    }
                }
                showAlert('info', `Silakan lengkapi detail untuk memproses barang keluar "${itemData.nama_barang}".`);
            } else {
                showAlert('error', result.message || 'Gagal memuat data barang untuk dipindahkan.');
            }
        } catch (error) {
            console.error('Error fetching item for move to outgoing:', error);
            showAlert('error', 'Terjadi kesalahan saat memuat data barang.');
        }
    });
}

/**
 * Melakukan aksi bulk (edit lokasi atau hapus) pada barang masuk.
 */
window.bulkEditLocation = function() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const itemIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (itemIds.length === 0) {
        showAlert('warning', 'Pilih minimal satu item untuk diedit.');
        return;
    }
    
    showCustomConfirm(`Apakah Anda yakin ingin mengedit lokasi ${itemIds.length} item yang terpilih?`, () => {
        const newLocation = prompt('Masukkan lokasi rak baru (Format: R[1-8]-[1-4]-[1-6]):');
        if (newLocation) {
            const pattern = /^R[1-8]-[1-4]-[1-6]$/;
            if (pattern.test(newLocation)) {
                sendBulkAction(itemIds, 'update_location', { location: newLocation });
            } else {
                showAlert('error', 'Format lokasi tidak valid! Gunakan format: R[1-8]-[1-4]-[1-6]');
            }
        }
    });
}

window.bulkDelete = function() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const itemIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (itemIds.length === 0) {
        showAlert('warning', 'Pilih minimal satu item untuk dihapus.');
        return;
    }
    
    showCustomConfirm(`Apakah Anda yakin ingin menghapus ${itemIds.length} item yang terpilih? Tindakan ini tidak dapat dibatalkan.`, () => {
        sendBulkAction(itemIds, 'delete');
    });
}

/**
 * Mengirim permintaan bulk action ke backend.
 * @param {Array<number>} itemIds - Array ID item.
 * @param {string} action - Tipe aksi ('update_category', 'update_location', 'delete').
 * @param {object} payload - Data tambahan untuk aksi (misal: { category: 'New Category' }).
 */
window.sendBulkAction = async function(itemIds, action, payload = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    showAlert('info', `Memproses aksi bulk (${action})...`);

    try {
        const response = await fetch('{{ route("staff.items.bulk_update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                items: itemIds.map(id => ({ id: id })),
                action: action,
                ...payload
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            let errorMessage = data.message || 'Gagal melakukan aksi bulk.';
            if (data.errors) {
                for (const key in data.errors) {
                    errorMessage += `\n- ${data.errors[key][0]}`;
                }
            }
            showAlert('error', errorMessage);
        }
    } catch (error) {
        console.error('Bulk action error:', error);
        showAlert('error', 'Terjadi kesalahan jaringan saat melakukan aksi bulk.');
    }
}

// Placeholder functions for other actions
window.printDeliveryNote = function(itemId) {
    showAlert('info', 'Menyiapkan surat jalan untuk dicetak...');
    // Implement actual print logic or API call here
    setTimeout(() => {
        showAlert('success', 'Surat jalan berhasil dicetak!');
    }, 1500);
}

window.trackDelivery = function(itemId) {
    showAlert('info', 'Fitur pelacakan pengiriman akan segera hadir!');
}

window.updateDeliveryStatus = function(itemId) {
    showCustomConfirm('Apakah Anda ingin memperbarui status pengiriman ini?', () => {
        const newStatus = prompt('Masukkan status baru (Pending, Dalam Perjalanan, Selesai):');
        if (newStatus) {
            showAlert('success', `Status pengiriman berhasil diubah menjadi: ${newStatus}`);
            // Implement AJAX call to update outgoing item status
            // location.reload();
        }
    });
}

window.exportData = function(format) {
    const activeTab = document.querySelector('.nav-link.active').textContent.trim();
    showAlert('info', `Mengexport data ${activeTab} ke format ${format.toUpperCase()}...`);
    
    // Implement actual export logic or redirect to export route
    // Example: window.location.href = `{{ route('staff.items.export_csv') }}?type=incoming&format=${format}`;
    
    setTimeout(() => {
        showAlert('success', `Data berhasil diexport ke ${format.toUpperCase()}!`);
    }, 2000);
}

window.refreshData = function() {
    showAlert('info', 'Memuat ulang data...');
    setTimeout(() => {
        location.reload();
    }, 1000);
}

window.importFromCSV = function() {
    const modal = new bootstrap.Modal(document.getElementById('importCSVModal'));
    modal.show();
}

window.processCSVImport = async function() {
    const csvFile = document.getElementById('csvFile');
    if (!csvFile.files[0]) {
        showAlert('error', 'Pilih file CSV terlebih dahulu.');
        return;
    }
    
    const formData = new FormData();
    formData.append('csv_file', csvFile.files[0]);
    formData.append('has_header', document.getElementById('hasHeader').checked ? '1' : '0');
    // Determine type based on active tab or a new input in the modal
    const activeTabId = document.querySelector('#itemTabs .nav-link.active').id;
    let importType = 'incoming';
    if (activeTabId === 'outgoing-items-tab') {
        importType = 'outgoing';
    }
    formData.append('type', importType);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    showAlert('info', 'Memproses import data...');
    
    try {
        const response = await fetch('{{ route("staff.items.import_csv") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('importCSVModal'));
            modal.hide();
            showAlert('success', data.message + (data.errors.length > 0 ? ` Beberapa kesalahan terjadi: ${data.errors.join(', ')}` : ''));
            location.reload();
        } else {
            let errorMessage = data.message || 'Gagal mengimport data.';
            if (data.errors) {
                errorMessage += `\n- ${data.errors.join('\n- ')}`;
            }
            showAlert('error', errorMessage);
        }
    } catch (error) {
        console.error('Import CSV Error:', error);
        showAlert('error', 'Terjadi kesalahan jaringan saat mengimport CSV.');
    }
}

window.downloadCSVTemplate = function() {
    const csvContent = "nama_barang,kategori_barang,jumlah_barang,tanggal_masuk_barang,lokasi_rak_barang\n" +
                      "Contoh Barang,Makanan,100,2024-01-01,R1-1-1\n" +
                      "Contoh Barang 2,Minuman,50,2024-01-01,R1-1-2";
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template_barang_masuk.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    showAlert('info', 'Template CSV berhasil diunduh.');
}

window.generateBarcode = function() {
    showAlert('info', 'Fitur generate barcode akan segera hadir!');
}

window.stockOpname = function() {
    showAlert('info', 'Fitur stock opname akan segera hadir!');
}

window.viewWarehouse = function() {
    @if(Route::has('staff.warehouse_monitor'))
        window.open('{{ route("staff.warehouse_monitor") }}', '_blank');
    @else
        showAlert('info', 'Halaman monitor gudang tidak tersedia.');
    @endif
}

// Helper functions
window.getStatusBadgeClass = function(status) {
    switch(status) {
        case 'Banyak': return 'bg-success';
        case 'Sedikit': return 'bg-warning';
        case 'Habis': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

/**
 * Menampilkan pesan alert kustom.
 * @param {string} type - Tipe alert ('success', 'error', 'warning', 'info').
 * @param {string} message - Pesan yang akan ditampilkan.
 */
window.showAlert = function(type, message) {
    // Remove any existing custom alerts to prevent stacking
    const existingAlert = document.querySelector('.custom-fixed-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-fixed-alert`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.opacity = '0'; // Start invisible for animation
    alertDiv.style.transform = 'translateY(-20px)'; // Start slightly above for animation
    alertDiv.style.transition = 'all 0.5s ease'; // Smooth transition

    const iconMap = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    alertDiv.innerHTML = `
        <i class="${iconMap[type] || 'fas fa-info-circle'}"></i>
        <div>${message.replace(/\n/g, '<br>')}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Trigger fade-in animation
    setTimeout(() => {
        alertDiv.style.opacity = '1';
        alertDiv.style.transform = 'translateY(0)';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => alertDiv.remove(), 500); // Remove after transition
        }
    }, 5000);
}

/**
 * Menampilkan modal konfirmasi kustom.
 * @param {string} message - Pesan konfirmasi.
 * @param {Function} onConfirm - Callback saat user mengkonfirmasi.
 */
window.showCustomConfirm = function(message, onConfirm) {
    const confirmModal = new bootstrap.Modal(document.getElementById('customConfirmModal'));
    const confirmMessage = document.getElementById('customConfirmMessage');
    const confirmBtn = document.getElementById('customConfirmActionBtn');

    confirmMessage.textContent = message;
    
    // Clear previous event listener
    confirmBtn.onclick = null; 
    
    // Set new event listener
    confirmBtn.onclick = () => {
        onConfirm();
        confirmModal.hide();
    };

    confirmModal.show();
}

window.printItemDetails = function() {
    window.print();
}

window.duplicateItem = function(itemId) {
    showCustomConfirm('Apakah Anda yakin ingin menduplikasi barang ini?', async () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        showAlert('info', 'Menduplikasi barang...');

        try {
            const response = await fetch(`/staff/items/${itemId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Gagal menduplikasi barang.');
            }
        } catch (error) {
            console.error('Duplicate item error:', error);
            showAlert('error', 'Terjadi kesalahan jaringan saat menduplikasi barang.');
        }
    });
}

window.generateQR = function(itemId) {
    showAlert('info', 'Generating QR Code...');
    // Implement actual QR code generation logic or API call here
    setTimeout(() => {
        showAlert('success', 'QR Code berhasil dibuat!');
    }, 1500);
}

// Function to handle moving item (calls showRackSelector with item ID)
window.moveItem = function(itemId) {
    // Note: The input field 'crud_lokasi_rak' is part of the itemCrudModal.
    // If you call this from viewItemDetails, ensure the itemCrudModal is not open
    // or handle the context properly. For simplicity, we assume this is for
    // assigning a location to an item that doesn't have one, or changing it.
    showRackSelector('crud_lokasi_rak', itemId); // Pass item ID to rack selector
}


// Pastikan kode ini berjalan setelah DOM sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi halaman
    initializePage();
    
    // Set default dates for forms
    const incomingTanggalMasuk = document.getElementById('incoming_tanggal_masuk');
    if (incomingTanggalMasuk) {
        incomingTanggalMasuk.value = new Date().toISOString().split('T')[0];
    }
    const outgoingTanggalKeluar = document.getElementById('outgoing_tanggal_keluar');
    if (outgoingTanggalKeluar) {
        outgoingTanggalKeluar.value = new Date().toISOString().split('T')[0];
    }
    
    // Auto dismiss alerts after 5 seconds (already global, but safe to re-run for new alerts)
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Add smooth fade-in animation for alerts (already global, but safe to re-run for new alerts)
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
