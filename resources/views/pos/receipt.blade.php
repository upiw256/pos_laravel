<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk - {{ $sale->reference_no }}</title>
    <style>
        body { font-family: monospace; font-size: 11px; line-height: 1.3; margin: 0; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 5px; }
        td, th { padding: 2px 0; vertical-align: top; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
    </style>
</head>
<body>
    @php
        $shopName = App\Models\Setting::get('shop_name', 'M-POS ENTERPRISE');
        $shopAddress = App\Models\Setting::get('shop_address', 'Jl. Contoh POS No. 123, Kota');
        $shopPhone = App\Models\Setting::get('shop_phone', '08123456789');
    @endphp

    <div class="text-center font-bold" style="font-size: 14px; margin-bottom: 3px;">{{ strtoupper($shopName) }}</div>
    <div class="text-center" style="margin-bottom: 2px;">{{ $shopAddress }}</div>
    <div class="text-center" style="margin-bottom: 5px;">Telp: {{ $shopPhone }}</div>
    <div class="divider"></div>
    
    <div>No : {{ $sale->reference_no }}</div>
    <div>Tgl: {{ $sale->created_at->format('d/m/Y H:i') }}</div>
    <div>Ksr: {{ $sale->user->name }}</div>
    @if($sale->customer)
    <div>Plg: {{ $sale->customer->name }}</div>
    @endif
    
    <div class="divider"></div>
    
    <table>
        @foreach($sale->items as $item)
        <tr>
            <td colspan="3">{{ $item->product->name }} {{ $item->variant ? '('.$item->variant->name.')' : '' }}</td>
        </tr>
        <tr>
            <td style="width: 30%">{{ $item->quantity }} x </td>
            <td style="width: 35%">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td style="width: 35%" class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    
    <div class="divider"></div>
    
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($sale->total_price, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount_amount > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-right">-{{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td>PPN</td>
            <td class="text-right">{{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="font-bold" style="font-size: 12px;">
            <td style="padding-top: 5px;">TOTAL Rp</td>
            <td class="text-right" style="padding-top: 5px;">{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <table>
        <tr>
            <td>Bayar: {{ strtoupper($sale->payment_method) }}</td>
            <td class="text-right">{{ $sale->payment_detail }}</td>
        </tr>
        @if($sale->payment_method === 'cash')
        <tr>
            <td>Tunai</td>
            <td class="text-right">{{ number_format($sale->cash_tendered, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">{{ number_format($sale->change_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>
    
    <div class="divider"></div>
    
    <div class="text-center" style="margin-top: 15px;">
        Terima Kasih Atas Kunjungan Anda!
    </div>
</body>
</html>
