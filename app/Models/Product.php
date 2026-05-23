<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'unit_id', 'name', 'slug',
        'sku', 'barcode', 'description', 'image', 'is_variant',
        'status', 'cost_price', 'sell_price',
    ];

    protected $casts = [
        'is_variant'  => 'boolean',
        'cost_price'  => 'decimal:2',
        'sell_price'  => 'decimal:2',
    ];

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
