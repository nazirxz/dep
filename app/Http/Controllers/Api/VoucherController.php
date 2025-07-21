<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    /**
     * Validate voucher code
     */
    public function validate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'voucher_code' => 'required|string|max:50',
                'subtotal' => 'required|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $voucher = Voucher::where('code', $request->voucher_code)
                             ->active()
                             ->available()
                             ->first();

            if (!$voucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode voucher tidak ditemukan atau tidak valid'
                ], 404);
            }

            $validation = $voucher->isValid($request->subtotal);

            if (!$validation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validation['message']
                ], 422);
            }

            $discountAmount = $voucher->calculateDiscount(
                $request->subtotal, 
                $request->shipping_cost ?? 0
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher valid',
                'data' => [
                    'voucher_name' => $voucher->name,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'discount_amount' => $discountAmount,
                    'description' => $voucher->description,
                    'max_discount' => $voucher->max_discount,
                    'min_purchase' => $voucher->min_purchase,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal validasi voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available vouchers
     */
    public function index(Request $request)
    {
        try {
            $vouchers = Voucher::active()
                              ->available()
                              ->orderBy('created_at', 'desc')
                              ->get();

            return response()->json([
                'status' => 'success',
                'data' => $vouchers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data voucher: ' . $e->getMessage()
            ], 500);
        }
    }
}
