<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PosReceiptController extends Controller
{
    public function show($id)
    {
        $sale = Sale::with(['user', 'customer', 'items.product', 'items.variant'])->findOrFail($id);

        $pdf = Pdf::loadView('pos.receipt', compact('sale'));
        
        // Thermal receipt width typically 58mm (164pt) or 80mm (226pt)
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait'); 

        return $pdf->stream("Struk-{$sale->reference_no}.pdf");
    }
}
