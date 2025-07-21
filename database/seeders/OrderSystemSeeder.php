<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;
use App\Models\Voucher;
use Carbon\Carbon;

class OrderSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create shipping methods
        $shippingMethods = [
            [
                'name' => 'Standard Delivery',
                'description' => '2-3 business days',
                'price' => 15000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 3,
                'icon' => 'local_shipping',
                'is_active' => true,
            ],
            [
                'name' => 'Express Delivery',
                'description' => 'Same day delivery',
                'price' => 25000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'icon' => 'speed',
                'is_active' => true,
            ],
            [
                'name' => 'Free Delivery',
                'description' => '5-7 business days',
                'price' => 0,
                'estimated_days_min' => 5,
                'estimated_days_max' => 7,
                'icon' => 'card_giftcard',
                'is_active' => true,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
        }

        // Create vouchers
        $vouchers = [
            [
                'code' => 'DISCOUNT10',
                'name' => 'Diskon 10%',
                'description' => 'Diskon 10% untuk semua pembelian minimum Rp 100.000',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount' => 50000,
                'min_purchase' => 100000,
                'usage_limit' => 100,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now()->subDays(1),
                'valid_until' => Carbon::now()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Gratis Ongkir',
                'description' => 'Gratis ongkos kirim untuk pembelian minimum Rp 200.000',
                'discount_type' => 'free_shipping',
                'discount_value' => 0,
                'max_discount' => null,
                'min_purchase' => 200000,
                'usage_limit' => 50,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now()->subDays(1),
                'valid_until' => Carbon::now()->addMonths(2),
            ],
            [
                'code' => 'NEWUSER25',
                'name' => 'Diskon Member Baru',
                'description' => 'Diskon Rp 25.000 untuk member baru',
                'discount_type' => 'fixed_amount',
                'discount_value' => 25000,
                'max_discount' => null,
                'min_purchase' => 150000,
                'usage_limit' => null,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now()->subDays(1),
                'valid_until' => Carbon::now()->addMonths(6),
            ],
            [
                'code' => 'EXPIRED',
                'name' => 'Voucher Expired',
                'description' => 'Voucher yang sudah expired untuk testing',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount' => 30000,
                'min_purchase' => 50000,
                'usage_limit' => 10,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now()->subMonths(2),
                'valid_until' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['code' => $voucher['code']],
                $voucher
            );
        }
    }
}
