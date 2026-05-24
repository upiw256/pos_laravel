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
                Grid::make(1)
                    ->schema([
                        Section::make('Informasi Produk')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Produk')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->columnSpan(1),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->columnSpan(2),
                            ]),
                            
                        Section::make('Identitas, Relasi & Satuan')
                            ->columns(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->unique(ignoreRecord: true),
                                TextInput::make('barcode')
                                    ->label('Barcode')
                                    ->unique(ignoreRecord: true),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Aktif',
                                        'inactive' => 'Non-Aktif',
                                    ])
                                    ->default('active')
                                    ->required(),
                                    
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
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('cost_price')
                                            ->label('Harga Pokok (HPP)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(fn ($get) => !$get('is_variant')),
                                        TextInput::make('sell_price')
                                            ->label('Harga Jual Normal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(fn ($get) => !$get('is_variant')),
                                        TextInput::make('discount_price')
                                            ->label('Harga Diskon (kosongkan jika tidak ada)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->placeholder('Opsional'),
                                    ])
                                    ->visible(fn ($get) => !$get('is_variant')),

                                // Repeater untuk Produk Dengan Varian
                                \Filament\Forms\Components\Repeater::make('variants')
                                    ->relationship()
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nama Varian (Contoh: Merah, XL)')
                                                    ->required()
                                                    ->columnSpan(1),
                                                TextInput::make('sku')
                                                    ->label('SKU Varian')
                                                    ->columnSpan(1),
                                                TextInput::make('cost_price')
                                                    ->label('HPP Varian')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->columnSpan(1),
                                                TextInput::make('sell_price')
                                                    ->label('Harga Jual Normal')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->columnSpan(1),
                                                TextInput::make('discount_price')
                                                    ->label('Harga Diskon')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->placeholder('Opsional')
                                                    ->columnSpan(1),
                                                TextInput::make('barcode')
                                                    ->columnSpan(5),
                                            ])
                                    ])
                                    ->visible(fn ($get) => $get('is_variant'))
                                    ->columns(1)
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                            ]),
                    ]),
            ]);
    }
}
