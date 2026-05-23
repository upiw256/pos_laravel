<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStockMovements extends ManageRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Biasanya mutasi dibuat otomatis dari Purchase/Sale, 
            // tapi bisa tambah CreateAction jika butuh manual adjustment di sini.
        ];
    }
}
