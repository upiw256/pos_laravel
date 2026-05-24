<?php

namespace App\Filament\Resources\InventoryStocks\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\IconColumn;

class InventoryStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant.name')
                    ->label('Variant')
                    ->placeholder('-'),
                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->sortable()
                    ->alignment('center')
                    ->color(fn ($state, $record) => $state <= $record->min_stock ? 'danger' : 'success')
                    ->weight('bold'),
                TextColumn::make('min_stock')
                    ->label('Min Stock')
                    ->sortable()
                    ->alignment('center'),
                IconColumn::make('stock_status')
                    ->label('Alert')
                    ->icon(fn ($record) => $record->quantity <= $record->min_stock ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->quantity <= $record->min_stock ? 'danger' : 'success'),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
