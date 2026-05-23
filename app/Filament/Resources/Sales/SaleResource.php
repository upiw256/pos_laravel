<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Sales\Pages\ManageSales;
use App\Models\Sale;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\Sales\Tables\SalesTable;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Transaksi';
    
    protected static ?string $navigationLabel = 'Riwayat Penjualan';

    public static function table(Table $table): Table
    {
        return SalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSales::route('/'),
        ];
    }
}
