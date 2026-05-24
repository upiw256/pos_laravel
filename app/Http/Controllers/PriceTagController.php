<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PriceTagController extends Controller
{
    public function print(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids) || count($ids) == 0) {
            abort(404, 'No products selected.');
        }

        // We fetch the products and also grab exactly 1 active price and barcode
        $products = Product::whereIn('id', $ids)->get();

        return view('print.price-tags', compact('products'));
    }
}
