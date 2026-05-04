<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\InvestmentCategory;
use App\Models\Filament;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Investment;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(['email' => 'admin@printshop.local'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        $operator1 = User::firstOrCreate(['email' => 'operator1@printshop.local'], [
            'name' => 'Jane Operator',
            'password' => Hash::make('password'),
            'role' => 'operator'
        ]);

        // 2. Investment Categories
        $categories = ['Printers', 'Filaments', 'Packaging Materials', 'Electricity', 'Tools & Maintenance', 'Other'];
        foreach ($categories as $i => $cat) {
            InvestmentCategory::firstOrCreate(['name' => $cat], [
                'type' => $i === 0 ? 'equipment' : ($i === 3 ? 'utility' : 'consumable')
            ]);
        }

        // 3. Filaments
        $filaments = [
            ['brand' => 'Bambu', 'name' => 'Bambu Matte', 'material' => 'pla', 'color_name' => 'Charcoal', 'color_hex' => '#333333', 'price_per_kg' => 24.99],
            ['brand' => 'eSUN', 'name' => 'eSUN PLA+', 'material' => 'pla', 'color_name' => 'Fire Engine Red', 'color_hex' => '#CE1126', 'price_per_kg' => 19.99],
            ['brand' => 'Polymaker', 'name' => 'PolyLite PETG', 'material' => 'petg', 'color_name' => 'Clear', 'color_hex' => '#FFFFFF', 'price_per_kg' => 22.50]
        ];
        
        foreach ($filaments as $f) {
            Filament::firstOrCreate(['name' => $f['name']], $f);
        }

        // 4. Products
        $filament1 = Filament::first();
        $filament2 = Filament::skip(1)->first();
        
        $products = [
            ['name' => 'Articulated Dragon', 'material' => 'pla', 'filament_id' => $filament1->id ?? null, 'weight_grams' => 120, 'print_time_minutes' => 360, 'price' => 25.00],
            ['name' => 'Headphone Stand', 'material' => 'petg', 'filament_id' => $filament2->id ?? null, 'weight_grams' => 210, 'print_time_minutes' => 450, 'price' => 35.00],
            ['name' => 'Vase Spiral', 'material' => 'pla', 'filament_id' => $filament1->id ?? null, 'weight_grams' => 60, 'print_time_minutes' => 120, 'price' => 15.00],
            ['name' => 'Cable Organizer (5 Pack)', 'material' => 'petg', 'filament_id' => null, 'color_name' => 'Black', 'color_hex' => '#000000', 'weight_grams' => 45, 'print_time_minutes' => 90, 'price' => 12.00]
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['name' => $p['name']], $p);
        }

        // 5. Orders & Investments via Factories in full project (Using basic seeded here)
        if (Order::count() === 0) {
            for ($i = 1; $i <= 5; $i++) {
                $order = Order::create([
                    'order_number' => 'ORD-' . date('Ym') . '-000' . $i,
                    'customer_name' => 'Customer ' . $i,
                    'status' => 'delivered',
                    'total_price' => 0,
                    'estimated_print_minutes' => 0,
                    'created_by' => $operator1->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 30))
                ]);

                $p = Product::inRandomOrder()->first();
                $qty = rand(1, 4);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'quantity' => $qty,
                    'unit_price' => $p->price,
                    'print_time_minutes' => $p->print_time_minutes,
                    'weight_grams' => $p->weight_grams,
                    'color_name' => collect([null, $p->color_name])->filter()->first()
                ]);

                $order->update([
                    'total_price' => $p->price * $qty,
                    'estimated_print_minutes' => $p->print_time_minutes * $qty
                ]);
            }
        }
    }
}
