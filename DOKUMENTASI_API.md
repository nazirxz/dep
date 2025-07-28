# API Documentation - UD Keluarga Sehati Backend

## Overview

Sistem API untuk UD Keluarga Sehati yang menyediakan endpoint lengkap untuk manajemen inventori, pesanan, dan autentikasi. API ini dibangun menggunakan Laravel dengan autentikasi Sanctum.

## Base URL
```
Production: https://udkeluargasehati.com/api
Development: http://localhost:8000/api
```

## Authentication

API menggunakan Laravel Sanctum untuk autentikasi token-based.

### Headers Required
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your-token} // Untuk endpoint yang memerlukan autentikasi
```

## Response Format

Semua response menggunakan format JSON standar:

```json
{
    "status": "success|error",
    "message": "Pesan deskriptif",
    "data": {}, // Data response
    "pagination": {} // Untuk endpoint dengan pagination
}
```

---

# 📋 Daftar Endpoint API

## 🔐 Authentication Endpoints

### Autentikasi Dasar
| Endpoint | Method | Auth | Deskripsi |
|----------|--------|------|-----------|
| `/register` | POST | ❌ | Registrasi user baru dengan role default "Pengecer" |
| `/login` | POST | ❌ | Login user dan mendapatkan token autentikasi |
| `/logout` | POST | ✅ | Logout user dan hapus token |
| `/user` | GET | ✅ | Mendapatkan profil user yang sedang login |

### Email Verification
| Endpoint | Method | Auth | Rate Limit | Deskripsi |
|----------|--------|------|------------|-----------|
| `/send-otp` | POST | ❌ | 3/min | Kirim kode OTP 6 digit ke email |
| `/verify-otp` | POST | ❌ | 5/min | Verifikasi kode OTP dan aktivasi akun |
| `/resend-otp` | POST | ❌ | 2/min | Kirim ulang kode OTP |

### Password Reset
| Endpoint | Method | Auth | Rate Limit | Deskripsi |
|----------|--------|------|------------|-----------|
| `/forgot-password` | POST | ❌ | 3/min | Kirim link reset password ke email |
| `/verify-reset-token` | POST | ❌ | 10/min | Verifikasi token reset password |
| `/reset-password` | POST | ❌ | 5/min | Reset password dengan token valid |

---

## 📦 Products Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/products` | GET | Daftar semua produk dengan pagination dan filter |
| `/products/categories` | GET | Daftar semua kategori produk |
| `/products/category/{kategori}` | GET | Produk berdasarkan kategori tertentu |
| `/products/search` | GET | Pencarian produk dengan keyword |
| `/products/{id}` | GET | Detail produk berdasarkan ID |

**Fitur:**
- Filter berdasarkan kategori dan stok
- Pencarian multi-field (nama, kategori)
- Sorting dan pagination
- Hanya menampilkan produk dengan stok > 0
- URL gambar produk otomatis

---

## 📊 Dashboard Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/dashboard/stats` | GET | Statistik harian dashboard (barang masuk/keluar, transaksi) |
| `/dashboard/low-stock` | GET | Daftar barang dengan stok rendah |
| `/dashboard/notifications` | GET | Notifikasi stok untuk manager (stok habis/rendah) |
| `/dashboard/weekly-stats` | GET | Statistik mingguan barang masuk dan keluar |
| `/dashboard/monthly-stats` | GET | Statistik bulanan lengkap |
| `/dashboard/complete` | GET | Data dashboard lengkap dalam satu request |

**Fitur:**
- Real-time stock monitoring
- Notifikasi stok critical/rendah
- Analytics mingguan dan bulanan
- Chart data untuk visualisasi
- Summary statistik harian

---

## 📥 Incoming Items Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/incoming-items` | GET | Daftar barang masuk dengan filter lengkap |
| `/incoming-items/categories` | GET | Kategori barang masuk |
| `/incoming-items/category/{kategori}` | GET | Barang masuk per kategori |
| `/incoming-items/search` | GET | Pencarian barang masuk |
| `/incoming-items/weekly-incoming-stats` | GET | Statistik mingguan barang masuk |
| `/incoming-items/{id}` | GET | Detail barang masuk spesifik |

**Fitur:**
- Filter berdasarkan status stok (all/available/empty/low_stock)
- Informasi produsen dan lokasi rak
- Estimasi nilai inventori
- Summary per kategori
- Analytics trend mingguan

---

## 📤 Outgoing Items Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/outgoing-items` | GET | Daftar barang keluar dengan tracking distribusi |
| `/outgoing-items/categories` | GET | Kategori barang keluar |
| `/outgoing-items/category/{kategori}` | GET | Barang keluar per kategori |
| `/outgoing-items/search` | GET | Pencarian barang keluar |
| `/outgoing-items/weekly-sales-stats` | GET | Statistik penjualan mingguan |
| `/outgoing-items/{id}` | GET | Detail barang keluar dan transaksi |

**Fitur:**
- Tracking tujuan distribusi
- Informasi nota transaksi
- Analytics penjualan
- Top selling products
- Performance per produsen

---

## 🔄 Return Items Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### Read Operations
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/return-items` | GET | Daftar semua return dengan filter lengkap |
| `/return-items/returnable-items` | GET | Barang yang bisa di-return oleh user |
| `/return-items/categories` | GET | Kategori barang return |
| `/return-items/category/{kategori}` | GET | Return per kategori |
| `/return-items/search` | GET | Pencarian return dengan filter alasan |
| `/return-items/weekly-return-stats` | GET | Statistik return mingguan |
| `/return-items/{id}` | GET | Detail return spesifik |

### Write Operations
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/return-items` | POST | Buat return baru dengan upload bukti foto |
| `/return-items/{id}` | PUT | Update data return |
| `/return-items/{id}` | DELETE | Hapus return |

**Fitur:**
- Upload foto bukti return (max 2MB)
- Kategorisasi alasan return (damaged/incorrect/expired/quality_issue/other)
- Validasi terhadap order yang eligible
- Tracking status return
- Analytics alasan return

---

## 🛒 Orders Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### User Operations
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/orders` | GET | Daftar pesanan user dengan filter status |
| `/orders` | POST | Buat pesanan baru dengan voucher dan shipping |
| `/orders/stats` | GET | Statistik pesanan user |
| `/orders/{id}` | GET | Detail pesanan spesifik |

### Admin Operations (Role: Admin)
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/orders/admin` | GET | Semua pesanan (admin only) |
| `/orders/user/{userId}` | GET | Pesanan berdasarkan user ID |
| `/orders/{id}/finished-packing` | PUT | Mark pesanan selesai dikemas |

### Sales Operations
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/orders/sales` | GET | Pesanan siap kirim (sales access) |
| `/orders/debug` | GET | Debug semua pesanan dan status |
| `/orders/test` | GET | Test endpoint sederhana |
| `/orders/{id}/shipping-status` | PUT | Update status pengiriman |

**Fitur:**
- Sistem voucher dengan validasi otomatis
- Multiple payment dan shipping status
- Role-based access control
- Order tracking dan history
- Integration dengan inventory

---

## 🎫 Vouchers Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/vouchers` | GET | Daftar voucher yang tersedia dan aktif |
| `/vouchers/validate` | POST | Validasi kode voucher dan hitung diskon |

**Fitur:**
- Validasi minimum purchase
- Perhitungan diskon otomatis (percentage/fixed)
- Maximum discount limit
- Usage limit tracking
- Periode validity check

---

## 🚚 Shipping Methods Endpoints

**Auth Required:** ❌ Public endpoint

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/shipping-methods` | GET | Daftar metode pengiriman aktif |

**Fitur:**
- Estimasi waktu pengiriman
- Pricing information
- Service level description
- Sorted by price

---

# 📖 Detail Request & Response

## 🔐 Authentication Endpoints

### 1. Register User
```
POST /api/register
```

**Deskripsi:** Registrasi user baru dengan role default "Pengecer". Setelah registrasi berhasil, sistem akan mengirim OTP ke email untuk verifikasi.

**Request Body:**
```json
{
    "full_name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Validation Rules:**
- `full_name`: required, string, max:255
- `username`: required, string, unique, max:255
- `email`: required, email, unique
- `password`: required, string, min:8

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Registrasi berhasil. Silakan cek email untuk verifikasi.",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "username": "johndoe",
            "email": "john@example.com",
            "email_verified": false,
            "role": "Pengecer",
            "created_at": "2025-07-27T10:00:00.000000Z"
        }
    }
}
```

**Response Error (422):**
```json
{
    "status": "error",
    "message": "Data yang diberikan tidak valid",
    "errors": {
        "email": ["Email sudah terdaftar"],
        "username": ["Username sudah digunakan"]
    }
}
```

### 2. Login User
```
POST /api/login
```

**Deskripsi:** Login user dan mendapatkan token autentikasi. User harus sudah terverifikasi email.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "role": "Pengecer",
            "email_verified": true
        },
        "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yzc567"
    }
}
```

**Response Error (401):**
```json
{
    "status": "error",
    "message": "Email atau password tidak valid"
}
```

**Response Error - Email Not Verified (403):**
```json
{
    "status": "error",
    "message": "Email belum diverifikasi. Silakan verifikasi email terlebih dahulu."
}
```

### 3. Logout User
```
POST /api/logout
```
**Auth Required:** ✅

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Logout berhasil"
}
```

### 4. Get User Profile
```
GET /api/user
```
**Auth Required:** ✅

**Response Success (200):**
```json
{
    "id": 1,
    "full_name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "email_verified": true,
    "role": "Pengecer",
    "created_at": "2025-07-27T10:00:00.000000Z",
    "updated_at": "2025-07-27T10:30:00.000000Z"
}
```

---

## 📧 Email Verification Endpoints

### 1. Send OTP
```
POST /api/send-otp
```

**Rate Limit:** 3 requests per minute

**Deskripsi:** Mengirim kode OTP 6 digit ke email user untuk verifikasi.

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Kode OTP telah dikirim ke email Anda"
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Email tidak ditemukan"
}
```

### 2. Verify OTP
```
POST /api/verify-otp
```

**Rate Limit:** 5 requests per minute

**Deskripsi:** Verifikasi kode OTP dan aktivasi akun user.

**Request Body:**
```json
{
    "email": "john@example.com",
    "otp": "123456"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Email berhasil diverifikasi",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "email_verified": true,
            "role": "Pengecer"
        },
        "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yzc567"
    }
}
```

**Response Error (400):**
```json
{
    "status": "error",
    "message": "Kode OTP tidak valid atau sudah kedaluwarsa"
}
```

### 3. Resend OTP
```
POST /api/resend-otp
```

**Rate Limit:** 2 requests per minute

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Kode OTP baru telah dikirim ke email Anda"
}
```

---

## 🔑 Password Reset Endpoints

### 1. Send Reset Link
```
POST /api/forgot-password
```

**Rate Limit:** 3 requests per minute

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Link reset password telah dikirim ke email Anda"
}
```

### 2. Verify Reset Token
```
POST /api/verify-reset-token
```

**Rate Limit:** 10 requests per minute

**Request Body:**
```json
{
    "token": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yzc567abc123def456",
    "email": "john@example.com"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Token valid",
    "data": {
        "can_reset": true
    }
}
```

### 3. Reset Password
```
POST /api/reset-password
```

**Rate Limit:** 5 requests per minute

**Request Body:**
```json
{
    "token": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yzc567abc123def456",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Password berhasil direset"
}
```

---

## 📦 Products Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### 1. Get All Products
```
GET /api/products
```

**Query Parameters:**
- `per_page` (integer, default: 15, max: 100) - Items per page
- `search` (string) - Search by product name
- `kategori` (string) - Filter by category
- `sort_by` (string) - Sort field (nama_barang, harga_jual, jumlah_barang)
- `sort_order` (string) - asc/desc

**Example Request:**
```
GET /api/products?per_page=20&search=indomie&kategori=Makanan&sort_by=harga_jual&sort_order=asc
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Data produk berhasil diambil",
    "data": [
        {
            "id": 1,
            "nama_barang": "Indomie Goreng",
            "foto_barang": "http://localhost:8000/storage/images/indomie.jpg",
            "harga_jual": 3500.00,
            "jumlah_barang": 100,
            "kategori_barang": "Makanan"
        },
        {
            "id": 2,
            "nama_barang": "Indomie Soto",
            "foto_barang": "http://localhost:8000/storage/images/indomie-soto.jpg",
            "harga_jual": 3500.00,
            "jumlah_barang": 85,
            "kategori_barang": "Makanan"
        }
    ],
    "total_products": 2,
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 2
    }
}
```

### 2. Get Product Categories
```
GET /api/products/categories
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        "Makanan",
        "Minuman",
        "Snack",
        "Bumbu Dapur",
        "Peralatan Rumah Tangga"
    ]
}
```

### 3. Get Products by Category
```
GET /api/products/category/{kategori}
```

**Path Parameters:**
- `kategori` (string) - Category name (URL encoded)

**Example Request:**
```
GET /api/products/category/Makanan
```

**Response:** Same format as Get All Products, filtered by category

### 4. Search Products
```
GET /api/products/search
```

**Query Parameters:**
- `q` (string, required) - Search query
- `kategori` (string, optional) - Filter by category

**Example Request:**
```
GET /api/products/search?q=indomie&kategori=Makanan
```

### 5. Get Single Product
```
GET /api/products/{id}
```

**Path Parameters:**
- `id` (integer) - Product ID

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "nama_barang": "Indomie Goreng",
        "foto_barang": "http://localhost:8000/storage/images/indomie.jpg",
        "harga_jual": 3500.00,
        "jumlah_barang": 100,
        "kategori_barang": "Makanan",
        "created_at": "2025-07-25T08:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Produk tidak ditemukan"
}
```

---

## 📊 Dashboard Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### 1. Get Dashboard Statistics
```
GET /api/dashboard/stats
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "barang_masuk_hari_ini": 150,
        "barang_keluar_hari_ini": 75,
        "transaksi_penjualan_hari_ini": 25,
        "transaksi_pembelian_hari_ini": 10,
        "total_stok_tersedia": 5000,
        "total_kategori": 15,
        "total_barang_masuk": 1000,
        "total_barang_keluar": 500
    }
}
```

### 2. Get Low Stock Warnings
```
GET /api/dashboard/low-stock
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "nama_barang": "Indomie Goreng",
            "jumlah_barang": 5,
            "kategori_barang": "Makanan",
            "status_level": "critical",
            "lokasi_rak_barang": "R1-1-1"
        },
        {
            "id": 15,
            "nama_barang": "Teh Botol Sosro",
            "jumlah_barang": 12,
            "kategori_barang": "Minuman",
            "status_level": "low",
            "lokasi_rak_barang": "R2-3-4"
        }
    ],
    "summary": {
        "critical_count": 1,
        "low_count": 1,
        "total_affected": 2
    }
}
```

### 3. Get Stock Notifications (Manager Only)
```
GET /api/dashboard/notifications
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "out_of_stock": {
            "count": 3,
            "items": [
                {
                    "id": 5,
                    "nama_barang": "Chitato Rasa BBQ",
                    "kategori_barang": "Snack",
                    "lokasi_rak_barang": "R3-2-1"
                }
            ]
        },
        "low_stock": {
            "count": 8,
            "items": [
                {
                    "id": 1,
                    "nama_barang": "Indomie Goreng",
                    "jumlah_barang": 5,
                    "kategori_barang": "Makanan",
                    "lokasi_rak_barang": "R1-1-1"
                }
            ]
        }
    }
}
```

### 4. Get Weekly Statistics
```
GET /api/dashboard/weekly-stats
```

**Query Parameters:**
- `start_date` (date, optional) - Start date (YYYY-MM-DD)
- `end_date` (date, optional) - End date (YYYY-MM-DD)

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "chart_labels": ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
        "incoming_data": [100, 150, 200, 120, 180, 90, 110],
        "outgoing_data": [80, 120, 160, 95, 140, 70, 85],
        "period": "22 Juli - 28 Juli 2025",
        "summary": {
            "total_incoming": 950,
            "total_outgoing": 750,
            "net_change": 200,
            "average_daily_incoming": 135.7,
            "average_daily_outgoing": 107.1
        }
    }
}
```

---

## 🔄 Return Items Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### 1. Get All Returns
```
GET /api/return-items
```

**Query Parameters:**
- `per_page` (integer, default: 15)
- `search` (string) - Search query
- `kategori` (string) - Filter by category
- `date_from` (date) - Start date filter (YYYY-MM-DD)
- `date_to` (date) - End date filter (YYYY-MM-DD)
- `reason_category` (string) - damaged/incorrect/expired/quality_issue/other

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "order_item_id": 5,
            "nama_barang": "Indomie Goreng",
            "jumlah_barang": 2,
            "alasan_pengembalian": "Kemasan rusak saat pengiriman",
            "foto_bukti": "http://localhost:8000/storage/returns/evidence_001.jpg",
            "tanggal_pengembalian": "2025-07-27",
            "status_return": "Pending",
            "reason_category": "damaged",
            "order": {
                "order_number": "ORD-2025-001",
                "total_amount": 50000,
                "order_date": "2025-07-20"
            },
            "user": {
                "full_name": "John Doe",
                "email": "john@example.com"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_items": 25
    },
    "summary": {
        "total_returns": 25,
        "total_amount": 500000,
        "reason_breakdown": {
            "damaged": 10,
            "incorrect": 5,
            "expired": 3,
            "quality_issue": 5,
            "other": 2
        },
        "status_breakdown": {
            "pending": 15,
            "approved": 8,
            "rejected": 2
        }
    }
}
```

### 2. Get Returnable Order Items
```
GET /api/return-items/returnable-items
```

**Deskripsi:** Mendapatkan daftar item dari order user yang bisa di-return (order status completed/delivered).

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 5,
            "order_id": 10,
            "product_name": "Indomie Goreng",
            "quantity": 10,
            "returned_quantity": 2,
            "available_for_return": 8,
            "unit_price": 3500,
            "order_status": "completed",
            "order_date": "2025-07-20",
            "order_number": "ORD-2025-010"
        },
        {
            "id": 7,
            "order_id": 12,
            "product_name": "Teh Botol Sosro",
            "quantity": 5,
            "returned_quantity": 0,
            "available_for_return": 5,
            "unit_price": 4000,
            "order_status": "delivered",
            "order_date": "2025-07-18",
            "order_number": "ORD-2025-012"
        }
    ],
    "summary": {
        "total_eligible_items": 2,
        "total_returnable_quantity": 13,
        "total_returnable_value": 48000
    }
}
```

### 3. Create New Return
```
POST /api/return-items
```

**Content-Type:** multipart/form-data

**Request Body:**
```
order_item_id: 5
jumlah_barang: 2
alasan_pengembalian: Kemasan rusak saat pengiriman
foto_bukti: [file] // Optional, max 2MB, JPG/JPEG/PNG
```

**Response Success (201):**
```json
{
    "status": "success",
    "message": "Return berhasil dibuat",
    "data": {
        "id": 25,
        "order_item_id": 5,
        "jumlah_barang": 2,
        "alasan_pengembalian": "Kemasan rusak saat pengiriman",
        "foto_bukti": "http://localhost:8000/storage/returns/evidence_025.jpg",
        "tanggal_pengembalian": "2025-07-27",
        "status_return": "Pending",
        "reason_category": "damaged"
    }
}
```

**Response Error (422):**
```json
{
    "status": "error",
    "message": "Data yang diberikan tidak valid",
    "errors": {
        "jumlah_barang": ["Jumlah barang melebihi yang tersedia untuk return"],
        "foto_bukti": ["File terlalu besar. Maksimal 2MB"]
    }
}
```

### 4. Update Return
```
PUT /api/return-items/{id}
```

**Request Body:**
```json
{
    "jumlah_barang": 3,
    "alasan_pengembalian": "Updated reason for return",
    "foto_bukti": "base64_image_string_or_file"
}
```

### 5. Delete Return
```
DELETE /api/return-items/{id}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Return berhasil dihapus"
}
```

---

## 🛒 Orders Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### 1. Get User Orders
```
GET /api/orders
```

**Query Parameters:**
- `per_page` (integer, default: 15)
- `status` (string) - pending/processing/shipped/delivered/cancelled
- `payment_status` (string) - pending/paid/failed
- `date_from` (date) - Start date filter
- `date_to` (date) - End date filter

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "order_number": "ORD-2025-001",
            "status": "processing",
            "payment_status": "paid",
            "total_amount": 150000,
            "shipping_cost": 15000,
            "discount_amount": 15000,
            "final_amount": 150000,
            "shipping_address": "Jl. Contoh No. 123, Jakarta",
            "notes": "Kirim pagi hari",
            "created_at": "2025-07-27T10:00:00",
            "order_items": [
                {
                    "id": 1,
                    "product_name": "Indomie Goreng",
                    "product_image": "http://localhost:8000/storage/images/indomie.jpg",
                    "quantity": 10,
                    "unit": "pcs",
                    "unit_price": 3500,
                    "total_price": 35000
                },
                {
                    "id": 2,
                    "product_name": "Teh Botol Sosro",
                    "product_image": "http://localhost:8000/storage/images/teh-botol.jpg",
                    "quantity": 5,
                    "unit": "botol",
                    "unit_price": 4000,
                    "total_price": 20000
                }
            ],
            "shipping_method": {
                "name": "JNE Reguler",
                "description": "Pengiriman 2-3 hari kerja"
            },
            "voucher": {
                "code": "DISCOUNT10",
                "name": "Diskon 10%"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_items": 50
    }
}
```

### 2. Create New Order
```
POST /api/orders
```

**Request Body:**
```json
{
    "shipping_method_id": 1,
    "voucher_code": "DISCOUNT10",
    "shipping_address": "Jl. Contoh No. 123, Jakarta Selatan",
    "notes": "Kirim pagi hari sebelum jam 10",
    "items": [
        {
            "product_id": 1,
            "quantity": 10
        },
        {
            "product_id": 2,
            "quantity": 5
        }
    ]
}
```

**Validation Rules:**
- `shipping_method_id`: required, exists in shipping_methods
- `voucher_code`: optional, string, valid voucher code
- `shipping_address`: required, string, max:500
- `notes`: optional, string, max:1000
- `items`: required, array, min:1
- `items.*.product_id`: required, exists in incoming_items
- `items.*.quantity`: required, integer, min:1

**Response Success (201):**
```json
{
    "status": "success",
    "message": "Pesanan berhasil dibuat",
    "data": {
        "order_id": 25,
        "order_number": "ORD-2025-025",
        "subtotal": 55000,
        "shipping_cost": 15000,
        "discount_amount": 5500,
        "total_amount": 64500,
        "items_count": 2,
        "estimated_delivery": "2-3 hari kerja"
    }
}
```

**Response Error (422):**
```json
{
    "status": "error",
    "message": "Data yang diberikan tidak valid",
    "errors": {
        "items.0.quantity": ["Stok tidak mencukupi. Tersedia: 8"],
        "voucher_code": ["Voucher tidak valid atau sudah kedaluwarsa"]
    }
}
```

### 3. Get Order Statistics
```
GET /api/orders/stats
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "total_orders": 100,
        "pending_orders": 15,
        "processing_orders": 25,
        "completed_orders": 55,
        "cancelled_orders": 5,
        "total_revenue": 5000000,
        "average_order_value": 50000,
        "this_month_orders": 25,
        "this_month_revenue": 1250000
    }
}
```

---

## 🎫 Vouchers Endpoints

**Auth Required:** ✅ Semua endpoint memerlukan autentikasi

### 1. Get Available Vouchers
```
GET /api/vouchers
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "code": "DISCOUNT10",
            "name": "Diskon 10%",
            "description": "Diskon 10% untuk pembelian minimal Rp 100.000",
            "type": "percentage",
            "value": 10,
            "minimum_purchase": 100000,
            "maximum_discount": 50000,
            "valid_from": "2025-07-01",
            "valid_until": "2025-07-31",
            "usage_limit": 100,
            "used_count": 25,
            "is_active": true
        },
        {
            "id": 2,
            "code": "HEMAT20K",
            "name": "Hemat 20 Ribu",
            "description": "Potongan langsung Rp 20.000 untuk pembelian minimal Rp 200.000",
            "type": "fixed",
            "value": 20000,
            "minimum_purchase": 200000,
            "maximum_discount": 20000,
            "valid_from": "2025-07-15",
            "valid_until": "2025-08-15",
            "usage_limit": 50,
            "used_count": 12,
            "is_active": true
        }
    ]
}
```

### 2. Validate Voucher
```
POST /api/vouchers/validate
```

**Request Body:**
```json
{
    "voucher_code": "DISCOUNT10",
    "subtotal": 150000,
    "shipping_cost": 15000
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Voucher valid",
    "data": {
        "voucher": {
            "code": "DISCOUNT10",
            "name": "Diskon 10%",
            "type": "percentage",
            "value": 10
        },
        "calculations": {
            "subtotal": 150000,
            "shipping_cost": 15000,
            "discount_amount": 15000,
            "total_before_discount": 165000,
            "final_total": 150000
        },
        "savings": 15000
    }
}
```

**Response Error (422):**
```json
{
    "status": "error",
    "message": "Voucher tidak dapat digunakan",
    "errors": {
        "voucher_code": ["Minimum pembelian untuk voucher ini adalah Rp 200.000"]
    }
}
```

---

## 🚚 Shipping Methods Endpoints

**Auth Required:** ❌ Public endpoint

### 1. Get Shipping Methods
```
GET /api/shipping-methods
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "JNE Reguler",
            "description": "Pengiriman reguler 2-3 hari kerja",
            "price": 15000,
            "estimated_days": "2-3",
            "is_active": true
        },
        {
            "id": 2,
            "name": "JNE YES",
            "description": "Pengiriman express 1 hari kerja",
            "price": 25000,
            "estimated_days": "1",
            "is_active": true
        },
        {
            "id": 3,
            "name": "JNT Reguler",
            "description": "Pengiriman ekonomis 3-4 hari kerja",
            "price": 12000,
            "estimated_days": "3-4",
            "is_active": true
        }
    ]
}
```

---

## 🔒 Security & Error Handling

### Rate Limiting
- **Email OTP endpoints:** 2-3 requests per minute
- **Password reset endpoints:** 3-10 requests per minute
- **Other endpoints:** Follow Laravel default throttling

### Authentication Errors

**Token Invalid/Expired (401):**
```json
{
    "status": "error",
    "message": "Token tidak valid atau telah kedaluwarsa"
}
```

**Missing Authorization (401):**
```json
{
    "status": "error",
    "message": "Token autentikasi diperlukan"
}
```

### Validation Errors (422)
```json
{
    "status": "error",
    "message": "Data yang diberikan tidak valid",
    "errors": {
        "email": ["Email harus berupa alamat email yang valid"],
        "password": ["Password minimal 8 karakter"],
        "foto_bukti": ["File harus berupa gambar (jpg, jpeg, png)"]
    }
}
```

### Rate Limit Error (429)
```json
{
    "status": "error",
    "message": "Terlalu banyak permintaan. Silakan coba lagi dalam 60 detik.",
    "retry_after": 60
}
```

### Server Error (500)
```json
{
    "status": "error",
    "message": "Terjadi kesalahan pada server. Silakan coba lagi nanti.",
    "error_code": "INTERNAL_SERVER_ERROR"
}
```

---

## 🚀 Getting Started

### 1. Registrasi dan Verifikasi
```bash
# 1. Registrasi user baru
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123"
  }'

# 2. Verifikasi email dengan OTP
curl -X POST http://localhost:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "otp": "123456"
  }'
```

### 2. Login dan Mendapatkan Token
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 3. Menggunakan Token untuk Request
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 4. Upload File (Return Evidence)
```bash
curl -X POST http://localhost:8000/api/return-items \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "order_item_id=5" \
  -F "jumlah_barang=2" \
  -F "alasan_pengembalian=Kemasan rusak" \
  -F "foto_bukti=@/path/to/image.jpg"
```

---

## 📚 Additional Information

### File Upload Specifications
- **Supported formats:** JPG, JPEG, PNG
- **Maximum size:** 2MB per file
- **Storage:** Laravel storage with public access
- **URL format:** `{base_url}/storage/{path}`

### Pagination Response Format
```json
{
    "pagination": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150,
        "from": 1,
        "to": 15
    }
}
```

### Date Formats
- **Input:** YYYY-MM-DD (ISO 8601)
- **Output:** YYYY-MM-DDTHH:mm:ss.ssssssZ (ISO 8601 with timezone)

### Currency
- All monetary values in Indonesian Rupiah (IDR)
- Format: integer/float without currency symbol
- Example: 15000 (represents Rp 15.000)

---

**Last Updated:** July 27, 2025  
**API Version:** 1.0  
**Laravel Version:** 10.x  
**Documentation Generated by:** Claude Code Assistant