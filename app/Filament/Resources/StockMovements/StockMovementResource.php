<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ManageStockMovements;
use App\Models\StockMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\StockMovements\Schemas\StockMovementForm;
use App\Filament\Resources\StockMovements\Tables\StockMovementsTable;
use Filament\Support\Icons\Heroicon;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    
    protected static ?string $navigationLabel = 'Kartu Stok / Mutasi';

    public static function form(Schema $schema): Schema
    {
        return StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockMovements::route('/'),
        ];
    }
}
