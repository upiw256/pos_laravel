<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Expense;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference_no')
                    ->default(fn () => Expense::generateReferenceNo())
                    ->readonly()
                    ->required(),
                Select::make('category')
                    ->options([
                        'Listrik' => 'Listrik',
                        'Air' => 'Air',
                        'Gaji' => 'Gaji',
                        'Sewa' => 'Sewa',
                        'Lain-lain' => 'Lain-lain',
                    ])
                    ->required()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('category')->required(),
                    ]),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                DatePicker::make('expense_date')
                    ->default(now())
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
