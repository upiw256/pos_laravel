<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $fillable = [
        'product_id', 'variant_id', 'quantity', 'min_stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Cek apakah stok di bawah minimum
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}
