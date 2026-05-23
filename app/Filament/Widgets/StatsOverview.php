<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();

        // 1. Total Penjualan Hari Ini
        $salesToday = Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('grand_total');
        
        // 2. Total Profit Hari Ini (Subtotal - (Qty * Cost))
        $profitToday = SaleItem::whereHas('sale', function($q) use ($today) {
            $q->whereDate('created_at', $today)->where('status', 'completed');
        })->sum(\DB::raw('subtotal - (quantity * cost_price)'));

        // 3. Total Pengeluaran Hari Ini
        $expensesToday = Expense::whereDate('expense_date', $today)->sum('amount');

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($salesToday, 0, ',', '.'))
                ->description('Total pendapatan kotor')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Laba Bersih Hari Ini', 'Rp ' . number_format($profitToday - $expensesToday, 0, ',', '.'))
                ->description('Pendapatan - (HPP + Biaya)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($expensesToday, 0, ',', '.'))
                ->description('Biaya operasional tercatat')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
