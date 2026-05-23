<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('General Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('reference_no')
                                            ->label('PO Number')
                                            ->required()
                                            ->default(fn () => \App\Models\Purchase::generateReferenceNo())
                                            ->readonly(),
                                        Select::make('supplier_id')
                                            ->relationship('supplier', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('purchase_date')
                                            ->default(now())
                                            ->required(),
                                        DatePicker::make('expected_date'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'ordered' => 'Ordered',
                                                'partial' => 'Partial',
                                                'received' => 'Received',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                        Select::make('payment_status')
                                            ->options([
                                                'unpaid' => 'Unpaid',
                                                'partial' => 'Partial',
                                                'paid' => 'Paid',
                                            ])
                                            ->default('unpaid')
                                            ->required(),
                                    ]),
                            ])->columnSpan(2),
                        Section::make('Summary')
                            ->schema([
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->readonly()
                                    ->prefix('Rp')
                                    ->default(0),
                                TextInput::make('tax_amount')
                                    ->label('Tax (PPN)')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotal($set, $get))
                                    ->prefix('Rp')
                                    ->default(0),
                                TextInput::make('discount_amount')
                                    ->label('Discount')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotal($set, $get))
                                    ->prefix('Rp')
                                    ->default(0),
                                TextInput::make('total_amount')
                                    ->numeric()
                                    ->readonly()
                                    ->prefix('Rp')
                                    ->weight('bold')
                                    ->default(0),
                                Hidden::make('user_id')
                                    ->default(auth()->id()),
                            ])->columnSpan(1),
                        Section::make('Purchase Items')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Product')
                                                    ->relationship('product', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, $state) {
                                                        $product = Product::find($state);
                                                        if ($product) {
                                                            $set('unit_cost', $product->cost_price);
                                                        }
                                                    }),
                                                TextInput::make('quantity_ordered')
                                                    ->label('Qty')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemTotals($set, $get)),
                                                TextInput::make('unit_cost')
                                                    ->label('Unit Cost')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemTotals($set, $get)),
                                                Placeholder::make('item_total')
                                                    ->label('Total')
                                                    ->content(fn (Get $get) => 'Rp ' . number_format($get('quantity_ordered') * $get('unit_cost'), 2)),
                                            ]),
                                    ])
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotal($set, $get))
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function updateItemTotals(Set $set, Get $get): void
    {
        // This is mainly for UI updates if we had a subtotal per item field
    }

    public static function updateTotal(Set $set, Get $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += ($item['quantity_ordered'] ?? 0) * ($item['unit_cost'] ?? 0);
        }

        $tax = (float) ($get('tax_amount') ?? 0);
        $discount = (float) ($get('discount_amount') ?? 0);
        
        $set('subtotal', $subtotal);
        $set('total_amount', $subtotal + $tax - $discount);
    }
}
