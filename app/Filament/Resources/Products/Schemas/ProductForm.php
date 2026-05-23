<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Informasi Produk')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Produk')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('description')
                                            ->label('Deskripsi')
                                            ->rows(5),
                                    ]),
                                Section::make('Identitas & Status')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('sku')
                                                    ->label('SKU')
                                                    ->unique(ignoreRecord: true),
                                                TextInput::make('barcode')
                                                    ->label('Barcode')
                                                    ->unique(ignoreRecord: true),
                                            ]),
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'active' => 'Aktif',
                                                'inactive' => 'Non-Aktif',
                                            ])
                                            ->default('active')
                                            ->required(),
                                    ]),
                            ])->columnSpan(2),
                        Group::make()
                            ->schema([
                                Section::make('Relasi & Satuan')
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('brand_id')
                                            ->label('Merk')
                                            ->relationship('brand', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Select::make('unit_id')
                                            ->label('Satuan')
                                            ->relationship('unit', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),
                                Section::make('Media')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Foto Produk')
                                            ->image()
                                            ->directory('products'),
                                    ]),
                                Section::make('Varian & Harga')
                                    ->schema([
                                        Toggle::make('is_variant')
                                            ->label('Produk ini memiliki varian?')
                                            ->live(),
                                        
                                        // Harga untuk Produk Tanpa Varian
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('cost_price')
                                                    ->label('Harga Pokok (HPP)')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required(fn ($get) => !$get('is_variant')),
                                                TextInput::make('sell_price')
                                                    ->label('Harga Jual')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required(fn ($get) => !$get('is_variant')),
                                            ])
                                            ->visible(fn ($get) => !$get('is_variant')),

                                        // Repeater untuk Produk Dengan Varian
                                        \Filament\Forms\Components\Repeater::make('variants')
                                            ->relationship()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nama Varian (Contoh: Merah, XL)')
                                                    ->required(),
                                                TextInput::make('sku')
                                                    ->label('SKU Varian'),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('cost_price')
                                                            ->label('HPP Varian')
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->required(),
                                                        TextInput::make('sell_price')
                                                            ->label('Harga Jual Varian')
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->required(),
                                                    ]),
                                                TextInput::make('barcode'),
                                            ])
                                            ->visible(fn ($get) => $get('is_variant'))
                                            ->columns(1)
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                                    ]),
                            ])->columnSpan(1),
                    ]),
            ]);
    }
}
