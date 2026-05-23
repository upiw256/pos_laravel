<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'variant_id', 'type', 'quantity',
        'stock_after', 'reference_type', 'reference_id',
        'notes', 'user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Polymorphic: bisa merujuk ke Purchase, Sale, dll.
     */
    public function reference()
    {
        return $this->morphTo();
    }
}
