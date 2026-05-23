<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryStock;
use Illuminate\Auth\Access\Response;

class InventoryStockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function view(User $user, InventoryStock $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function update(User $user, InventoryStock $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function delete(User $user, InventoryStock $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function restore(User $user, InventoryStock $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, InventoryStock $model): bool
    {
        return false;
    }
}
