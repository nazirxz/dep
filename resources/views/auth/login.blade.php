@extends('layouts.auth')

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
    height: 100vh;
    max-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    position: relative;
    overflow: hidden;
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
    max-height: calc(100vh - 2rem);
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 500px;
    max-height: calc(100vh - 4rem);
    width: 100%;
}

/* Brand Side */
.auth-brand-side {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 2rem 1.5rem;
    display: flex;
    align-items: center;
    position: relative;
    overflow-y: auto;
}

.auth-brand-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="b" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="white" stop-opacity="0.1"/><stop offset="100%" stop-color="white" stop-opacity="0"/></radialGradient></defs><circle cx="10" cy="10" r="1" fill="url(%23b)"/><circle cx="90" cy="20" r="1.5" fill="url(%23b)"/><circle cx="20" cy="90" r="1" fill="url(%23b)"/><circle cx="80" cy="80" r="1.2" fill="url(%23b)"/></svg>') repeat;
    opacity: 0.3;
}

.brand-content {
    position: relative;
    z-index: 1;
    text-align: center;
}

.brand-logo {
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
}

.brand-logo img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.logo-fallback {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.brand-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.brand-subtitle {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.brand-description {
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.brand-features {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
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
    padding: 2rem 1.5rem;
    display: flex;
    align-items: center;
    overflow-y: auto;
}

.form-content {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.form-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* Form Styles */
.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.2rem;
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
    margin-bottom: 1.5rem;
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
    padding: 0.8rem 2rem;
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
    .auth-container {
        padding: 0.5rem;
    }
    
    .auth-card {
        grid-template-columns: 1fr;
        min-height: calc(100vh - 1rem);
        max-height: calc(100vh - 1rem);
    }
    
    .auth-brand-side {
        padding: 1.5rem 1rem;
        text-align: center;
        min-height: 200px;
    }
    
    .brand-title {
        font-size: 1.3rem;
    }
    
    .brand-subtitle {
        font-size: 1.1rem;
    }
    
    .brand-description {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .auth-form-side {
        padding: 1.5rem 1rem;
    }
    
    .form-title {
        font-size: 1.3rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .auth-container {
        padding: 0.25rem;
        height: 100vh;
    }
    
    .auth-wrapper {
        max-height: calc(100vh - 0.5rem);
    }
    
    .auth-card {
        min-height: calc(100vh - 0.5rem);
        max-height: calc(100vh - 0.5rem);
        border-radius: 10px;
    }
    
    .auth-brand-side,
    .auth-form-side {
        padding: 1rem;
    }
    
    .brand-logo img,
    .logo-fallback {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .brand-features {
        gap: 0.5rem;
    }
    
    .feature-item {
        font-size: 0.85rem;
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