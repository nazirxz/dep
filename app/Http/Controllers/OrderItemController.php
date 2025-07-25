<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the order items.
     */
    public function index()
    {
        $orderItems = OrderItem::with(['order', 'product'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $orderItems
        ]);
    }

    /**
     * Store a newly created order item in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:incoming_items,id',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ], [
            'order_id.required' => 'Pesanan harus dipilih.',
            'order_id.exists' => 'Pesanan yang dipilih tidak valid.',
            'product_id.required' => 'Produk harus dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'quantity.required' => 'Jumlah harus diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal 1.',
            'unit_price.required' => 'Harga satuan harus diisi.',
            'unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'unit_price.min' => 'Harga satuan tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal menambah item pesanan. Silakan periksa input Anda.');
        }

        try {
            DB::transaction(function () use ($request) {
                // Get product information
                $product = IncomingItem::findOrFail($request->product_id);
                
                // Check stock availability
                if ($product->jumlah_barang < $request->quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk produk {$product->nama_barang}. Stok tersedia: {$product->jumlah_barang}");
                }

                // Calculate total price
                $totalPrice = $request->quantity * $request->unit_price;

                // Create order item
                OrderItem::create([
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                    'incoming_item_id' => $request->product_id,
                    'product_name' => $product->nama_barang,
                    'product_image' => $product->foto_barang,
                    'product_category' => $product->kategori_barang,
                    'quantity' => $request->quantity,
                    'unit' => $request->unit ?? 'pcs',
                    'unit_price' => $request->unit_price,
                    'total_price' => $totalPrice,
                    'notes' => $request->notes,
                ]);

                // Update product stock
                $product->decrement('jumlah_barang', $request->quantity);
            });

            return redirect()->route('order.items')
                ->with('success', 'Item pesanan berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambah item pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order item.
     */
    public function show($id)
    {
        try {
            $orderItem = OrderItem::with(['order', 'product'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $orderItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item pesanan tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Update the specified order item in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:incoming_items,id',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ], [
            'order_id.required' => 'Pesanan harus dipilih.',
            'order_id.exists' => 'Pesanan yang dipilih tidak valid.',
            'product_id.required' => 'Produk harus dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'quantity.required' => 'Jumlah harus diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal 1.',
            'unit_price.required' => 'Harga satuan harus diisi.',
            'unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'unit_price.min' => 'Harga satuan tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal mengupdate item pesanan. Silakan periksa input Anda.');
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $orderItem = OrderItem::findOrFail($id);
                $oldQuantity = $orderItem->quantity;
                $oldProductId = $orderItem->product_id;

                // Get new product information
                $newProduct = IncomingItem::findOrFail($request->product_id);
                
                // If product changed, restore old product stock and check new product stock
                if ($oldProductId != $request->product_id) {
                    // Restore old product stock
                    $oldProduct = IncomingItem::findOrFail($oldProductId);
                    $oldProduct->increment('jumlah_barang', $oldQuantity);
                    
                    // Check new product stock
                    if ($newProduct->jumlah_barang < $request->quantity) {
                        throw new \Exception("Stok tidak mencukupi untuk produk {$newProduct->nama_barang}. Stok tersedia: {$newProduct->jumlah_barang}");
                    }
                    
                    // Decrease new product stock
                    $newProduct->decrement('jumlah_barang', $request->quantity);
                } else {
                    // Same product, calculate stock difference
                    $quantityDiff = $request->quantity - $oldQuantity;
                    
                    if ($quantityDiff > 0) {
                        // Need more stock
                        if ($newProduct->jumlah_barang < $quantityDiff) {
                            throw new \Exception("Stok tidak mencukupi untuk produk {$newProduct->nama_barang}. Tambahan stok yang dibutuhkan: {$quantityDiff}, Stok tersedia: {$newProduct->jumlah_barang}");
                        }
                        $newProduct->decrement('jumlah_barang', $quantityDiff);
                    } else if ($quantityDiff < 0) {
                        // Return excess stock
                        $newProduct->increment('jumlah_barang', abs($quantityDiff));
                    }
                }

                // Calculate total price
                $totalPrice = $request->quantity * $request->unit_price;

                // Update order item
                $orderItem->update([
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                    'incoming_item_id' => $request->product_id,
                    'product_name' => $newProduct->nama_barang,
                    'product_image' => $newProduct->foto_barang,
                    'product_category' => $newProduct->kategori_barang,
                    'quantity' => $request->quantity,
                    'unit' => $request->unit ?? 'pcs',
                    'unit_price' => $request->unit_price,
                    'total_price' => $totalPrice,
                    'notes' => $request->notes,
                ]);
            });

            return redirect()->route('order.items')
                ->with('success', 'Item pesanan berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate item pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified order item from storage.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $orderItem = OrderItem::findOrFail($id);
                
                // Restore product stock
                $product = IncomingItem::findOrFail($orderItem->product_id);
                $product->increment('jumlah_barang', $orderItem->quantity);
                
                // Delete order item
                $orderItem->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Item pesanan berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}