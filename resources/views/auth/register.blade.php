@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">{{ __('Daftar Akun Baru') }}</h4>
                </div>

                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="logo-circle">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="login-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="logo-fallback" style="display: none;">
                                <i class="fas fa-user-plus fa-3x text-success"></i>
                            </div>
                        </div>
                        <h5 class="mt-2 text-muted">Usaha Distributor Keluarga Sehati</h5>
                        <p class="text-muted small">Bergabunglah dengan keluarga besar kami</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name" class="form-label">{{ __('Nama Lengkap') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input id="name" type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" 
                                       required autocomplete="name" autofocus
                                       placeholder="Masukkan nama lengkap Anda"
                                       minlength="2" maxlength="255">
                            </div>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Minimal 2 karakter, maksimal 255 karakter</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">{{ __('Alamat Email') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email"
                                       placeholder="contoh: nama@email.com">
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Gunakan email yang valid dan aktif</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="form-label">{{ __('Kata Sandi') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password"
                                       placeholder="Masukkan kata sandi (min. 8 karakter)"
                                       minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="form-text" id="passwordHint">
                                    Minimal 8 karakter, disarankan menggunakan kombinasi huruf, angka, dan simbol
                                </small>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password-confirm" class="form-label">{{ __('Konfirmasi Kata Sandi') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="password-confirm" type="password" 
                                       class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password"
                                       placeholder="Masukkan ulang kata sandi Anda">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                    <i class="fas fa-eye" id="eyeIconConfirm"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-2"></div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" required>
                                <label class="form-check-label" for="terms">
                                    Saya menyetujui <a href="#" class="text-success" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat dan Ketentuan</a> 
                                    serta <a href="#" class="text-success" data-bs-toggle="modal" data-bs-target="#privacyModal">Kebijakan Privasi</a> <span class="text-danger">*</span>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success w-100 py-2 mb-3" id="registerBtn">
                                <span class="btn-text">{{ __('Daftar Sekarang') }}</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                            
                            <div class="text-center mt-3">
                                <p class="mb-0">Sudah punya akun? 
                                    <a href="{{ route('login') }}" class="text-success fw-bold">Masuk di sini</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Syarat dan Ketentuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Ketentuan Umum</h6>
                <p>Dengan mendaftar dan menggunakan layanan Usaha Distributor Keluarga Sehati, Anda menyetujui untuk mematuhi semua syarat dan ketentuan yang berlaku.</p>
                
                <h6>2. Akun Pengguna</h6>
                <p>Anda bertanggung jawab untuk menjaga keamanan akun dan kata sandi Anda. Segala aktivitas yang terjadi pada akun Anda menjadi tanggung jawab Anda.</p>
                
                <h6>3. Penggunaan Layanan</h6>
                <p>Layanan kami hanya boleh digunakan untuk tujuan yang sah dan sesuai dengan peraturan yang berlaku di Indonesia.</p>
                
                <h6>4. Privasi dan Data</h6>
                <p>Kami menghormati privasi Anda dan berkomitmen untuk melindungi data pribadi sesuai dengan kebijakan privasi kami.</p>
                
                <h6>5. Pembatasan Tanggung Jawab</h6>
                <p>Usaha Distributor Keluarga Sehati tidak bertanggung jawab atas kerugian langsung atau tidak langsung yang mungkin timbul dari penggunaan layanan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="document.getElementById('terms').checked = true">Saya Setuju</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Kebijakan Privasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Informasi yang Kami Kumpulkan</h6>
                <p>Kami mengumpulkan informasi yang Anda berikan secara langsung kepada kami, seperti nama dan email.</p>
                
                <h6>Bagaimana Kami Menggunakan Informasi</h6>
                <p>Informasi yang dikumpulkan digunakan untuk:</p>
                <ul>
                    <li>Menyediakan dan meningkatkan layanan kami</li>
                    <li>Komunikasi dengan Anda</li>
                    <li>Keamanan dan pencegahan penipuan</li>
                    <li>Analisis dan penelitian</li>
                </ul>
                
                <h6>Perlindungan Informasi</h6>
                <p>Kami menggunakan langkah-langkah keamanan yang sesuai untuk melindungi informasi pribadi Anda dari akses, perubahan, pengungkapan, atau penghancuran yang tidak sah.</p>
                
                <h6>Pembagian Informasi</h6>
                <p>Kami tidak akan menjual, memperdagangkan, atau mentransfer informasi pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali dalam keadaan tertentu yang diatur oleh hukum.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .card {
        border-radius: 15px;
        margin-top: 2rem;
        margin-bottom: 2rem;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
    
    .login-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #28a745;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .logo-fallback {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid #28a745;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: rgba(40, 167, 69, 0.1);
    }
    
    .logo-circle {
        margin-bottom: 1rem;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        min-width: 45px;
        justify-content: center;
    }
    
    .form-control {
        border-left: none;
    }
    
    .form-control:focus {
        box-shadow: none;
        border-color: #28a745;
    }
    
    .form-control:focus + .btn-outline-secondary {
        border-color: #28a745;
    }
    
    .btn-success {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        border-radius: 25px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .btn-success:disabled {
        transform: none;
        opacity: 0.8;
    }
    
    .password-strength .progress-bar {
        transition: all 0.3s ease;
    }
    
    .strength-weak { background-color: #dc3545 !important; }
    .strength-medium { background-color: #ffc107 !important; }
    .strength-strong { background-color: #28a745 !important; }
    
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .modal-content {
        border-radius: 15px;
    }
    
    .modal-header {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        border-radius: 15px 15px 0 0;
    }
    
    .btn-outline-secondary {
        border-left: none;
    }
    
    .text-match { color: #28a745; }
    .text-no-match { color: #dc3545; }
    
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }
        
        .card-body {
            padding: 2rem 1.5rem !important;
        }
        
        .login-logo, .logo-fallback {
            width: 60px;
            height: 60px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
    
    // Toggle confirm password visibility
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordConfirm = document.getElementById('password-confirm');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');
    
    togglePasswordConfirm.addEventListener('click', function() {
        const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirm.setAttribute('type', type);
        eyeIconConfirm.classList.toggle('fa-eye');
        eyeIconConfirm.classList.toggle('fa-eye-slash');
    });
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordHint = document.getElementById('passwordHint');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let hint = '';
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        strength = Math.min(strength, 100);
        
        passwordStrength.style.width = strength + '%';
        
        if (strength < 50) {
            passwordStrength.className = 'progress-bar strength-weak';
            hint = 'Kata sandi lemah - tambahkan huruf besar, angka, dan simbol';
        } else if (strength < 75) {
            passwordStrength.className = 'progress-bar strength-medium';
            hint = 'Kata sandi sedang - tambahkan lebih banyak variasi karakter';
        } else {
            passwordStrength.className = 'progress-bar strength-strong';
            hint = 'Kata sandi kuat!';
        }
        
        passwordHint.textContent = hint;
    });
    
    // Password match checker
    const passwordMatchDiv = document.getElementById('passwordMatch');
    
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password-confirm').value;
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                passwordMatchDiv.innerHTML = '<small class="text-match"><i class="fas fa-check"></i> Kata sandi cocok</small>';
            } else {
                passwordMatchDiv.innerHTML = '<small class="text-no-match"><i class="fas fa-times"></i> Kata sandi tidak cocok</small>';
            }
        } else {
            passwordMatchDiv.innerHTML = '';
        }
    }
    
    passwordInput.addEventListener('input', checkPasswordMatch);
    passwordConfirm.addEventListener('input', checkPasswordMatch);
    
    // Form submission with loading state
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const btnText = registerBtn.querySelector('.btn-text');
    const spinner = registerBtn.querySelector('.spinner-border');
    
    registerForm.addEventListener('submit', function() {
        registerBtn.disabled = true;
        btnText.textContent = 'Mendaftar...';
        spinner.classList.remove('d-none');
    });
    
    // Email validation
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
@endsection