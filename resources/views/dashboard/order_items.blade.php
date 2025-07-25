@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
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
                        <a class="nav-link" href="{{ route('report.stock') }}">
                            <i class="fas fa-boxes"></i>Laporan Stok Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('order.items') }}">
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

        {{-- Main Content untuk Pemesanan Barang --}}
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

            {{-- Card Daftar Mitra Produsen --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs" id="producerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="add-producer-tab" data-bs-toggle="tab" data-bs-target="#add-producer" type="button" role="tab" aria-controls="add-producer" aria-selected="true">
                                Tambah Mitra Produsen
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="return-history-tab" data-bs-toggle="tab" data-bs-target="#return-history" type="button" role="tab" aria-controls="return-history" aria-selected="false">
                                Riwayat Pengembalian Barang
                            </button>
                        </li>
                    </ul>
                    <h5 class="mb-0 ms-auto">Daftar Mitra Produsen</h5>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="producerTabsContent">
                        <div class="tab-pane fade show active" id="add-producer" role="tabpanel" aria-labelledby="add-producer-tab">
                            {{-- Filter dan Pencarian --}}
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#producerModal">
                                        <i class="fas fa-plus"></i> Tambah Mitra Produsen
                                    </button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Mencari" id="searchProducerInput">
                                        <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Daftar Mitra Produsen --}}
                            <div class="table-responsive">
                                <table class="table table-hover" id="producerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Produsen/Supplier</th>
                                            <th>No. Telepon/WhatsApp</th>
                                            <th>Alamat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($producers as $producer)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $producer->nama_produsen_supplier }}</td>
                                                <td>{{ $producer->kontak_whatsapp ?? $producer->no_telp ?? '-' }}</td>
                                                <td>{{ $producer->alamat ?? '-' }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        @if($producer->kontak_whatsapp || $producer->no_telp)
                                                        <a href="https://wa.me/{{ $producer->kontak_whatsapp ?? $producer->no_telp }}" target="_blank" class="btn btn-success btn-sm">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                        @endif
                                                        <button type="button" class="btn btn-warning btn-sm" onclick="editProducer({{ $producer->id }}, {{ json_encode($producer->nama_produsen_supplier) }}, {{ json_encode($producer->kontak_whatsapp) }}, {{ json_encode($producer->alamat) }}, {{ json_encode($producer->no_telp ?? '') }}, {{ json_encode($producer->email ?? '') }}, {{ json_encode($producer->catatan ?? '') }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteProducer({{ $producer->id }}, {{ json_encode($producer->nama_produsen_supplier) }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data mitra produsen.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- Paginasi (placeholder) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga {{ $producers->count() }} dari {{ $producers->count() }} tabel</small>
                                <div>
                                    <button class="btn btn-sm btn-light">Sebelumnya</button>
                                    <button class="btn btn-sm btn-light">Berikutnya</button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="return-history" role="tabpanel" aria-labelledby="return-history-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori Barang</th>
                                            <th>Jumlah</th>
                                            <th>Nama Produsen</th>
                                            <th>Alasan Pengembalian</th>
                                            <th>Tanggal Pengembalian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($returnedItems as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->kategori_barang }}</td>
                                                <td>{{ $item->jumlah_barang }}</td>
                                                <td>{{ $item->nama_produsen ?? '-' }}</td>
                                                <td>{{ $item->alasan_pengembalian }}</td>
                                                <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data barang yang dikembalikan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal untuk Create/Edit Mitra Produsen --}}
<div class="modal fade" id="producerModal" tabindex="-1" aria-labelledby="producerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="producerModalLabel">Tambah Mitra Produsen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="producerForm" method="POST" action="/producers">
                @csrf
                <input type="hidden" id="producerMethod" name="_method" value="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_produsen_supplier" class="form-label">Nama Produsen/Supplier <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_produsen_supplier" name="nama_produsen_supplier" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kontak_whatsapp" class="form-label">Kontak WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kontak_whatsapp" name="kontak_whatsapp" placeholder="Contoh: 628123456789" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_telp" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telp" name="no_telp">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus mitra produsen <strong id="deleteProducerName"></strong>?</p>
                <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Tambahan CSS khusus untuk Pemesanan Barang */
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

        // Search functionality for producers table
        const searchInput = document.getElementById('searchProducerInput');
        const producerTable = document.getElementById('producerTable');

        if (searchInput && producerTable) {
            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = producerTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const nameCell = rows[i].getElementsByTagName('td')[1]; // Kolom Nama Produsen/Supplier
                    if (nameCell) {
                        const textValue = nameCell.textContent || nameCell.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        }

        // Reset modal saat ditutup
        document.getElementById('producerModal').addEventListener('hidden.bs.modal', function () {
            resetModal();
        });
    });

    // Fungsi untuk reset modal
    function resetModal() {
        document.getElementById('producerForm').action = '/producers';
        document.getElementById('producerMethod').value = 'POST';
        document.getElementById('producerModalLabel').textContent = 'Tambah Mitra Produsen';
        document.getElementById('submitBtn').textContent = 'Simpan';
        document.getElementById('producerForm').reset();
    }

    // Fungsi untuk edit producer
    function editProducer(id, nama, whatsapp, alamat, noTelp, email, catatan) {
        document.getElementById('producerForm').action = '/producers/' + id;
        document.getElementById('producerMethod').value = 'PUT';
        document.getElementById('producerModalLabel').textContent = 'Edit Mitra Produsen';
        document.getElementById('submitBtn').textContent = 'Update';
        
        // Set values dengan null check
        document.getElementById('nama_produsen_supplier').value = nama || '';
        document.getElementById('kontak_whatsapp').value = whatsapp || '';
        document.getElementById('alamat').value = alamat || '';
        document.getElementById('no_telp').value = noTelp || '';
        document.getElementById('email').value = email || '';
        document.getElementById('catatan').value = catatan || '';
        
        new bootstrap.Modal(document.getElementById('producerModal')).show();
    }

    // Fungsi untuk delete producer
    function deleteProducer(id, nama) {
        document.getElementById('deleteProducerName').textContent = nama;
        document.getElementById('deleteForm').action = '/producers/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endsection
