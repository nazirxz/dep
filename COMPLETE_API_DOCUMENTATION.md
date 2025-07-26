# UDKS Complete API Documentation

## Base URL
```
https://udkeluargasehati.com/api
```

## Authentication
Most endpoints require Bearer token authentication:
```
Authorization: Bearer {your-access-token}
```

---

## 📋 API Endpoints Overview

### 🔐 Authentication (Public)
| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| POST | `/register` | User registration | - |
| POST | `/login` | User login | - |
| POST | `/logout` | User logout | Auth Required |

### 📧 Email Verification (Public)
| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| POST | `/send-otp` | Send OTP to email | 3/minute |
| POST | `/verify-otp` | Verify OTP code | 5/minute |
| POST | `/resend-otp` | Resend OTP | 2/minute |

### 🔑 Password Reset (Public)
| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| POST | `/forgot-password` | Send reset link | 3/minute |
| POST | `/verify-reset-token` | Verify reset token | 10/minute |
| POST | `/reset-password` | Reset password | 5/minute |

### 👤 User Profile (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/user` | Get current user profile |

### 🛍️ Products (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | Get all products |
| GET | `/products/categories` | Get all categories |
| GET | `/products/category/{kategori}` | Get products by category |
| GET | `/products/search?q={keyword}` | Search products |
| GET | `/products/{id}` | Get product detail |

### 📊 Dashboard (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard/stats` | Get dashboard statistics |
| GET | `/dashboard/low-stock` | Get low stock warnings |
| GET | `/dashboard/notifications` | Get stock notifications |
| GET | `/dashboard/weekly-stats` | Get weekly statistics |
| GET | `/dashboard/monthly-stats` | Get monthly statistics |
| GET | `/dashboard/complete` | Get complete dashboard data |

### 📦 Incoming Items (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/incoming-items` | Get all incoming items |
| GET | `/incoming-items/categories` | Get categories |
| GET | `/incoming-items/category/{kategori}` | Get by category |
| GET | `/incoming-items/search?q={keyword}` | Search items |
| GET | `/incoming-items/weekly-incoming-stats` | Weekly statistics |
| GET | `/incoming-items/{id}` | Get item detail |

### 📤 Outgoing Items (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/outgoing-items` | Get all outgoing items |
| GET | `/outgoing-items/categories` | Get categories |
| GET | `/outgoing-items/category/{kategori}` | Get by category |
| GET | `/outgoing-items/search?q={keyword}` | Search items |
| GET | `/outgoing-items/weekly-sales-stats` | Weekly sales statistics |
| GET | `/outgoing-items/{id}` | Get item detail |

### ↩️ Return Items (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/return-items` | Get all returned items |
| GET | `/return-items/returnable-items` | Get returnable order items |
| POST | `/return-items` | Create new return |
| PUT | `/return-items/{id}` | Update return item |
| DELETE | `/return-items/{id}` | Delete return item |
| GET | `/return-items/categories` | Get categories |
| GET | `/return-items/category/{kategori}` | Get by category |
| GET | `/return-items/search?q={keyword}` | Search returns |
| GET | `/return-items/weekly-return-stats` | Weekly return statistics |
| GET | `/return-items/{id}` | Get return detail |

### 🛒 Orders (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders` | Get user's orders |
| POST | `/orders` | Create new order |
| GET | `/orders/stats` | Get order statistics |
| GET | `/orders/sales` | Get orders for shipping |
| GET | `/orders/{id}` | Get order detail |
| PUT | `/orders/{id}/shipping-status` | Update shipping status |

#### Admin Only (Requires Admin Role)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders/admin` | Get all orders |
| GET | `/orders/user/{userId}` | Get orders by user ID |
| PUT | `/orders/{id}/finished-packing` | Mark as finished packing |

### 🎟️ Vouchers (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/vouchers` | Get available vouchers |
| POST | `/vouchers/validate` | Validate voucher code |

### 🚚 Shipping Methods (Public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/shipping-methods` | Get available shipping methods |

---

## 📝 Detailed API Documentation

### 🔐 Authentication

#### 1. Register
```http
POST /api/register
```

**Request:**
```json
{
    "full_name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Registrasi berhasil. Kode OTP telah dikirim ke email Anda untuk verifikasi.",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "role": "Pengecer",
        "phone_number": null,
        "email_verified_at": null,
        "created_at": "2025-01-01T10:00:00.000000Z",
        "updated_at": "2025-01-01T10:00:00.000000Z"
    },
    "requires_verification": true,
    "expires_at": "2025-01-01T10:10:00.000000Z"
}
```

#### 2. Login
```http
POST /api/login
```

**Request:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "access_token": "1|eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "role": "Pengecer",
        "email_verified_at": "2025-01-01T10:05:00.000000Z"
    }
}
```

#### 3. Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

### 📧 Email Verification

#### 1. Send OTP
```http
POST /api/send-otp
```

**Request:**
```json
{
    "email": "john@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Kode OTP telah dikirim ke email Anda",
    "expires_at": "2025-01-01T10:10:00.000000Z"
}
```

#### 2. Verify OTP
```http
POST /api/verify-otp
```

**Request:**
```json
{
    "email": "john@example.com",
    "otp": "123456"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Email berhasil diverifikasi",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2025-01-01T10:05:00.000000Z"
    },
    "access_token": "1|eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
}
```

---

### 🔑 Password Reset

#### 1. Forgot Password
```http
POST /api/forgot-password
```

**Request:**
```json
{
    "email": "john@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Link reset password telah dikirim ke email Anda"
}
```

#### 2. Reset Password
```http
POST /api/reset-password
```

**Request:**
```json
{
    "token": "reset-token-from-email",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password berhasil direset"
}
```

---

### 👤 User Profile

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "id": 1,
    "full_name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "Pengecer",
    "phone_number": "+628123456789",
    "email_verified_at": "2025-01-01T10:05:00.000000Z",
    "created_at": "2025-01-01T10:00:00.000000Z",
    "updated_at": "2025-01-01T10:05:00.000000Z"
}
```

---

### 🛍️ Products

#### 1. Get All Products
```http
GET /api/products
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `kategori` (optional): Filter by category
- `search` (optional): Search term

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "nama_barang": "Beras Premium",
                "kategori": "Makanan Pokok",
                "harga_jual": 15000,
                "stok": 100,
                "satuan": "kg",
                "foto_barang": "http://example.com/storage/products/beras.jpg",
                "producer": {
                    "id": 1,
                    "nama_produsen": "CV Beras Sejahtera"
                },
                "created_at": "2025-01-01T10:00:00.000000Z"
            }
        ],
        "first_page_url": "http://example.com/api/products?page=1",
        "from": 1,
        "last_page": 5,
        "last_page_url": "http://example.com/api/products?page=5",
        "next_page_url": "http://example.com/api/products?page=2",
        "path": "http://example.com/api/products",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 67
    }
}
```

#### 2. Get Product Categories
```http
GET /api/products/categories
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        "Makanan Pokok",
        "Bumbu Dapur",
        "Minuman",
        "Snack",
        "Kebutuhan Rumah Tangga"
    ]
}
```

#### 3. Get Products by Category
```http
GET /api/products/category/{kategori}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "nama_barang": "Beras Premium",
                "kategori": "Makanan Pokok",
                "harga_jual": 15000,
                "stok": 100,
                "satuan": "kg"
            }
        ]
    }
}
```

#### 4. Search Products
```http
GET /api/products/search?q=beras
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nama_barang": "Beras Premium",
            "kategori": "Makanan Pokok",
            "harga_jual": 15000,
            "stok": 100,
            "satuan": "kg",
            "foto_barang": "http://example.com/storage/products/beras.jpg"
        }
    ]
}
```

#### 5. Get Product Detail
```http
GET /api/products/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nama_barang": "Beras Premium",
        "kategori": "Makanan Pokok",
        "harga_jual": 15000,
        "stok": 100,
        "satuan": "kg",
        "foto_barang": "http://example.com/storage/products/beras.jpg",
        "deskripsi": "Beras premium kualitas terbaik",
        "producer": {
            "id": 1,
            "nama_produsen": "CV Beras Sejahtera",
            "alamat": "Jakarta",
            "no_telepon": "08123456789"
        },
        "created_at": "2025-01-01T10:00:00.000000Z",
        "updated_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

---

### 📊 Dashboard

#### 1. Get Dashboard Statistics
```http
GET /api/dashboard/stats
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "total_products": 150,
        "total_incoming_items": 1250,
        "total_outgoing_items": 890,
        "low_stock_items": 12,
        "total_orders": 245,
        "pending_orders": 8,
        "completed_orders": 237,
        "total_revenue": 15750000,
        "monthly_revenue": 2500000,
        "weekly_revenue": 650000
    }
}
```

#### 2. Get Low Stock Warning
```http
GET /api/dashboard/low-stock
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "nama_barang": "Gula Pasir",
            "kategori": "Makanan Pokok",
            "stok": 8,
            "minimum_stock": 20,
            "satuan": "kg",
            "status": "critical"
        },
        {
            "id": 12,
            "nama_barang": "Minyak Goreng",
            "kategori": "Kebutuhan Dapur",
            "stok": 15,
            "minimum_stock": 30,
            "satuan": "liter",
            "status": "warning"
        }
    ]
}
```

#### 3. Get Complete Dashboard Data
```http
GET /api/dashboard/complete
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "stats": {
            "total_products": 150,
            "total_orders": 245,
            "total_revenue": 15750000
        },
        "low_stock_items": [
            {
                "id": 5,
                "nama_barang": "Gula Pasir",
                "stok": 8
            }
        ],
        "weekly_stats": {
            "incoming": [
                {"date": "2025-01-01", "total": 50},
                {"date": "2025-01-02", "total": 45}
            ],
            "outgoing": [
                {"date": "2025-01-01", "total": 30},
                {"date": "2025-01-02", "total": 35}
            ]
        },
        "recent_orders": [
            {
                "id": 1,
                "order_number": "ORD-2025-001",
                "total_amount": 150000,
                "status": "completed",
                "created_at": "2025-01-01T10:00:00.000000Z"
            }
        ]
    }
}
```

---

### 🛒 Orders

#### 1. Get User Orders
```http
GET /api/orders
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number
- `status` (optional): Filter by status (pending, processing, shipped, delivered, cancelled)
- `search` (optional): Search by order number

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "order_number": "ORD-2025-001",
                "total_amount": 150000,
                "status": "processing",
                "shipping_cost": 15000,
                "discount_amount": 5000,
                "final_amount": 160000,
                "delivery_address": "Jl. Merdeka No. 123, Jakarta",
                "delivery_phone": "08123456789",
                "delivery_notes": "Antar sebelum jam 5 sore",
                "shipping_method": {
                    "id": 1,
                    "name": "Regular",
                    "cost": 15000,
                    "estimated_days": "2-3 hari"
                },
                "voucher": {
                    "id": 1,
                    "code": "DISKON5K",
                    "discount_amount": 5000
                },
                "items": [
                    {
                        "id": 1,
                        "product_id": 5,
                        "product_name": "Beras Premium",
                        "quantity": 5,
                        "price": 15000,
                        "subtotal": 75000
                    },
                    {
                        "id": 2,
                        "product_id": 8,
                        "product_name": "Minyak Goreng",
                        "quantity": 3,
                        "price": 25000,
                        "subtotal": 75000
                    }
                ],
                "created_at": "2025-01-01T10:00:00.000000Z",
                "updated_at": "2025-01-01T11:00:00.000000Z"
            }
        ],
        "total": 10,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

#### 2. Create New Order
```http
POST /api/orders
Authorization: Bearer {token}
```

**Request:**
```json
{
    "items": [
        {
            "product_id": 5,
            "quantity": 5
        },
        {
            "product_id": 8,
            "quantity": 3
        }
    ],
    "shipping_method_id": 1,
    "voucher_code": "DISKON5K",
    "delivery_address": "Jl. Merdeka No. 123, Jakarta",
    "delivery_phone": "08123456789",
    "delivery_notes": "Antar sebelum jam 5 sore"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Order berhasil dibuat",
    "data": {
        "id": 1,
        "order_number": "ORD-2025-001",
        "total_amount": 150000,
        "shipping_cost": 15000,
        "discount_amount": 5000,
        "final_amount": 160000,
        "status": "pending",
        "items": [...],
        "created_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

#### 3. Get Order Detail
```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "order_number": "ORD-2025-001",
        "status": "processing",
        "total_amount": 150000,
        "shipping_cost": 15000,
        "discount_amount": 5000,
        "final_amount": 160000,
        "delivery_address": "Jl. Merdeka No. 123, Jakarta",
        "delivery_phone": "08123456789",
        "shipping_tracking": "TRK123456789",
        "shipping_status": "in_transit",
        "estimated_delivery": "2025-01-03",
        "items": [...],
        "shipping_method": {...},
        "voucher": {...},
        "created_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

---

### ↩️ Return Items

#### 1. Create Return Item
```http
POST /api/return-items
Authorization: Bearer {token}
```

**Request (multipart/form-data):**
```
order_item_id: 5
alasan_return: "Barang rusak saat diterima"
jumlah_return: 2
foto_bukti: [file upload]
catatan: "Kemasan penyok dan isi tumpah"
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Return item berhasil dibuat",
    "data": {
        "id": 1,
        "order_item_id": 5,
        "product_name": "Beras Premium",
        "jumlah_return": 2,
        "alasan_return": "Barang rusak saat diterima",
        "status": "pending",
        "foto_bukti": "http://example.com/storage/returns/bukti_1.jpg",
        "catatan": "Kemasan penyok dan isi tumpah",
        "created_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

#### 2. Get Returnable Order Items
```http
GET /api/return-items/returnable-items
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "order_item_id": 5,
            "order_number": "ORD-2025-001",
            "product_id": 8,
            "product_name": "Beras Premium",
            "quantity": 5,
            "price": 15000,
            "can_return_quantity": 5,
            "order_date": "2025-01-01T10:00:00.000000Z",
            "days_since_order": 2
        }
    ]
}
```

---

### 🎟️ Vouchers

#### 1. Get Available Vouchers
```http
GET /api/vouchers
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "DISKON5K",
            "name": "Diskon 5 Ribu",
            "description": "Diskon Rp 5.000 untuk pembelian minimum Rp 50.000",
            "discount_type": "fixed",
            "discount_value": 5000,
            "minimum_amount": 50000,
            "maximum_discount": 5000,
            "valid_from": "2025-01-01T00:00:00.000000Z",
            "valid_until": "2025-01-31T23:59:59.000000Z",
            "usage_limit": 100,
            "used_count": 25,
            "is_active": true
        }
    ]
}
```

#### 2. Validate Voucher
```http
POST /api/vouchers/validate
Authorization: Bearer {token}
```

**Request:**
```json
{
    "code": "DISKON5K",
    "total_amount": 75000
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Voucher valid",
    "data": {
        "voucher": {
            "id": 1,
            "code": "DISKON5K",
            "discount_type": "fixed",
            "discount_value": 5000
        },
        "discount_amount": 5000,
        "final_amount": 70000
    }
}
```

---

### 🚚 Shipping Methods

#### Get Available Shipping Methods
```http
GET /api/shipping-methods
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Regular",
            "description": "Pengiriman reguler 2-3 hari kerja",
            "cost": 15000,
            "estimated_days": "2-3 hari",
            "is_active": true
        },
        {
            "id": 2,
            "name": "Express",
            "description": "Pengiriman express 1 hari kerja",
            "cost": 25000,
            "estimated_days": "1 hari",
            "is_active": true
        }
    ]
}
```

---

## 🚨 Common Error Responses

### Authentication Required (401)
```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "message": "Access denied. Admin role required."
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Data tidak ditemukan"
}
```

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field must be at least 8 characters."]
    }
}
```

### Rate Limit Exceeded (429)
```json
{
    "message": "Too Many Attempts."
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Terjadi kesalahan server"
}
```

---

## 📱 Mobile Implementation Notes

### Headers
Always include these headers:
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token} // for protected routes
```

### Error Handling
- Always check the `success` field in responses
- Handle HTTP status codes appropriately
- Implement retry logic for 5xx errors
- Show user-friendly messages for 4xx errors

### Pagination
Most list endpoints support pagination:
- Use `page` parameter for page number
- Use `per_page` parameter for items per page
- Check `last_page` to know total pages

### File Uploads
For endpoints accepting files (like return items):
- Use `multipart/form-data` content type
- Maximum file size: 2MB
- Supported formats: JPG, PNG, PDF

### Caching
Consider caching these endpoints:
- `/products/categories`
- `/shipping-methods`
- `/vouchers`

---

## 🔧 Testing

Use Postman collection or curl commands to test endpoints:

```bash
# Login
curl -X POST https://udkeluargasehati.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Get products with token
curl -X GET https://udkeluargasehati.com/api/products \
  -H "Authorization: Bearer {your-token}" \
  -H "Accept: application/json"
```