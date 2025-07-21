<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of active shipping methods
     */
    public function index(Request $request)
    {
        try {
            $shippingMethods = ShippingMethod::active()
                                           ->orderBy('price', 'asc')
                                           ->get();

            return response()->json([
                'status' => 'success',
                'data' => $shippingMethods
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil metode pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }
}
