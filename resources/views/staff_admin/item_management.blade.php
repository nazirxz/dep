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
                        <a class="nav-link" href="{{ route('staff.items.index') }}">
                            <i class="fas fa-boxes"></i>Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('staff.item.management') }}">
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

        {{-- Main Content untuk Pengelolaan Barang --}}
        <div class="col-md-10 offset-md-2 main-content">
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> Staff Admin</span>
                <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="fas fa-bars"></span>
                </button>
            </div>

            {{-- Tab Navigation --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="managementTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="warehouse-location-tab" data-bs-toggle="tab" data-bs-target="#warehouse-location" type="button" role="tab">
                                <i class="fas fa-warehouse"></i> Lokasi Barang di Gudang
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="outgoing-orders-tab" data-bs-toggle="tab" data-bs-target="#outgoing-orders" type="button" role="tab">
                                <i class="fas fa-truck"></i> Daftar Pesanan Barang Keluar
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="managementTabsContent">
                        {{-- Tab 1: Lokasi Barang di Gudang --}}
                        <div class="tab-pane fade show active" id="warehouse-location" role="tabpanel">
                            <div class="row">
                                {{-- Peta Gudang --}}
                                <div class="col-lg-8">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Lokasi Barang di Gudang</h5>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showWarehouseView()">
                                                    Tampilkan ke Layar Monitor
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            {{-- Grid Gudang 8 Rak --}}
                                            <div class="warehouse-grid">
                                                @for($rak = 1; $rak <= 8; $rak++)
                                                    <div class="warehouse-rack mb-4">
                                                        <div class="rack-header">
                                                            <h6>Rak {{ $rak }}</h6>
                                                        </div>
                                                        <div class="rack-grid">
                                                            @for($row = 1; $row <= 4; $row++)
                                                                <div class="rack-row">
                                                                    @for($col = 1; $col <= 6; $col++)
                                                                        @php
                                                                            $position = "R{$rak}-{$row}-{$col}";
                                                                            $hasItem = $incomingItems->where('lokasi_rak_barang', $position)->first();
                                                                        @endphp
                                                                        <div class="rack-cell {{ $hasItem ? 'occupied' : 'empty' }}" 
                                                                             data-position="{{ $position }}"
                                                                             data-item-id="{{ $hasItem ? $hasItem->id : '' }}"
                                                                             onclick="showRackDetails('{{ $position }}', {{ $hasItem ? $hasItem->id : 'null' }})">
                                                                            @if($hasItem)
                                                                                <div class="item-preview">
                                                                                    <small>{{ substr($hasItem->nama_barang, 0, 8) }}...</small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endfor
                                                                </div>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                            
                                            {{-- Legend --}}
                                            <div class="warehouse-legend mt-3">
                                                <div class="d-flex gap-3 justify-content-center">
                                                    <div class="legend-item">
                                                        <span class="legend-color empty"></span>
                                                        <small>Kosong</small>
                                                    </div>
                                                    <div class="legend-item">
                                                        <span class="legend-color occupied"></span>
                                                        <small>Terisi</small>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-2">
                                                    <button class="btn btn-sm btn-secondary" onclick="refreshWarehouse()">
                                                        <i class="fas fa-sync-alt"></i> Muat Ulang
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Detail Rak Barang --}}
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Detail Rak Barang</h6>
                                        </div>
                                        <div class="card-body" id="rackDetailsContainer">
                                            <div class="text-center text-muted py-5">
                                                <i class="fas fa-click fa-3x mb-3"></i>
                                                <p>Klik pada rak untuk melihat detail</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Daftar Pesanan Barang Keluar --}}
                        <div class="tab-pane fade" id="outgoing-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Daftar Pesanan Barang Keluar</h5>
                                <div class="input-group w-auto">
                                    <input type="text" class="form-control" placeholder="Mencari..." id="searchOutgoingInput">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover" id="outgoingTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Pengecer</th>
                                            <th>Tanggal Keluar Barang</th>
                                            <th>Daftar Barang</th>
                                            <th>Jumlah Barang</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($outgoingItems as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->tujuan_distribusi ?? 'Tidak Diketahui' }}</td>
                                                <td>{{ $item->tanggal_keluar_barang->format('d M Y') }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->jumlah_barang }} unit</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary me-1" onclick="showRackLocation('{{ $item->lokasi_rak_barang }}')">
                                                        <i class="fas fa-map-marker-alt"></i> Tampilkan Lokasi Rak Barang
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="markAsCompleted({{ $item->id }})">
                                                        <i class="fas fa-check"></i> Barang Sudah Selesai dikemas
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada pesanan barang keluar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga {{ $outgoingItems->count() }} dari {{ $outgoingItems->count() }} tabel</small>
                                <div>
                                    <button class="btn btn-sm btn-light">Sebelumnya</button>
                                    <button class="btn btn-sm btn-light">Berikutnya</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Detail Rak -->
<div class="modal fade" id="rackDetailModal" tabindex="-1" aria-labelledby="rackDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rackDetailModalLabel">Detail Rak Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalRackDetails">
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

    /* Warehouse Grid Styles */
    .warehouse-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        max-height: 600px;
        overflow-y: auto;
    }

    .warehouse-rack {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem;
        background: #f8f9fa;
    }

    .rack-header {
        text-align: center;
        margin-bottom: 0.5rem;
        padding: 0.25rem;
        background: #6c757d;
        color: white;
        border-radius: 4px;
    }

    .rack-grid {
        display: grid;
        gap: 2px;
    }

    .rack-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 2px;
    }

    .rack-cell {
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
    }

    .rack-cell.empty {
        background: #ffffff;
    }

    .rack-cell.occupied {
        background: #28a745;
        color: white;
        font-weight: 500;
    }

    .rack-cell:hover {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .item-preview {
        text-align: center;
        line-height: 1.2;
    }

    .warehouse-legend {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        border: 1px solid #dee2e6;
    }

    .legend-color.empty {
        background: #ffffff;
    }

    .legend-color.occupied {
        background: #28a745;
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

    @media (max-width: 768px) {
        .warehouse-grid {
            grid-template-columns: 1fr;
        }
        
        .rack-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<script>
    // Fungsi-fungsi yang akan dipanggil dari HTML (onclick) harus berada di lingkup global
    // atau diakses melalui window.namaFungsi. Untuk kemudahan, kita akan membuatnya global.

    /**
     * Menampilkan detail rak dan barang di dalamnya.
     * @param {string} position - Posisi rak (misal: "R1-1-1").
     * @param {number|null} itemId - ID barang di rak tersebut, atau null jika kosong.
     */
    window.showRackDetails = function(position, itemId) {
        const rackDetailsContainer = document.getElementById('rackDetailsContainer');
        
        if (itemId && itemId !== 'null') {
            // Fetch item data from backend
            fetch(`/staff/incoming-items/${itemId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const item = result.data;
                        let locationDisplay = item.lokasi_rak_barang ? 
                                              `<span class="badge bg-info">${item.lokasi_rak_barang}</span>` : 
                                              `<span class="badge bg-secondary">Belum ditempatkan</span>`;
                        let inconsistencyWarning = '';

                        // Check for synchronization: if item's stored location differs from clicked rack position
                        if (item.lokasi_rak_barang && item.lokasi_rak_barang !== position) {
                            inconsistencyWarning = `
                                <div class="alert alert-warning alert-sm mt-2" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> Lokasi tersimpan: ${item.lokasi_rak_barang}. Tidak sinkron dengan posisi rak yang diklik.
                                </div>
                            `;
                        } else if (!item.lokasi_rak_barang && item.jumlah_barang > 0) {
                            // If item has no stored location but is in a physical rack
                            inconsistencyWarning = `
                                <div class="alert alert-warning alert-sm mt-2" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> Barang ini belum memiliki lokasi rak tersimpan di database.
                                </div>
                            `;
                        }
                        
                        rackDetailsContainer.innerHTML = `
                            <div class="rack-detail-card">
                                <div class="position-badge mb-3">
                                    <span class="badge bg-primary fs-6">${position}</span>
                                </div>
                                <div class="item-details">
                                    <h6 class="text-primary">Rak ${position.split('-')[0].substring(1)}</h6>
                                    <div class="detail-row">
                                        <strong>Nama Barang:</strong>
                                        <span>${item.nama_barang}</span>
                                    </div>
                                    <div class="detail-row">
                                        <strong>Kategori Barang:</strong>
                                        <span>${item.kategori_barang}</span>
                                    </div>
                                    <div class="detail-row">
                                        <strong>Jumlah Barang:</strong>
                                        <span>${item.jumlah_barang} unit</span>
                                    </div>
                                    <div class="detail-row">
                                        <strong>Tanggal Masuk:</strong>
                                        <span>${new Date(item.tanggal_masuk_barang).toLocaleDateString('id-ID')}</span>
                                    </div>
                                    <div class="detail-row">
                                        <strong>Status:</strong>
                                        <span class="badge ${getStatusBadgeClass(item.status_barang)}">${item.status_barang}</span>
                                    </div>
                                    <div class="detail-row">
                                        <strong>Lokasi Tersimpan:</strong>
                                        <span>${locationDisplay}</span>
                                    </div>
                                    ${inconsistencyWarning}
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-warning w-100 mb-2" onclick="editIncomingItemFromManagement('${item.id}')">
                                        <i class="fas fa-edit"></i> Edit Barang
                                    </button>
                                    <button class="btn btn-sm btn-danger w-100 mb-2" onclick="deleteIncomingItemFromManagement('${item.id}')">
                                        <i class="fas fa-trash"></i> Hapus Barang
                                    </button>
                                    <button class="btn btn-sm btn-info w-100" onclick="showItemHistory('${item.id}')">
                                        <i class="fas fa-history"></i> Lihat Riwayat
                                    </button>
                                    <button class="btn btn-sm btn-success w-100 mt-2" onclick="updateItemLocationToClickedRack('${item.id}', '${position}')">
                                        <i class="fas fa-sync-alt"></i> Sinkronkan Lokasi ke Rak Ini
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        rackDetailsContainer.innerHTML = `<p class="text-danger text-center">Gagal memuat detail barang: ${result.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching item details:', error);
                    rackDetailsContainer.innerHTML = `<p class="text-danger text-center">Terjadi kesalahan jaringan saat memuat detail barang.</p>`;
                });
        } else {
            rackDetailsContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="position-badge mb-3">
                        <span class="badge bg-secondary fs-6">${position}</span>
                    </div>
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Rak Kosong</h6>
                    <p class="text-muted small">Lokasi ini belum digunakan</p>
                    <button class="btn btn-sm btn-success" onclick="assignItemToRack('${position}')">
                        <i class="fas fa-plus"></i> Tempatkan Barang
                    </button>
                </div>
            `;
        }
    }

    /**
     * Helper function to get status badge class.
     * Duplicated from items_content for self-containment if needed, or can be globalized.
     */
    window.getStatusBadgeClass = function(status) {
        switch(status) {
            case 'Banyak': return 'bg-success'; // Changed from 'Tersedia' to 'Banyak' for consistency with items_content
            case 'Sedikit': return 'bg-warning'; // Changed from 'Stok Rendah' to 'Sedikit'
            case 'Habis': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    /**
     * Menampilkan tampilan gudang di layar monitor terpisah.
     */
    window.showWarehouseView = function() {
        const monitorWindow = window.open('{{ route("staff.warehouse_monitor") }}', 'warehouseMonitor', 
            'width=1920,height=1080,fullscreen=yes,scrollbars=no,resizable=no');
        
        if (monitorWindow) {
            monitorWindow.focus();
        } else {
            showAlert('error', 'Pop-up diblokir. Silakan izinkan pop-up untuk fitur ini.');
        }
    }

    /**
     * Memuat ulang data gudang.
     */
    window.refreshWarehouse = function() {
        location.reload();
    }

    /**
     * Menampilkan lokasi rak untuk barang keluar dan menyorotnya di peta gudang.
     * @param {string} rackPosition - Posisi rak.
     */
    window.showRackLocation = function(rackPosition) {
        if (!rackPosition || rackPosition === '-') {
            showAlert('info', 'Lokasi rak tidak tersedia untuk barang ini.');
            return;
        }
        
        const warehouseTab = document.getElementById('warehouse-location-tab');
        if (warehouseTab) {
            warehouseTab.click(); // Activate the warehouse tab
            
            setTimeout(() => {
                const rackCell = document.querySelector(`[data-position="${rackPosition}"]`);
                if (rackCell) {
                    // Highlight the cell
                    rackCell.style.border = '3px solid #ff6b6b';
                    rackCell.style.boxShadow = '0 0 15px rgba(255, 107, 107, 0.5)';
                    rackCell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    const itemId = rackCell.dataset.itemId || null;
                    showRackDetails(rackPosition, itemId); // Show details in the right panel
                    
                    // Remove highlight after a few seconds
                    setTimeout(() => {
                        rackCell.style.border = '';
                        rackCell.style.boxShadow = '';
                    }, 3000);
                } else {
                    showAlert('warning', `Lokasi rak ${rackPosition} tidak ditemukan di peta gudang.`);
                }
            }, 500); // Small delay to allow tab to activate
        } else {
            showAlert('info', 'Tab Lokasi Barang di Gudang tidak ditemukan.');
        }
    }

    /**
     * Menandai pesanan barang keluar sebagai selesai dikemas.
     * @param {number} itemId - ID barang keluar.
     */
    window.markAsCompleted = function(itemId) {
        showCustomConfirm('Apakah barang ini sudah selesai dikemas dan siap dikirim?', () => {
            // Implement AJAX call to update outgoing item status
            showAlert('success', 'Status barang berhasil diperbarui menjadi "Selesai Dikemas"');
            // location.reload(); // Reload to reflect changes
        });
    }

    /**
     * Menetapkan barang ke rak kosong (membuka modal tambah barang masuk).
     * @param {string} position - Posisi rak yang akan diisi.
     */
    window.assignItemToRack = function(position) {
        // Panggil fungsi renderItemCrudForm dari items_content.blade.php
        // Asumsi items_content.blade.php sudah dimuat dan fungsi renderItemCrudForm tersedia secara global.
        if (typeof window.renderItemCrudForm === 'function') {
            window.renderItemCrudForm('incoming', 'add');
            // Pre-fill location if the form has the field
            setTimeout(() => {
                const crudLokasiRak = document.getElementById('crud_lokasi_rak');
                if (crudLokasiRak) {
                    crudLokasiRak.value = position;
                    crudLokasiRak.readOnly = true; // Make location read-only
                }
            }, 100);
        } else {
            showAlert('error', 'Fungsi renderItemCrudForm tidak ditemukan. Pastikan items_content.blade.php dimuat dengan benar.');
        }
    }

    /**
     * Mengedit barang masuk dari halaman manajemen gudang.
     * @param {number} itemId - ID barang masuk.
     */
    window.editIncomingItemFromManagement = function(itemId) {
        // Panggil fungsi editIncomingItem dari items_content.blade.php
        if (typeof window.editIncomingItem === 'function') {
            window.editIncomingItem(itemId);
        } else {
            showAlert('error', 'Fungsi editIncomingItem tidak ditemukan. Pastikan items_content.blade.php dimuat dengan benar.');
        }
    }

    /**
     * Menghapus barang masuk dari halaman manajemen gudang.
     * @param {number} itemId - ID barang masuk.
     */
    window.deleteIncomingItemFromManagement = function(itemId) {
        // Panggil fungsi deleteIncomingItem dari items_content.blade.php
        if (typeof window.deleteIncomingItem === 'function') {
            window.deleteIncomingItem(itemId);
        } else {
            showAlert('error', 'Fungsi deleteIncomingItem tidak ditemukan. Pastikan items_content.blade.php dimuat dengan benar.');
        }
    }

    /**
     * Menampilkan riwayat barang (placeholder).
     * @param {number} itemId - ID barang.
     */
    window.showItemHistory = function(itemId) {
        showAlert('info', 'Fitur riwayat barang akan segera hadir!');
        // Here you would open a modal or navigate to history page
    }

    /**
     * Sinkronkan lokasi rak barang di database dengan posisi rak yang diklik.
     * Ini akan memperbarui `lokasi_rak_barang` di `IncomingItem` di database.
     * @param {number} itemId - ID barang masuk.
     * @param {string} newLocation - Posisi rak yang akan disinkronkan.
     */
    window.updateItemLocationToClickedRack = async function(itemId, newLocation) {
        showCustomConfirm(`Apakah Anda yakin ingin memperbarui lokasi rak barang ini di database menjadi ${newLocation}?`, async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            showAlert('info', `Memperbarui lokasi barang #${itemId} ke ${newLocation}...`);

            try {
                // Fetch current item data to send a complete update request
                const itemResponse = await fetch(`/staff/incoming-items/${itemId}`);
                const itemResult = await itemResponse.json();

                if (!itemResult.success) {
                    showAlert('error', itemResult.message || 'Gagal memuat data barang untuk sinkronisasi lokasi.');
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
                    location.reload(); // Reload to reflect changes
                } else {
                    showAlert('error', data.message || 'Gagal memperbarui lokasi barang.');
                }
            } catch (error) {
                console.error('Error updating item location:', error);
                showAlert('error', 'Terjadi kesalahan jaringan saat memperbarui lokasi barang.');
            }
        });
    };

    // Add some CSS for detail styling (ensure these are not duplicated if already in app.blade.php)
    const additionalStyles = `
        <style>
            .detail-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f1f3f4;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .rack-detail-card {
                background: #f8f9fa;
                padding: 1rem;
                border-radius: 8px;
                border: 1px solid #e9ecef;
            }
            .position-badge {
                text-align: center;
            }
        </style>
    `;
    document.head.insertAdjacentHTML('beforeend', additionalStyles);

    // Search functionality for outgoing items table in this specific blade file
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchOutgoingInput');
        const outgoingTable = document.getElementById('outgoingTable');

        if (searchInput && outgoingTable) {
            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = outgoingTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const nameCell = rows[i].getElementsByTagName('td')[1]; // Nama Pengecer
                    const itemCell = rows[i].getElementsByTagName('td')[3]; // Daftar Barang
                    if (nameCell && itemCell) {
                        const nameValue = nameCell.textContent || nameCell.innerText;
                        const itemValue = itemCell.textContent || itemCell.innerText;
                        if (nameValue.toLowerCase().includes(filter) || 
                            itemValue.toLowerCase().includes(filter)) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        }

        // Auto dismiss alerts (copied from app.blade.php for consistency)
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

        // Handle URL parameter for highlighting
        const urlParams = new URLSearchParams(window.location.search);
        const highlightPosition = urlParams.get('highlight');
        if (highlightPosition) {
            window.showRackLocation(highlightPosition);
        }
    });
</script>
@endsection
