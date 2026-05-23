<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\StockMovement;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;

class PosPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected string $view = 'filament.pages.pos-page';
    protected static ?string $title = 'Kasir POS';
    protected static ?string $navigationLabel = 'Kasir (POS)';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super-admin') || 
               auth()->user()->hasRole('manager') || 
               auth()->user()->hasRole('cashier');
    }

    public $search = '';
    public $cart = [];
    public $selectedCategory = null;
    public $customer_id = null;
    public $discount_amount = 0;
    public $tax_rate = 11; // Default PPN 11%
    public $payment_method = 'cash';
    public $notes = '';

    public function mount()
    {
        // Load session cart if exists (optional)
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function getProductsProperty()
    {
        return Product::with(['variants', 'category'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")->orWhere('sku', 'ilike', "%{$this->search}%"))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->where('status', 'active')
            ->limit(20)
            ->get();
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    public function addToCart($productId, $variantId = null)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $key = $variantId ? "v{$variantId}" : "p{$productId}";

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $price = (float) $product->sell_price;
            $cost = (float) $product->cost_price;
            $name = $product->name;

            if ($variantId) {
                $variant = $product->variants()->find($variantId);
                if ($variant) {
                    $price = (float) $variant->sell_price;
                    $cost = (float) $variant->cost_price;
                    $name .= " (" . $variant->name . ")";
                }
            }

            $this->cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $name,
                'price' => $price,
                'cost' => $cost,
                'quantity' => 1,
            ];
        }

        Notification::make()->title('Ditambahkan ke keranjang')->success()->send();
    }

    public function updateQty($key, $delta)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += $delta;
            if ($this->cart[$key]['quantity'] <= 0) {
                unset($this->cart[$key]);
            }
        }
    }

    public function removeItem($key)
    {
        unset($this->cart[$key]);
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function getTaxAmountProperty()
    {
        return ($this->subtotal * $this->tax_rate) / 100;
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotal + $this->tax_amount - $this->discount_amount;
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }

        try {
            DB::transaction(function () {
                $sale = Sale::create([
                    'reference_no' => Sale::generateReferenceNo(),
                    'user_id' => auth()->id(),
                    'customer_id' => $this->customer_id,
                    'total_price' => $this->subtotal,
                    'tax_amount' => $this->tax_amount,
                    'discount_amount' => $this->discount_amount,
                    'grand_total' => $this->grand_total,
                    'status' => 'completed',
                    'payment_method' => $this->payment_method,
                    'notes' => $this->notes,
                ]);

                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'unit_price' => $item['price'],
                        'cost_price' => $item['cost'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);

                    // Update Stok (Deduct)
                    $stock = InventoryStock::where('product_id', $item['product_id'])
                        ->where('variant_id', $item['variant_id'])
                        ->first();

                    if ($stock) {
                        $qtyBefore = $stock->quantity;
                        $stock->decrement('quantity', $item['quantity']);
                        
                        // Record Movement
                        StockMovement::create([
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'type' => 'out',
                            'quantity' => $item['quantity'],
                            'stock_after' => $qtyBefore - $item['quantity'],
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'user_id' => auth()->id(),
                            'notes' => "Penjualan POS: {$sale->reference_no}",
                        ]);
                    }
                }
            });

            Notification::make()->title('Transaksi Berhasil!')->success()->send();
            $this->reset(['cart', 'customer_id', 'discount_amount', 'notes']);

        } catch (\Exception $e) {
            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
        }
    }

    public function selectCategory($id)
    {
        $this->selectedCategory = $id;
    }
}
