<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Harga</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #e2e8f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(5, 1fr); /* 5 kolom untuk A4 horizontal/vertical yang cukup padat */
            gap: 15px;
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
            background: #fff;
            padding: 10mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .tag-box {
            border: 2px dashed #cbd5e1;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .store-name {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .product-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.2;
            word-wrap: break-word;
        }
        .price {
            font-size: 18px;
            font-weight: 900;
            color: #000;
            margin-bottom: 8px;
        }
        .barcode-svg {
            width: 100%;
            height: 40px;
            margin-bottom: 5px;
        }
        .sku {
            font-size: 9px;
            color: #475569;
        }
        
        /* Print Specifics */
        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                max-width: 100%;
                padding: 5mm;
                gap: 5mm;
            }
            .tag-box {
                border: 1px dashed #000;
            }
            /* Hide print button when printing */
            .no-print {
                display: none !important;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <div style="text-align: center; margin-bottom: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; font-weight: bold; cursor: pointer; background: #6366f1; color: white; border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(99,102,241,0.3);">
            🖨️ Cetak Halaman Ini
        </button>
        <p style="color: #64748b; font-size: 14px; margin-top: 10px;">Gunakan kertas A4. Sesuaikan skala di pengaturan cetak browser Anda.</p>
    </div>

    <div class="container">
        @foreach($products as $product)
            @php
                // Jika produk tidak punya barcode sendiri, gunakan SKU. Jika tidak, "0000" fallback.
                $barcodeVal = $product->barcode ?: ($product->sku ?: '0000000');
            @endphp
            <div class="tag-box">
                <div class="store-name">{{ \App\Models\Setting::get('store_name', 'POS Store') }}</div>
                <div class="product-name">{{ $product->name }}</div>
                <div class="price">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</div>
                
                <svg class="barcode-svg"
                     data-format="CODE128"
                     data-value="{{ $barcodeVal }}"
                     data-text="{{ $barcodeVal }}"
                     data-fontoptions="bold">
                </svg>
            </div>
        @endforeach
    </div>

    <script>
        // Init semua barcode di halaman
        JsBarcode(".barcode-svg").init({
            width: 1.5,
            height: 40,
            fontSize: 12,
            margin: 0,
            displayValue: true
        });
        
        // Auto show print dialog after barcodes fully render
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
