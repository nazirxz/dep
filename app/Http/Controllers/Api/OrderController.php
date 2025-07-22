<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutgoingItem;
use App\Models\IncomingItem;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of user's orders
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);
            
            $query = Order::where('user_id', $user->id)
                         ->with([
                             'orderItems:id,order_id,product_name,product_image,quantity,unit,unit_price,total_price',
                             'user:id,full_name,email'
                         ])
                         ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== '') {
                $query->byStatus($request->status);
            }

            // Filter by payment status if provided
            if ($request->has('payment_status') && $request->payment_status !== '') {
                $query->byPaymentStatus($request->payment_status);
            }

            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search by order number or pengecer name
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                      ->orWhere('pengecer_name', 'LIKE', "%{$search}%");
                });
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'pengecer_name' => 'required|string|max:255',
                'pengecer_phone' => 'nullable|string|max:20',
                'pengecer_email' => 'nullable|email|max:255',
                'shipping_address' => 'required|string',
                'city' => 'required|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'location_address' => 'nullable|string',
                'location_accuracy' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:incoming_items,id',
                'items.*.incoming_item_id' => 'required|integer|exists:incoming_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit' => 'nullable|string|max:20',
                'items.*.notes' => 'nullable|string',
                'shipping_method' => 'required|string|max:100',
                'payment_method' => 'required|string|max:100',
                'voucher_code' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $user = $request->user();
                $items = $request->items;
                
                // Hitung subtotal dan validasi stok
                $subtotal = 0;
                $orderItemsData = [];

                foreach ($items as $item) {
                    \Log::info('Processing order item', [
                        'product_id' => $item['product_id'],
                        'incoming_item_id' => $item['incoming_item_id'],
                        'quantity' => $item['quantity']
                    ]);
                    
                    // Validasi konsistensi product_id dan incoming_item_id
                    if ($item['product_id'] != $item['incoming_item_id']) {
                        $productItem = IncomingItem::find($item['product_id']);
                        $incomingItem = IncomingItem::find($item['incoming_item_id']);
                        
                        $productName = $productItem ? $productItem->nama_barang : 'Not found';
                        $incomingName = $incomingItem ? $incomingItem->nama_barang : 'Not found';
                        
                        throw new \Exception("Ketidakcocokan ID produk: product_id ({$item['product_id']}: {$productName}) berbeda dengan incoming_item_id ({$item['incoming_item_id']}: {$incomingName}). Kedua ID harus sama untuk menghindari kebingungan produk.");
                    }
                    
                    $incomingItem = IncomingItem::findOrFail($item['incoming_item_id']);
                    
                    \Log::info('Found incoming item', [
                        'id' => $incomingItem->id,
                        'nama_barang' => $incomingItem->nama_barang,
                        'kategori_barang' => $incomingItem->kategori_barang,
                        'jumlah_barang' => $incomingItem->jumlah_barang
                    ]);
                    
                    // Check stock availability
                    if ($incomingItem->jumlah_barang < $item['quantity']) {
                        throw new \Exception("Stok tidak mencukupi untuk barang: {$incomingItem->nama_barang}. Stok tersedia: {$incomingItem->jumlah_barang}");
                    }

                    $unitPrice = $incomingItem->harga_jual ?? $incomingItem->harga_barang;
                    $totalPrice = $unitPrice * $item['quantity'];
                    $subtotal += $totalPrice;

                    $orderItemsData[] = [
                        'product_id' => $item['product_id'],
                        'incoming_item_id' => $item['incoming_item_id'],
                        'product_name' => $incomingItem->nama_barang,
                        'product_image' => $incomingItem->foto_barang,
                        'product_category' => $incomingItem->kategori_barang,
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'notes' => $item['notes'] ?? null,
                        // Store complete incoming item data for later use
                        'incoming_item_data' => [
                            'id' => $incomingItem->id,
                            'nama_barang' => $incomingItem->nama_barang,
                            'kategori_barang' => $incomingItem->kategori_barang,
                            'category_id' => $incomingItem->category_id,
                            'producer_id' => $incomingItem->producer_id,
                            'lokasi_rak_barang' => $incomingItem->lokasi_rak_barang,
                        ]
                    ];
                }

                // Calculate shipping cost (simplified - could be more complex)
                $shippingCost = $this->calculateShippingCost($request->shipping_method);
                
                // Calculate tax (simplified - 10%)
                $taxAmount = $subtotal * 0.10;
                
                // Handle voucher
                $voucherDiscount = 0;
                $voucherCode = null;
                if ($request->voucher_code) {
                    $voucher = Voucher::where('code', $request->voucher_code)->active()->available()->first();
                    if ($voucher) {
                        $validation = $voucher->isValid($subtotal);
                        if ($validation['valid']) {
                            $voucherDiscount = $voucher->calculateDiscount($subtotal, $shippingCost);
                            $voucherCode = $voucher->code;
                        }
                    }
                }

                $discountAmount = $voucherDiscount;
                $totalAmount = $subtotal + $shippingCost + $taxAmount - $discountAmount;

                // Generate order number
                $orderNumber = Order::generateOrderNumber();

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'pengecer_name' => $request->pengecer_name,
                    'pengecer_phone' => $request->pengecer_phone,
                    'pengecer_email' => $request->pengecer_email,
                    'shipping_address' => $request->shipping_address,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'location_address' => $request->location_address,
                    'location_accuracy' => $request->location_accuracy,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'shipping_method' => $request->shipping_method,
                    'payment_method' => $request->payment_method,
                    'voucher_code' => $voucherCode,
                    'voucher_discount' => $voucherDiscount,
                    'notes' => $request->notes,
                ]);

                // Create order items and update stock
                foreach ($orderItemsData as $itemData) {
                    $incomingItemData = $itemData['incoming_item_data'];
                    unset($itemData['incoming_item_data']);
                    
                    $itemData['order_id'] = $order->id;
                    $orderItem = OrderItem::create($itemData);

                    // Create outgoing item record for stock tracking
                    \Log::info('Creating OutgoingItem for order', [
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'incoming_item_id' => $incomingItemData['id'],
                        'incoming_item_name' => $incomingItemData['nama_barang'],
                        'incoming_item_category' => $incomingItemData['kategori_barang'],
                        'requested_quantity' => $itemData['quantity'],
                        'order_item_data' => $itemData
                    ]);
                    
                    OutgoingItem::create([
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'incoming_item_id' => $incomingItemData['id'],
                        'nama_barang' => $incomingItemData['nama_barang'],
                        'kategori_barang' => $incomingItemData['kategori_barang'],
                        'category_id' => $incomingItemData['category_id'],
                        'producer_id' => $incomingItemData['producer_id'],
                        'tanggal_keluar_barang' => Carbon::now()->toDateString(),
                        'jumlah_barang' => $itemData['quantity'],
                        'lokasi_rak_barang' => $incomingItemData['lokasi_rak_barang'],
                        'tujuan_distribusi' => $request->pengecer_name,
                        'metode_bayar' => $request->payment_method,
                        'pembayaran_transaksi' => $totalAmount,
                        'pengecer_name' => $request->pengecer_name,
                        'pengecer_phone' => $request->pengecer_phone,
                        'shipping_address' => $request->shipping_address,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'location_address' => $request->location_address,
                        'transaction_type' => 'retail',
                    ]);

                    // Update incoming item stock - refetch to avoid stale data
                    $currentIncomingItem = IncomingItem::findOrFail($incomingItemData['id']);
                    $currentIncomingItem->decrement('jumlah_barang', $itemData['quantity']);
                }

                // Increment voucher usage if used
                if ($voucherCode) {
                    $voucher->incrementUsage();
                }

                // Load relationships
                $order->load('orderItems');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesanan berhasil dibuat',
                    'data' => $order
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $order = Order::where('user_id', $user->id)
                         ->where('id', $id)
                         ->with(['orderItems', 'outgoingItems'])
                         ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate shipping cost based on method
     */
    private function calculateShippingCost($shippingMethod): float
    {
        // Simplified shipping calculation
        switch (strtolower($shippingMethod)) {
            case 'standard delivery':
                return 15000;
            case 'express delivery':
                return 25000;
            case 'free delivery':
                return 0;
            default:
                return 15000;
        }
    }

    /**
     * Display orders for admin (all orders)
     */
    public function adminIndex(Request $request)
    {
        try {
            // Check if user is admin or sales
            $user = $request->user();
            if (!in_array($user->role, ['Admin', 'sales'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin or Sales access required.'
                ], 403);
            }

            $perPage = $request->get('per_page', 15);
            
            $query = Order::with([
                'orderItems:id,order_id,product_name,product_image,quantity,unit,unit_price,total_price',
                'user:id,full_name,email,phone_number'
            ])->orderBy('created_at', 'desc');

            // Filter by user_id if provided
            if ($request->has('user_id') && $request->user_id !== '') {
                $query->where('user_id', $request->user_id);
            }

            // Filter by status if provided
            if ($request->has('status') && $request->status !== '') {
                $query->byStatus($request->status);
            }

            // Filter by payment status if provided
            if ($request->has('payment_status') && $request->payment_status !== '') {
                $query->byPaymentStatus($request->payment_status);
            }

            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search by order number, pengecer name, or user email
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                      ->orWhere('pengecer_name', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('email', 'LIKE', "%{$search}%")
                                   ->orWhere('full_name', 'LIKE', "%{$search}%");
                      });
                });
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders by specific user ID (admin only)
     */
    public function getOrdersByUserId(Request $request, $userId)
    {
        try {
            // Check if user is admin
            $currentUser = $request->user();
            if ($currentUser->role !== 'Admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $perPage = $request->get('per_page', 15);
            
            $query = Order::where('user_id', $userId)
                         ->with([
                             'orderItems:id,order_id,product_name,product_image,quantity,unit,unit_price,total_price',
                             'user:id,full_name,email,phone_number'
                         ])
                         ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== '') {
                $query->byStatus($request->status);
            }

            // Filter by payment status if provided
            if ($request->has('payment_status') && $request->payment_status !== '') {
                $query->byPaymentStatus($request->payment_status);
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics for dashboard
     */
    public function getOrderStats(Request $request)
    {
        try {
            $user = $request->user();
            
            // Base query
            $baseQuery = Order::query();
            
            // If not admin, only show user's own stats
            if ($user->role !== 'Admin') {
                $baseQuery->where('user_id', $user->id);
            }

            $stats = [
                'total_orders' => (clone $baseQuery)->count(),
                'pending_orders' => (clone $baseQuery)->where('order_status', 'pending')->count(),
                'confirmed_orders' => (clone $baseQuery)->where('order_status', 'confirmed')->count(),
                'processing_orders' => (clone $baseQuery)->where('order_status', 'processing')->count(),
                'shipped_orders' => (clone $baseQuery)->where('order_status', 'shipped')->count(),
                'delivered_orders' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
                'cancelled_orders' => (clone $baseQuery)->where('order_status', 'cancelled')->count(),
                'total_amount' => (clone $baseQuery)->sum('total_amount'),
                'today_orders' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                'this_week_orders' => (clone $baseQuery)->whereBetween('created_at', [
                    now()->startOfWeek(), 
                    now()->endOfWeek()
                ])->count(),
                'this_month_orders' => (clone $baseQuery)->whereMonth('created_at', now()->month)
                                                         ->whereYear('created_at', now()->year)
                                                         ->count(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display orders for sales (ready to ship and shipping orders with location)
     */
    public function salesIndex(Request $request)
    {
        try {
            // Check if user is sales or admin
            $user = $request->user();
            if (!in_array($user->role, ['Admin', 'sales'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Sales access required.'
                ], 403);
            }

            $perPage = $request->get('per_page', 15);
            
            // Query untuk orders yang siap dikirim dan sedang dikirim
            $query = Order::with([
                'orderItems:id,order_id,product_name,product_image,quantity,unit,unit_price,total_price',
                'user:id,full_name,email,phone_number'
            ])
            ->whereIn('order_status', ['confirmed', 'processing', 'shipped'])
            ->select([
                'id', 'order_number', 'user_id', 'pengecer_name', 'pengecer_phone', 
                'pengecer_email', 'shipping_address', 'city', 'postal_code',
                'latitude', 'longitude', 'location_address', 'location_accuracy',
                'order_status', 'payment_status', 'shipping_method', 'payment_method',
                'subtotal', 'shipping_cost', 'discount_amount', 'total_amount',
                'voucher_code', 'notes', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc');

            // Filter berdasarkan status jika disediakan
            if ($request->has('status') && $request->status !== '') {
                $query->where('order_status', $request->status);
            }

            // Filter berdasarkan kota
            if ($request->has('city') && $request->city !== '') {
                $query->where('city', 'LIKE', "%{$request->city}%");
            }

            // Filter berdasarkan tanggal
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search berdasarkan order number atau nama pengecer
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                      ->orWhere('pengecer_name', 'LIKE', "%{$search}%")
                      ->orWhere('shipping_address', 'LIKE', "%{$search}%");
                });
            }

            $orders = $query->paginate($perPage);

            // Add distance calculation from warehouse if coordinates provided
            if ($request->has('warehouse_lat') && $request->has('warehouse_lng')) {
                $warehouseLat = $request->warehouse_lat;
                $warehouseLng = $request->warehouse_lng;
                
                foreach ($orders->items() as $order) {
                    if ($order->latitude && $order->longitude) {
                        $order->distance_km = $this->calculateDistance(
                            $warehouseLat, $warehouseLng, 
                            $order->latitude, $order->longitude
                        );
                    } else {
                        $order->distance_km = null;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $orders,
                'summary' => [
                    'ready_to_ship' => Order::where('order_status', 'confirmed')->count(),
                    'processing' => Order::where('order_status', 'processing')->count(),
                    'shipped' => Order::where('order_status', 'shipped')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order shipping status
     */
    public function updateShippingStatus(Request $request, $id)
    {
        try {
            // Check if user is sales or admin
            $user = $request->user();
            if (!in_array($user->role, ['Admin', 'sales'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Sales access required.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'order_status' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
                'delivery_notes' => 'nullable|string|max:500',
                'delivered_at' => 'nullable|date',
                'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::findOrFail($id);

            // Update order status
            $order->order_status = $request->order_status;
            
            if ($request->delivery_notes) {
                $order->notes = ($order->notes ? $order->notes . '\n' : '') . 
                               '[' . now()->format('Y-m-d H:i:s') . '] ' . $request->delivery_notes;
            }

            if ($request->delivered_at && $request->order_status === 'delivered') {
                $order->delivered_at = $request->delivered_at;
            }

            // Handle delivery photo upload
            if ($request->hasFile('delivery_photo')) {
                $photo = $request->file('delivery_photo');
                $photoName = 'delivery_' . $order->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('delivery_photos', $photoName, 'public');
                $order->delivery_photo = $photoPath;
            }

            $order->save();

            // Load fresh data with relationships
            $order->load(['orderItems', 'user:id,full_name,email,phone_number']);

            return response()->json([
                'status' => 'success',
                'message' => 'Status pengantaran berhasil diupdate',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal update status pengantaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($earthRadius * $c, 2);
    }
}

