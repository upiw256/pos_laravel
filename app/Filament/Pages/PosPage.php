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
use Livewire\WithPagination;

class PosPage extends Page
{
    use WithPagination;
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

    // ── State ─────────────────────────────────────────────
    public $search = '';
    public $cart = [];
    public $selectedCategory = null;
    public $customer_id = null;
    public $discount_amount = 0;
    public $tax_rate = 11;
    public $payment_method = 'cash';
    public $notes = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    // Cash payment
    public $cash_tendered = 0;

    // Bank / QRIS detail
    public $payment_detail = '';
    
    // Checkout mode
    public $is_checkout_mode = false;

    // Receipt modal
    public $showReceiptModal = false;
    public $lastSale = null;

    // Pilihan bank dan QRIS
    public array $bankOptions = [
        'BCA'       => 'BCA (Bank Central Asia)',
        'BNI'       => 'BNI (Bank Negara Indonesia)',
        'BRI'       => 'BRI (Bank Rakyat Indonesia)',
        'Mandiri'   => 'Bank Mandiri',
        'CIMB'      => 'CIMB Niaga',
        'Permata'   => 'Bank Permata',
        'Danamon'   => 'Bank Danamon',
        'BSI'       => 'BSI (Bank Syariah Indonesia)',
        'Lainnya'   => 'Bank Lainnya',
    ];

    public array $qrisOptions = [
        'GoPay'     => 'GoPay (Gojek)',
        'OVO'       => 'OVO',
        'Dana'      => 'DANA',
        'ShopeePay' => 'ShopeePay',
        'LinkAja'   => 'LinkAja',
        'QRIS'      => 'QRIS (Umum)',
    ];

    // ── Computed Properties ────────────────────────────────
    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function getProductsProperty()
    {
        $search = trim($this->search);

        // if no search and no category selected, return empty paginator
        if (empty($search) && empty($this->selectedCategory)) {
            return Product::whereRaw('1 = 0')->paginate(20);
        }

        return Product::with(['variants', 'category', 'stocks'])
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->where('status', 'active')
            ->when($search, function ($q) use ($search) {
                // 1. Priority: exact barcode or SKU match (uses B-Tree index → O(log N))
                $q->where(function ($inner) use ($search) {
                    $inner->where('barcode', $search)
                        ->orWhere('sku',     $search)
                        // 2. Fallback: PostgreSQL full-text search via GIN index
                        ->orWhereRaw(
                            "to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(sku,'') || ' ' || coalesce(barcode,'')) @@ plainto_tsquery('simple', ?)",
                            [$search]
                        )
                        // 3. Last resort: substring ILIKE for partial prefix (e.g. typing "samp" → "Sampo")
                        ->orWhere('name', 'ilike', "%{$search}%");
                });
            })
            ->orderByRaw(
                $search
                    ? "CASE WHEN barcode = ? OR sku = ? THEN 0 ELSE 1 END, name"
                    : "name",
                $search ? [$search, $search] : []
            )
            ->paginate(20);
    }


    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
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

    public function getChangeAmountProperty()
    {
        if ($this->payment_method !== 'cash') return 0;
        $change = (float) $this->cash_tendered - $this->grand_total;
        return max(0, $change);
    }

    public function getIsCashValidProperty(): bool
    {
        if ($this->payment_method !== 'cash') return true;
        return (float) $this->cash_tendered >= $this->grand_total;
    }

    // ── Quick cash presets ─────────────────────────────────
    public function setCashPreset($amount)
    {
        $this->cash_tendered = $amount;
    }

    // ── Cart ───────────────────────────────────────────────
    public function addToCart($productId, $variantId = null)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $key = $variantId ? "v{$variantId}" : "p{$productId}";

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $price = (float) $product->sell_price;
            $cost  = (float) $product->cost_price;
            $name  = $product->name;

            if ($variantId) {
                $variant = $product->variants()->find($variantId);
                if ($variant) {
                    $price = (float) $variant->sell_price;
                    $cost  = (float) $variant->cost_price;
                    $name .= ' (' . $variant->name . ')';
                }
            }

            $this->cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name'       => $name,
                'price'      => $price,
                'cost'       => $cost,
                'quantity'   => 1,
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

    public function selectCategory($id)
    {
        $this->selectedCategory = $id;
    }

    // ── Checkout ───────────────────────────────────────────
    public function startCheckout()
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }
        $this->is_checkout_mode = true;
    }

    public function cancelCheckout()
    {
        $this->is_checkout_mode = false;
    }

    public function submitCheckout()
    {
        if (!$this->is_checkout_mode) {
            return;
        }

        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }

        // Validasi uang tunai
        if ($this->payment_method === 'cash' && (float) $this->cash_tendered < $this->grand_total) {
            Notification::make()->title('Uang tunai kurang!')->body('Uang yang diberikan tidak mencukupi total pembayaran.')->danger()->send();
            return;
        }

        // Validasi detail bank/qris
        if (in_array($this->payment_method, ['transfer', 'qris']) && empty($this->payment_detail)) {
            $label = $this->payment_method === 'transfer' ? 'bank' : 'QRIS';
            Notification::make()->title("Pilih {$label} terlebih dahulu!")->danger()->send();
            return;
        }

        try {
            $saleData = null;

            DB::transaction(function () use (&$saleData) {
                $changeAmt = $this->payment_method === 'cash'
                    ? max(0, (float) $this->cash_tendered - $this->grand_total)
                    : null;

                $sale = Sale::create([
                    'reference_no'   => Sale::generateReferenceNo(),
                    'user_id'        => auth()->id(),
                    'customer_id'    => $this->customer_id ?: null,
                    'total_price'    => $this->subtotal,
                    'tax_amount'     => $this->tax_amount,
                    'discount_amount'=> $this->discount_amount,
                    'grand_total'    => $this->grand_total,
                    'status'         => 'completed',
                    'payment_method' => $this->payment_method,
                    'payment_detail' => $this->payment_detail ?: null,
                    'cash_tendered'  => $this->payment_method === 'cash' ? (float) $this->cash_tendered : null,
                    'change_amount'  => $changeAmt,
                    'notes'          => $this->notes,
                ]);

                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'unit_price' => $item['price'],
                        'cost_price' => $item['cost'],
                        'quantity'   => $item['quantity'],
                        'subtotal'   => $item['price'] * $item['quantity'],
                    ]);

                    $stock = InventoryStock::where('product_id', $item['product_id'])
                        ->where('variant_id', $item['variant_id'])
                        ->first();

                    if ($stock) {
                        $qtyBefore = $stock->quantity;
                        $stock->decrement('quantity', $item['quantity']);

                        StockMovement::create([
                            'product_id'     => $item['product_id'],
                            'variant_id'     => $item['variant_id'],
                            'type'           => 'out',
                            'quantity'       => $item['quantity'],
                            'stock_after'    => $qtyBefore - $item['quantity'],
                            'reference_type' => Sale::class,
                            'reference_id'   => $sale->id,
                            'user_id'        => auth()->id(),
                            'notes'          => "Penjualan POS: {$sale->reference_no}",
                        ]);
                    }
                }

                $saleData = $sale->load(['customer', 'user', 'items.product']);
            });

            // Simpan data transaksi untuk struk
            $this->lastSale = [
                'id'              => $saleData->id,
                'reference_no'    => $saleData->reference_no,
                'created_at'      => $saleData->created_at->format('d/m/Y H:i:s'),
                'cashier'         => $saleData->user->name,
                'customer'        => $saleData->customer?->name ?? 'Walk-in Customer',
                'payment_method'  => $saleData->payment_method,
                'payment_detail'  => $saleData->payment_detail,
                'cash_tendered'   => $saleData->cash_tendered,
                'change_amount'   => $saleData->change_amount,
                'subtotal'        => (float) $saleData->total_price,
                'tax_amount'      => (float) $saleData->tax_amount,
                'discount_amount' => (float) $saleData->discount_amount,
                'grand_total'     => (float) $saleData->grand_total,
                'items'           => $saleData->items->map(fn($i) => [
                    'name'     => $i->product->name,
                    'quantity' => $i->quantity,
                    'price'    => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ])->toArray(),
            ];

            $this->showReceiptModal = true;
            $this->reset(['cart', 'customer_id', 'discount_amount', 'notes', 'cash_tendered', 'payment_detail', 'is_checkout_mode']);

        } catch (\Exception $e) {
            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
        }
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->lastSale = null;
    }
}
