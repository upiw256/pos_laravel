<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Ref')
                    ->searchable(),
                TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('right'),
                TextColumn::make('description')
                    ->limit(30),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Listrik' => 'Listrik',
                        'Air' => 'Air',
                        'Gaji' => 'Gaji',
                        'Sewa' => 'Sewa',
                        'Lain-lain' => 'Lain-lain',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
