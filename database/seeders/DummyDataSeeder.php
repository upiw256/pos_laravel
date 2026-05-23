<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Role;
use App\Models\Expense;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $roleIds = Role::pluck('id')->toArray();
        if (empty($roleIds)) {
            $roleIds = [null];
        }

        // 10 Users
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => "User " . $faker->name,
                'email' => "user{$i}_" . uniqid() . "@example.com",
                'password' => Hash::make('password'),
                'role_id' => $faker->randomElement($roleIds),
            ]);
        }

        // Master Data (10 each)
        for ($i = 0; $i < 10; $i++) {
            Brand::create([
                'name' => "Brand {$faker->company}", 
                'slug' => Str::slug("Brand {$faker->company}-".uniqid())
            ]);
            
            $cat = "Kategori " . ucfirst($faker->word);
            Category::create([
                'name' => $cat, 
                'slug' => Str::slug($cat)."-".uniqid()
            ]);
            
            Unit::create([
                'name' => "Unit " . strtoupper(Str::random(3)), 
                'short_name' => strtoupper(Str::random(2))
            ]);
            
            Customer::create([
                'name' => $faker->name, 
                'phone' => substr($faker->phoneNumber, 0, 15), 
                'email' => "cust{$i}_" . uniqid() . "@example.com", 
                'address' => substr($faker->address, 0, 100), 
                'total_points' => rand(0, 500), 
                'debt_balance' => 0
            ]);
            
            Supplier::create([
                'name' => $faker->company, 
                'contact_person' => $faker->name, 
                'phone' => substr($faker->phoneNumber, 0, 15), 
                'email' => "supp{$i}_" . uniqid() . "@example.com", 
                'address' => substr($faker->address, 0, 100)
            ]);
        }

        $brandIds = Brand::pluck('id')->toArray();
        $catIds = Category::pluck('id')->toArray();
        $unitIds = Unit::pluck('id')->toArray();

        // 20 Products
        for ($i = 0; $i < 20; $i++) {
            $cost = rand(10, 50) * 1000;
            $sell = $cost + rand(2, 10) * 1000;
            $prod = Product::create([
                'category_id' => $faker->randomElement($catIds),
                'brand_id' => $faker->randomElement($brandIds),
                'unit_id' => $faker->randomElement($unitIds),
                'name' => "Produk " . ucfirst($faker->word) . " {$i}",
                'slug' => Str::slug("Produk " . $faker->word . "-".uniqid()),
                'sku' => "SKU-" . strtoupper(Str::random(6)),
                'barcode' => substr($faker->ean13, 0, 13),
                'description' => substr($faker->sentence, 0, 200),
                'is_variant' => false,
                'status' => 'active',
                'cost_price' => $cost,
                'sell_price' => $sell,
            ]);

            // Add Stock explicitly
            InventoryStock::create([
                'product_id' => $prod->id,
                'variant_id' => null,
                'quantity' => rand(15, 150),
                'min_stock' => rand(5, 10)
            ]);
        }

        $supplierIds = Supplier::pluck('id')->toArray();
        $customerIds = Customer::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        // 10 Purchases
        for ($i = 0; $i < 10; $i++) {
            $purchase = Purchase::create([
                'reference_no' => 'PO-' . strtoupper(Str::random(6)),
                'supplier_id' => $faker->randomElement($supplierIds),
                'user_id' => end($userIds), // Just the last user
                'purchase_date' => now()->subDays(rand(1, 30)),
                'expected_date' => now()->addDays(rand(1, 5)),
                'status' => 'received',
                'payment_status' => 'paid',
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'notes' => substr($faker->sentence, 0, 100)
            ]);
            $cost = rand(1000, 50000);
            $qty = rand(5, 20);
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $faker->randomElement($productIds),
                'unit_cost' => $cost,
                'quantity_ordered' => $qty,
                'quantity_received' => $qty
            ]);
            $purchase->update([
                'subtotal' => $cost * $qty,
                'total_amount' => $cost * $qty
            ]);
        }

        // 10 Sales
        for ($i = 0; $i < 10; $i++) {
            $sale = Sale::create([
                'reference_no' => 'INV-' . strtoupper(Str::random(6)),
                'user_id' => end($userIds),
                'customer_id' => $faker->randomElement($customerIds),
                'total_price' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'grand_total' => 0,
                'status' => 'completed',
                'payment_method' => 'cash',
                'notes' => substr($faker->sentence, 0, 100)
            ]);
            $price = rand(1500, 60000);
            $qty = rand(1, 5);
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $faker->randomElement($productIds),
                'variant_id' => null,
                'unit_price' => $price,
                'cost_price' => $price - 500, 
                'quantity' => $qty,
                'subtotal' => $price * $qty
            ]);
            $sale->update([
                'total_price' => $price * $qty, 
                'grand_total' => $price * $qty
            ]);
        }
        
        // 10 Expenses
        for ($i = 0; $i < 10; $i++) {
            Expense::create([
                'reference_no' => 'EXP-' . strtoupper(Str::random(6)),
                'category' => $faker->randomElement(['Listrik', 'Internet', 'Operasional']),
                'amount' => rand(50, 500) * 1000,
                'expense_date' => now()->subDays(rand(1, 30)),
                'description' => substr($faker->sentence, 0, 200)
            ]);
        }
    }
}
