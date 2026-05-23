<?php

namespace App\Filament\Widgets;

use App\Models\InventoryStock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $lowStockCount = InventoryStock::whereColumn('quantity', '<=', 'min_stock')->count();
        $totalStockValue = \DB::table('inventory_stocks')
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->select(\DB::raw('SUM(inventory_stocks.quantity * products.cost_price) as total_value'))
            ->value('total_value');

        return [
            Stat::make('Produk Perlu Restock', $lowStockCount)
                ->description('Jumlah produk di bawah stok minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
            Stat::make('Total Nilai Inventaris', 'Rp ' . number_format($totalStockValue, 0, ',', '.'))
                ->description('Total modal barang tersimpan')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
