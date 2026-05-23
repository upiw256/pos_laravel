<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'reference_no', 'supplier_id', 'user_id',
        'purchase_date', 'expected_date', 'status',
        'payment_status', 'subtotal', 'discount_amount',
        'tax_amount', 'total_amount', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expected_date' => 'date',
        'subtotal'       => 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'total_amount'   => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    protected static function booted()
    {
        static::updated(function ($purchase) {
            // Trigger receiving process only when status changes to 'received'
            if ($purchase->isDirty('status') && $purchase->status === 'received' && $purchase->getOriginal('status') !== 'received') {
                $purchase->processReceiving();
            }
        });
    }

    /**
     * Proses penerimaan barang: update stok dan catat mutasi
     */
    public function processReceiving(): void
    {
        \DB::transaction(function () {
            foreach ($this->items as $item) {
                // Update atau Buat Database Stok
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $qtyBefore = (float) $stock->quantity;
                $qtyReceived = (float) $item->quantity_ordered;
                $newUnitCost = (float) $item->unit_cost;

                // --- LOGIKA HPP: MOVING AVERAGE ---
                // Formula: ((Qty Lama * HPP Lama) + (Qty Baru * Harga Baru)) / (Qty Total)
                $target = $item->variant_id ? $item->variant : $item->product;
                $currentHpp = (float) $target->cost_price;
                
                if ($qtyBefore + $qtyReceived > 0) {
                    $newHpp = (($qtyBefore * $currentHpp) + ($qtyReceived * $newUnitCost)) / ($qtyBefore + $qtyReceived);
                    $target->update(['cost_price' => $newHpp]);
                }

                $stock->increment('quantity', $qtyReceived);

                // Update jumlah diterima di item
                $item->update(['quantity_received' => $qtyReceived]);

                // Catat Kartu Stok (Stock Movement)
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'type' => 'in',
                    'quantity' => $qtyReceived,
                    'stock_after' => $qtyBefore + $qtyReceived,
                    'reference_type' => self::class,
                    'reference_id' => $this->id,
                    'user_id' => $this->user_id,
                    'notes' => "Penerimaan barang PO: {$this->reference_no} (HPP diupdate ke Average)",
                ]);
            }
        });
    }

    /**
     * Auto-generate reference number: PO-20260522-001
     */
    public static function generateReferenceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PO-{$date}-";
        $lastPurchase = static::where('reference_no', 'like', "{$prefix}%")
            ->orderByDesc('reference_no')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) str_replace($prefix, '', $lastPurchase->reference_no);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }

    /**
     * Hitung ulang total dari items
     */
    public function recalculateTotal(): void
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity_ordered * unit_cost'));
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->tax_amount;
        $this->saveQuietly();
    }
}
