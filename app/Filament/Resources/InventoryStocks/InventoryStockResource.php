<?php

namespace App\Filament\Resources\InventoryStocks;

use App\Filament\Resources\InventoryStocks\Pages\ManageInventoryStocks;
use App\Models\InventoryStock;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\InventoryStocks\Schemas\InventoryStockForm;
use App\Filament\Resources\InventoryStocks\Tables\InventoryStocksTable;
use Filament\Support\Icons\Heroicon;

class InventoryStockResource extends Resource
{
    protected static ?string $model = InventoryStock::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return InventoryStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryStocksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInventoryStocks::route('/'),
        ];
    }
}
