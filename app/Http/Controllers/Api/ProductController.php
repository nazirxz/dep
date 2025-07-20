<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get list of all products for mobile application
     * Returns: nama_barang, foto_barang, harga_jual, jumlah_barang, kategori_barang
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Query untuk mengambil data yang diperlukan mobile app
            $products = IncomingItem::select([
                'id',
                'nama_barang',
                'foto_barang', 
                'harga_jual',
                'jumlah_barang',
                'kategori_barang'
            ])
            ->where('jumlah_barang', '>', 0) // Hanya tampilkan barang yang masih ada stoknya
            ->orderBy('nama_barang', 'asc')
            ->get();

            // Transform data untuk mobile app
            $productsFormatted = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'nama_barang' => $product->nama_barang,
                    'foto_barang' => $product->foto_barang ? url('storage/' . $product->foto_barang) : null,
                    'harga_jual' => $product->harga_jual ? (float) $product->harga_jual : 0,
                    'jumlah_barang' => $product->jumlah_barang,
                    'kategori_barang' => $product->kategori_barang,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data produk berhasil diambil',
                'data' => $productsFormatted,
                'total_products' => $productsFormatted->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by category
     */
    public function category(Request $request, $kategori): JsonResponse
    {
        try {
            $products = IncomingItem::select([
                'id',
                'nama_barang',
                'foto_barang', 
                'harga_jual',
                'jumlah_barang',
                'kategori_barang'
            ])
            ->where('kategori_barang', $kategori)
            ->where('jumlah_barang', '>', 0)
            ->orderBy('nama_barang', 'asc')
            ->get();

            $productsFormatted = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'nama_barang' => $product->nama_barang,
                    'foto_barang' => $product->foto_barang ? url('storage/' . $product->foto_barang) : null,
                    'harga_jual' => $product->harga_jual ? (float) $product->harga_jual : 0,
                    'jumlah_barang' => $product->jumlah_barang,
                    'kategori_barang' => $product->kategori_barang,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Data produk kategori {$kategori} berhasil diambil",
                'data' => $productsFormatted,
                'total_products' => $productsFormatted->count(),
                'kategori' => $kategori
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data produk berdasarkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product detail
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $product = IncomingItem::select([
                'id',
                'nama_barang',
                'foto_barang', 
                'harga_jual',
                'jumlah_barang',
                'kategori_barang',
                'tanggal_masuk_barang',
                'lokasi_rak_barang'
            ])
            ->where('id', $id)
            ->first();

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            $productFormatted = [
                'id' => $product->id,
                'nama_barang' => $product->nama_barang,
                'foto_barang' => $product->foto_barang ? url('storage/' . $product->foto_barang) : null,
                'harga_jual' => $product->harga_jual ? (float) $product->harga_jual : 0,
                'jumlah_barang' => $product->jumlah_barang,
                'kategori_barang' => $product->kategori_barang,
                'tanggal_masuk_barang' => $product->tanggal_masuk_barang,
                'lokasi_rak_barang' => $product->lokasi_rak_barang,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Detail produk berhasil diambil',
                'data' => $productFormatted
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products by name
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter pencarian (q) tidak boleh kosong'
                ], 400);
            }

            $products = IncomingItem::select([
                'id',
                'nama_barang',
                'foto_barang', 
                'harga_jual',
                'jumlah_barang',
                'kategori_barang'
            ])
            ->where('nama_barang', 'LIKE', "%{$query}%")
            ->where('jumlah_barang', '>', 0)
            ->orderBy('nama_barang', 'asc')
            ->get();

            $productsFormatted = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'nama_barang' => $product->nama_barang,
                    'foto_barang' => $product->foto_barang ? url('storage/' . $product->foto_barang) : null,
                    'harga_jual' => $product->harga_jual ? (float) $product->harga_jual : 0,
                    'jumlah_barang' => $product->jumlah_barang,
                    'kategori_barang' => $product->kategori_barang,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Hasil pencarian untuk '{$query}'",
                'data' => $productsFormatted,
                'total_products' => $productsFormatted->count(),
                'search_query' => $query
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $categories = IncomingItem::select('kategori_barang')
                ->where('jumlah_barang', '>', 0)
                ->distinct()
                ->orderBy('kategori_barang', 'asc')
                ->pluck('kategori_barang')
                ->filter()
                ->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kategori berhasil diambil',
                'data' => $categories,
                'total_categories' => $categories->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}