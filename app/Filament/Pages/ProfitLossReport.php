<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class ProfitLossReport extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static \UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Laba Rugi';
    protected string $view = 'filament.pages.profit-loss-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super-admin') || 
               auth()->user()->hasRole('manager');
    }

    public $fromDate;
    public $toDate;

    public function mount()
    {
        $this->fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    protected function getViewData(): array
    {
        $sales = Sale::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->sum('grand_total');

        $tax = Sale::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->sum('tax_amount');

        $costOfGoodsSold = SaleItem::whereHas('sale', function ($q) {
            $q->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
              ->where('status', 'completed');
        })->sum(\DB::raw('quantity * cost_price'));

        $expenses = Expense::whereBetween('expense_date', [$this->fromDate, $this->toDate])
            ->sum('amount');

        $grossProfit = $sales - $tax - $costOfGoodsSold;
        $netProfit = $grossProfit - $expenses;

        return [
            'totalSales' => $sales,
            'taxAmount' => $tax,
            'cogs' => $costOfGoodsSold,
            'totalExpenses' => $expenses,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
        ];
    }
}
