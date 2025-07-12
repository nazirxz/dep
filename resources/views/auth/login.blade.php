@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Left Side - Branding -->
            <div class="auth-brand-side">
                <div class="brand-content">
                    <div class="brand-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Keluarga Sehati" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="logo-fallback" style="display: none;">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                    <h2 class="brand-title">Usaha Distributor</h2>
                    <h3 class="brand-subtitle">Keluarga Sehati</h3>
                    <p class="brand-description">
                        Solusi terpercaya untuk kebutuhan distribusi Anda. 
                        Bergabunglah dengan keluarga besar kami dan rasakan kemudahan berbisnis.
                    </p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Sistem manajemen terintegrasi</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Laporan real-time</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Support 24/7</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="auth-form-side">
                <div class="form-content">
                    <div class="form-header">
                        <h4 class="form-title">Selamat Datang Kembali!</h4>
                        <p class="form-subtitle">Silakan masuk ke akun Anda untuk melanjutkan</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger custom-alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="auth-form" id="loginForm">
                        @csrf

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Alamat Email
                            </label>
                            <div class="input-wrapper">
                                <input id="email" type="email" 
                                       class="form-input @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email" autofocus
                                       placeholder="Masukkan email Anda">
                                <div class="input-focus-line"></div>
                            </div>
                            @error('email')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Kata Sandi
                            </label>
                            <div class="input-wrapper">
                                <input id="password" type="password" 
                                       class="form-input @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password"
                                       placeholder="Masukkan kata sandi Anda">
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                                <div class="input-focus-line"></div>
                            </div>
                            @error('password')
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" name="remember" id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">Ingat saya</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-password">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn-primary btn-login" id="loginBtn">
                            <span class="btn-text">Masuk</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                                Memproses...
                            </span>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Auth Container */
.auth-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    position: relative;
}

.auth-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="white" stop-opacity="0.1"/><stop offset="100%" stop-color="white" stop-opacity="0"/></radialGradient></defs><circle cx="20" cy="20" r="2" fill="url(%23a)"/><circle cx="80" cy="40" r="1.5" fill="url(%23a)"/><circle cx="40" cy="80" r="1" fill="url(%23a)"/><circle cx="90" cy="80" r="1.5" fill="url(%23a)"/><circle cx="10" cy="60" r="1" fill="url(%23a)"/></svg>') repeat;
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.auth-wrapper {
    max-width: 1200px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 600px;
}

/* Brand Side */
.auth-brand-side {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 3rem;
    display: flex;
    align-items: center;
    position: relative;
}

.auth-brand-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path d="M0,100 Q50,50 100,100 T200,100 L200,200 L0,200 Z" fill="rgba(255,255,255,0.05)"/></svg>') no-repeat center center;
    background-size: cover;
}

.brand-content {
    position: relative;
    z-index: 1;
}

.brand-logo {
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-logo img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.logo-fallback {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.brand-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-align: center;
}

.brand-subtitle {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #3498db;
}

.brand-description {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    text-align: center;
    opacity: 0.9;
}

.brand-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
}

.feature-item i {
    color: #3498db;
    font-size: 1.1rem;
}

/* Form Side */
.auth-form-side {
    padding: 3rem;
    display: flex;
    align-items: center;
}

.form-content {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #7f8c8d;
    font-size: 1rem;
    margin-bottom: 0;
}

/* Form Styles */
.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-label i {
    color: #3498db;
    font-size: 0.9rem;
}

.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 1rem 1rem 1rem 0;
    border: none;
    border-bottom: 2px solid #e0e0e0;
    background: transparent;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-bottom-color: #3498db;
}

.form-input::placeholder {
    color: #bdc3c7;
}

.input-focus-line {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    transition: width 0.3s ease;
}

.form-input:focus + .password-toggle + .input-focus-line,
.form-input:focus + .input-focus-line {
    width: 100%;
}

.password-toggle {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #3498db;
}

.error-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #e74c3c;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #3498db;
}

.remember-me label {
    font-size: 0.9rem;
    color: #7f8c8d;
    cursor: pointer;
}

.forgot-password {
    color: #3498db;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #2980b9;
    text-decoration: underline;
}

.btn-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.auth-divider {
    text-align: center;
    margin: 2rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

.auth-divider span {
    background: white;
    padding: 0 1rem;
    color: #7f8c8d;
    font-size: 0.9rem;
    position: relative;
}

.auth-switch {
    text-align: center;
}

.auth-switch p {
    color: #7f8c8d;
    margin: 0;
}

.switch-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.switch-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

.custom-alert {
    border-radius: 10px;
    border: none;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.custom-alert i {
    margin-top: 2px;
}

.custom-alert ul {
    margin: 0;
    padding-left: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .auth-card {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    
    .auth-brand-side {
        padding: 2rem;
        text-align: center;
    }
    
    .brand-title {
        font-size: 1.5rem;
    }
    
    .brand-subtitle {
        font-size: 1.25rem;
    }
    
    .brand-description {
        font-size: 0.9rem;
    }
    
    .auth-form-side {
        padding: 2rem;
    }
    
    .form-title {
        font-size: 1.5rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-brand-side,
    .auth-form-side {
        padding: 1.5rem;
    }
    
    .brand-logo {
        width: 60px;
        height: 60px;
    }
    
    .logo-fallback {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (togglePassword && password && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form submission loading state
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoading = loginBtn.querySelector('.btn-loading');
    
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function() {
            loginBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
        });
    }
    
    // Input focus animations
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>
@endsection