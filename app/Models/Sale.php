<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference_no', 'user_id', 'customer_id',
        'total_price', 'tax_amount', 'discount_amount',
        'grand_total', 'status', 'payment_method', 'notes',
        'cash_tendered', 'change_amount', 'payment_detail',
    ];

    protected $casts = [
        'total_price'     => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'cash_tendered'   => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Auto-generate reference number: INV-20260522-001
     */
    public static function generateReferenceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";
        $lastSale = static::where('reference_no', 'like', "{$prefix}%")
            ->orderByDesc('reference_no')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) str_replace($prefix, '', $lastSale->reference_no);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
