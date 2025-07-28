# Dokumentasi Fungsi Kelas dalam Views

## Struktur Direktori Views

```
resources/views/
├── auth/                     # Halaman autentikasi
├── dashboard/               # Dashboard untuk berbagai role
├── emails/                  # Template email
├── exports/                 # Template export/print
├── layouts/                 # Layout template
├── staff_admin/            # Halaman khusus staff admin
└── File-file utama
```

## Detail Fungsi Setiap Kelas View

### 1. **Auth Views** (`resources/views/auth/`)

#### `login.blade.php`
- **Fungsi**: Halaman login untuk sistem UD Keluarga Sehati
- **Komponen**:
  - Form login dengan email dan password
  - Toggle visibility password 
  - Remember me checkbox
  - Link lupa password
  - Validasi error handling
  - Loading state saat submit
  - Responsive design dengan branding
- **Target User**: Semua pengguna sistem (Manager, Staff Admin, Pengecer)

#### `forgot-password.blade.php`
- **Fungsi**: Halaman reset password 
- **Komponen**:
  - Form input email untuk reset
  - Validasi email
  - Link kembali ke login
  - Status notifikasi sukses/error
  - Loading state saat submit
- **Target User**: User yang lupa password

#### `reset-password.blade.php`
- **Fungsi**: Halaman untuk set password baru (tidak ditampilkan dalam sample)
- **Komponen**: Form reset password dengan token validasi

### 2. **Dashboard Views** (`resources/views/dashboard/`)

#### `manager_dashboard.blade.php`
- **Fungsi**: Dashboard utama untuk role Manager
- **Komponen**:
  - Sidebar navigasi dengan menu:
    - Dashboard
    - Laporan Stok Barang
    - Pemesanan Barang  
    - Akun Pegawai
    - User Pengecer
  - Statistik harian:
    - Barang masuk hari ini
    - Barang keluar hari ini
    - Transaksi penjualan
    - Transaksi pembelian
  - Grafik tren penjualan dan pembelian mingguan
  - Chart top 10 barang stok terendah
  - Notifikasi stok habis/rendah dengan detail
  - Auto-refresh notifikasi setiap 5 menit
- **Target User**: Manager dengan akses penuh

#### `staff_admin_dashboard.blade.php`
- **Fungsi**: Dashboard untuk role Staff Admin
- **Komponen**:
  - Sidebar navigasi dengan menu:
    - Dashboard
    - Barang
    - Pengelolaan Barang
    - Users
  - Statistik dasar harian (barang masuk/keluar, transaksi)
  - Grafik mingguan barang masuk dan keluar
  - Akses terbatas dibanding manager
- **Target User**: Staff Admin dengan akses operasional

#### `employee_accounts.blade.php` (tidak ditampilkan)
- **Fungsi**: Manajemen akun pegawai
- **Target User**: Manager

#### `items_index.blade.php` (tidak ditampilkan)  
- **Fungsi**: Daftar dan pencarian barang
- **Target User**: Manager/Staff Admin

#### `order_items.blade.php` (tidak ditampilkan)
- **Fungsi**: Manajemen pemesanan barang
- **Target User**: Manager

#### `pengecer_users.blade.php` (tidak ditampilkan)
- **Fungsi**: Manajemen user pengecer
- **Target User**: Manager

#### `report_stock.blade.php` (tidak ditampilkan)
- **Fungsi**: Laporan stok barang
- **Target User**: Manager

### 3. **Staff Admin Views** (`resources/views/staff_admin/`)

#### `item_management.blade.php`
- **Fungsi**: Pengelolaan barang dan warehouse management
- **Komponen**:
  - **Tab 1: Lokasi Barang di Gudang**
    - Grid visual gudang 8 rak (4x6 per rak)
    - Visualisasi rak kosong vs terisi
    - Detail barang per posisi rak
    - Tombol tampilkan ke layar monitor
    - Deteksi inkonsistensi lokasi
    - Refresh warehouse data
  - **Tab 2: Daftar Pesanan Barang Keluar**
    - Tabel pesanan dengan pencarian
    - Tombol tampilkan lokasi rak
    - Mark as completed untuk packing
    - Tracking status pesanan
- **Target User**: Staff Admin untuk operasional gudang

#### `items.blade.php` (tidak ditampilkan)
- **Fungsi**: Manajemen CRUD barang
- **Target User**: Staff Admin

#### `users.blade.php` (tidak ditampilkan) 
- **Fungsi**: Manajemen user
- **Target User**: Staff Admin

#### `warehouse_monitor.blade.php` (tidak ditampilkan)
- **Fungsi**: Tampilan monitor gudang full-screen
- **Target User**: Display monitor gudang

#### `partials/items_content.blade.php` (tidak ditampilkan)
- **Fungsi**: Komponen partial untuk konten barang
- **Target User**: Include dalam views lain

### 4. **Email Templates** (`resources/views/emails/`)

#### `otp-verification.blade.php` (tidak ditampilkan)
- **Fungsi**: Template email OTP verification
- **Komponen**: Format email dengan kode OTP

#### `reset-password.blade.php` (tidak ditampilkan)
- **Fungsi**: Template email reset password 
- **Komponen**: Format email dengan link reset

### 5. **Export Templates** (`resources/views/exports/`)

#### `stock_report.blade.php` (tidak ditampilkan)
- **Fungsi**: Template export laporan stok
- **Format**: Excel/PDF export

#### `stock_report_print.blade.php` (tidak ditampilkan)
- **Fungsi**: Template print laporan stok
- **Format**: Print-friendly layout

### 6. **Layout Templates** (`resources/views/layouts/`)

#### `app.blade.php` (tidak ditampilkan)
- **Fungsi**: Layout utama untuk halaman aplikasi
- **Komponen**:
  - Header/navigation
  - Sidebar
  - Main content area
  - Footer
  - JavaScript/CSS includes

#### `auth.blade.php` (tidak ditampilkan)
- **Fungsi**: Layout khusus untuk halaman autentikasi
- **Komponen**:
  - Minimal layout
  - Styling khusus auth
  - Background/branding



## Fitur Umum Semua Views

### Security Features
- CSRF token protection
- XSS prevention dengan escaping
- Input validation
- Session-based authentication

### UI/UX Features  
- Responsive design (Bootstrap)
- Loading states
- Error handling dan notifikasi
- Smooth animations
- Auto-dismiss alerts
- Search dan filtering
- Pagination

### Technical Features
- Chart.js untuk visualisasi data
- AJAX untuk real-time updates
- Modal confirmations
- Progressive enhancement
- Accessibility support

## Arsitektur dan Pola

### Role-Based Access
- **Manager**: Akses penuh (dashboard, laporan, manajemen)
- **Staff Admin**: Akses operasional (barang, gudang, user)
- **Pengecer**: Akses terbatas (melalui API)

### Data Flow
1. Controller mengambil data dari Model
2. Data dikirim ke View melalui compact/with
3. Blade template merender UI dengan data
4. JavaScript menangani interaktivity
5. AJAX untuk real-time updates

### Component Structure
- Layouts untuk konsistensi UI
- Partials untuk reusable components  
- Includes untuk shared functionality
- Sections untuk content injection

Dokumentasi ini memberikan overview lengkap tentang fungsi setiap view dalam sistem UD Keluarga Sehati, mulai dari autentikasi hingga manajemen warehouse yang kompleks.