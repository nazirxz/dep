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
                        <a class="nav-link" href="{{ route('order.items') }}">
                            <i class="fas fa-shopping-cart"></i>Pemesanan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('employee.accounts') }}">
                            <i class="fas fa-users-cog"></i>Akun Pegawai
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pengecer.users') }}">
                            <i class="fas fa-store"></i>User Pengecer
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

        {{-- Main Content untuk Akun Pegawai --}}
        <div class="col-md-10 offset-md-2 main-content">
            <div class="d-flex justify-content-end align-items-center mb-4 mt-3">
                <span class="text-muted me-3"><i class="fas fa-user"></i> {{ Auth::user()->role === 'manager' ? 'Manajer' : 'Admin' }}</span>
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

            <div class="row">
                {{-- Form Tambah Akun Pegawai --}}
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Tambah Akun Pegawai</h5>
                        </div>
                        <div class="card-body">
                            <form id="addEmployeeForm" method="POST" action="{{ route('employee.accounts.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required value="{{ old('full_name') }}">
                                    <div class="text-danger mt-1" id="full_name_error"></div> {{-- Error display --}}
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required value="{{ old('username') }}">
                                    <div class="text-danger mt-1" id="username_error"></div> {{-- Error display --}}
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="{{ old('email') }}">
                                    <div class="text-danger mt-1" id="email_error"></div> {{-- Error display --}}
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Kata Sandi *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="text-danger mt-1" id="password_error"></div> {{-- Error display --}}
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi *</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Peran Pegawai *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Pilih Peran</option>
                                        @foreach($roles as $roleOption)
                                            <option value="{{ $roleOption }}" {{ old('role') == $roleOption ? 'selected' : '' }}>
                                                {{ ucfirst($roleOption) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger mt-1" id="role_error"></div> {{-- Error display --}}
                                </div>
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                                    <div class="text-danger mt-1" id="phone_number_error"></div> {{-- Error display --}}
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Tambah Akun
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tabel Data Akun Pegawai --}}
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Akun Pegawai</h5>
                            <div class="input-group w-auto flex-grow-0">
                                <input type="text" class="form-control" placeholder="Mencari" id="searchEmployeeInput">
                                <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="employeeTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Akun</th>
                                            <th>Username</th>
                                            <th>Email Akun</th>
                                            <th>Peran Akun</th>
                                            <th>Status Akun</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employeeAccounts as $account)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $account->full_name }}</td>
                                                <td>{{ $account->username }}</td>
                                                <td>{{ $account->email }}</td>
                                                <td>
                                                    <span class="badge {{ $account->role === 'manager' ? 'bg-primary' : ($account->role === 'sales' ? 'bg-info' : 'bg-secondary') }}">
                                                        {{ ucfirst($account->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Aktif</span> {{-- Status dummy --}}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning me-1" onclick="editEmployee({{ $account->id }})">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-danger me-1" onclick="deleteEmployee({{ $account->id }})">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Tidak ada akun pegawai yang terdaftar.</p>
                                                    {{-- Tombol ini tidak lagi memicu tab, hanya sebagai visual --}}
                                                    <button class="btn btn-primary" onclick="showAlert('info', 'Silakan isi formulir di samping untuk menambah akun baru.')">
                                                        <i class="fas fa-user-plus"></i> Tambah Akun Baru
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- Paginasi (placeholder) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small>Menampilkan 1 hingga {{ $employeeAccounts->count() }} dari {{ $employeeAccounts->count() }} tabel</small>
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

{{-- Modal Edit Akun Pegawai --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Akun Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    @csrf
                    @method('PUT') {{-- Method spoofing for PUT request --}}
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        <div class="text-danger mt-1" id="edit_full_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                        <div class="text-danger mt-1" id="edit_username_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                        <div class="text-danger mt-1" id="edit_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Kata Sandi Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <div class="text-danger mt-1" id="edit_password_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Peran Pegawai *</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="">Pilih Peran</option>
                            @foreach($roles as $roleOption)
                                <option value="{{ $roleOption }}">{{ ucfirst($roleOption) }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" id="edit_role_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone_number" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                        <div class="text-danger mt-1" id="edit_phone_number_error"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS yang sama seperti sebelumnya */
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

        // Search functionality for employee table
        const searchEmployeeInput = document.getElementById('searchEmployeeInput');
        const employeeTable = document.getElementById('employeeTable');

        if (searchEmployeeInput && employeeTable) {
            searchEmployeeInput.addEventListener('keyup', function() {
                const filter = searchEmployeeInput.value.toLowerCase();
                const rows = employeeTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const fullNameCell = rows[i].getElementsByTagName('td')[1]; // Nama Akun
                    const usernameCell = rows[i].getElementsByTagName('td')[2]; // Username
                    const emailCell = rows[i].getElementsByTagName('td')[3]; // Email Akun
                    
                    if (fullNameCell && usernameCell && emailCell) {
                        const fullNameText = fullNameCell.textContent || fullNameCell.innerText;
                        const usernameText = usernameCell.textContent || usernameCell.innerText;
                        const emailText = emailCell.textContent || emailCell.innerText;

                        if (fullNameText.toLowerCase().includes(filter) || // Use includes for broader search
                            usernameText.toLowerCase().includes(filter) ||
                            emailText.toLowerCase().includes(filter)) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        }

        // Handle Add Employee Form Submission (using Fetch API for AJAX)
        const addEmployeeForm = document.getElementById('addEmployeeForm');
        if (addEmployeeForm) {
            addEmployeeForm.addEventListener('submit', async function(event) {
                event.preventDefault(); // Prevent default form submission
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn.innerHTML;

                // Clear previous errors
                document.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Memproses...';

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Handle validation errors or other server errors
                        let errorMessage = data.message || 'Terjadi kesalahan.';
                        if (data.errors) {
                            for (const key in data.errors) {
                                const errorElement = document.getElementById(key + '_error');
                                if (errorElement) {
                                    errorElement.textContent = data.errors[key][0];
                                } else {
                                    // Fallback for general errors if specific element not found
                                    errorMessage += `\n- ${data.errors[key][0]}`;
                                }
                            }
                        }
                        showAlert('error', errorMessage);
                    } else {
                        // Success
                        showAlert('success', data.message);
                        addEmployeeForm.reset(); // Clear form fields
                        location.reload(); // Reload page to show new account
                    }
                } catch (error) {
                    console.error('Error adding employee:', error);
                    showAlert('error', 'Terjadi kesalahan jaringan atau server.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        }

        // Function to show edit modal and populate data
        window.editEmployee = async function(userId) {
            const editEmployeeModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
            const form = document.getElementById('editEmployeeForm');
            
            // Clear previous errors
            form.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

            try {
                const response = await fetch(`/employee/accounts/${userId}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    const user = data.data;
                    document.getElementById('edit_user_id').value = user.id;
                    document.getElementById('edit_full_name').value = user.full_name;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_phone_number').value = user.phone_number || '';
                    document.getElementById('edit_password').value = ''; // Clear password fields
                    document.getElementById('edit_password_confirmation').value = ''; // Clear password fields
                    editEmployeeModal.show();
                } else {
                    showAlert('error', data.message || 'Gagal memuat data akun.');
                }
            } catch (error) {
                console.error('Error fetching employee data for edit:', error);
                showAlert('error', 'Terjadi kesalahan jaringan saat memuat data akun.');
            }
        };

        // Handle Edit Employee Form Submission (using Fetch API for AJAX)
        const editEmployeeForm = document.getElementById('editEmployeeForm');
        if (editEmployeeForm) {
            editEmployeeForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                const userId = document.getElementById('edit_user_id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn.innerHTML;

                // Clear previous errors
                this.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Memproses...';

                try {
                    const response = await fetch(`/employee/accounts/${userId}`, {
                        method: 'POST', // Use POST for Laravel's PUT spoofing
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        let errorMessage = data.message || 'Terjadi kesalahan.';
                        if (data.errors) {
                            for (const key in data.errors) {
                                const errorElement = document.getElementById('edit_' + key + '_error');
                                if (errorElement) {
                                    errorElement.textContent = data.errors[key][0];
                                } else {
                                    errorMessage += `\n- ${data.errors[key][0]}`;
                                }
                            }
                        }
                        showAlert('error', errorMessage);
                    } else {
                        showAlert('success', data.message);
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
                        if (editModal) editModal.hide();
                        location.reload(); // Reload page to show updated account
                    }
                } catch (error) {
                    console.error('Error updating employee:', error);
                    showAlert('error', 'Terjadi kesalahan jaringan atau server.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        }

        // Function to show delete confirmation modal
        window.deleteEmployee = function(userId) {
            const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            
            // Set data-id on the button to pass userId
            confirmDeleteBtn.setAttribute('data-user-id', userId);

            // Clear previous event listener
            confirmDeleteBtn.onclick = null; 
            
            // Attach new event listener
            confirmDeleteBtn.onclick = async function() {
                const idToDelete = this.getAttribute('data-user-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                showAlert('info', 'Menghapus akun...');
                deleteConfirmModal.hide(); // Hide confirmation modal

                try {
                    const response = await fetch(`/employee/accounts/${idToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                         let errorText = data.message || `HTTP error! status: ${response.status}`;
                         showAlert('error', errorText);
                    } else {
                        showAlert('success', data.message);
                        location.reload(); // Reload page to reflect changes
                    }
                } catch (error) {
                    console.error('Error deleting employee:', error);
                    showAlert('error', 'Terjadi kesalahan jaringan atau server.');
                }
            };
            deleteConfirmModal.show();
        };

        // Helper function for custom alerts (if not already global)
        function showAlert(type, message) {
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
    });
</script>
@endsection
