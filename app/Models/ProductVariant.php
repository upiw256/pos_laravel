<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'barcode',
        'cost_price', 'sell_price', 'discount_price',
    ];

    protected $casts = [
        'cost_price'     => 'decimal:2',
        'sell_price'     => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    /**
     * Returns the effective selling price (discount if available, else normal).
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price && $this->discount_price > 0
            ? $this->discount_price
            : $this->sell_price);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'variant_id');
    }

    public function currentPrice()
    {
        return $this->hasOne(ProductPrice::class, 'variant_id')->latestOfMany();
    }
}
