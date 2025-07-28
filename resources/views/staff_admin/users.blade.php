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
                        <a class="nav-link" href="{{ route('staff.item.management') }}">
                            <i class="fas fa-cogs"></i>Pengelolaan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('staff.users') }}">
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

        {{-- Main Content untuk Users --}}
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

            {{-- Card Users (Pengecer) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users text-primary"></i> 
                        Daftar Users Pengecer
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">Total: {{ $pengecerUsers->count() }} User</span>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter dan Pencarian --}}
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Cari nama, username, atau email..." id="searchUsersInput">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-muted">
                                <small>Role Admin hanya dapat melihat users dengan role Pengecer</small>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Users Pengecer --}}
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                    <th>No. Telepon</th>
                                    <th>Role</th>
                                    <th>Bergabung</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pengecerUsers as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-store text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $user->full_name }}</strong>
                                                    <small class="text-muted d-block">ID: #{{ $user->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code>{{ $user->username }}</code>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <div class="password-field">
                                                <span class="password-text d-none">{{ $user->plain_password ?? $user->password }}</span>
                                                <span class="password-masked">••••••••</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="togglePassword(this)" title="Tampilkan password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if(!$user->plain_password)
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                        <i class="fas fa-info-circle"></i> Hash
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $user->phone_number ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-store"></i> Pengecer
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Aktif
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                                <h5>Belum ada Users Pengecer</h5>
                                                <p>Saat ini belum ada user yang terdaftar dengan role pengecer.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Informasi tambahan --}}
                    @if($pengecerUsers->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Informasi:</strong> Sebagai admin, Anda dapat melihat semua users dengan role "pengecer". 
                                    Users ini adalah pelanggan/pengecer yang dapat melakukan pemesanan barang melalui sistem. 
                                    Total users pengecer yang terdaftar: <strong>{{ $pengecerUsers->count() }} user</strong>.
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS khusus untuk Users page Staff Admin */
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

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #dee2e6;
    }

    .btn {
        border-radius: 6px;
    }

    .badge {
        font-size: 0.75rem;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.875em;
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

        // Search functionality for users table
        const searchInput = document.getElementById('searchUsersInput');
        const usersTable = document.getElementById('usersTable');

        if (searchInput && usersTable) {
            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = usersTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    // Skip if this is the "no data" row
                    if (rows[i].getElementsByTagName('td').length === 1) {
                        continue;
                    }

                    const nameCell = rows[i].getElementsByTagName('td')[1]; // Kolom Nama
                    const usernameCell = rows[i].getElementsByTagName('td')[2]; // Kolom Username
                    const emailCell = rows[i].getElementsByTagName('td')[3]; // Kolom Email
                    
                    if (nameCell && usernameCell && emailCell) {
                        const nameText = nameCell.textContent || nameCell.innerText;
                        const usernameText = usernameCell.textContent || usernameCell.innerText;
                        const emailText = emailCell.textContent || emailCell.innerText;
                        
                        const searchText = (nameText + ' ' + usernameText + ' ' + emailText).toLowerCase();
                        
                        if (searchText.indexOf(filter) > -1) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        }

        // Function to toggle password visibility
        window.togglePassword = function(button) {
            const passwordField = button.closest('.password-field');
            const passwordText = passwordField.querySelector('.password-text');
            const passwordMasked = passwordField.querySelector('.password-masked');
            const icon = button.querySelector('i');

            if (passwordText.classList.contains('d-none')) {
                // Show password
                passwordText.classList.remove('d-none');
                passwordMasked.classList.add('d-none');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                button.title = 'Sembunyikan password';
            } else {
                // Hide password
                passwordText.classList.add('d-none');
                passwordMasked.classList.remove('d-none');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                button.title = 'Tampilkan password';
            }
        };
    });
</script>
@endsection