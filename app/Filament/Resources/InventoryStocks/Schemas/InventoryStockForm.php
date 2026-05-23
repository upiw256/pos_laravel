<?php

namespace App\Filament\Resources\InventoryStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabledOn('edit'),
                Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit'),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('min_stock')
                    ->label('Minimal Stock')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
