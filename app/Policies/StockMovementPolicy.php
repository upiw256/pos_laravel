<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StockMovement;
use Illuminate\Auth\Access\Response;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function view(User $user, StockMovement $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function update(User $user, StockMovement $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function delete(User $user, StockMovement $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function restore(User $user, StockMovement $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, StockMovement $model): bool
    {
        return false;
    }
}
