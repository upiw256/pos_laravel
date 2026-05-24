<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'unit_id', 'name', 'slug',
        'sku', 'barcode', 'description', 'image', 'is_variant',
        'status', 'cost_price', 'sell_price', 'discount_price',
    ];

    protected $casts = [
        'is_variant'     => 'boolean',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function currentPrice()
    {
        return $this->hasOne(ProductPrice::class)->latestOfMany();
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
