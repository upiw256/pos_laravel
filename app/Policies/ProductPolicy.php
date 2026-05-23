<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function view(User $user, Product $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function update(User $user, Product $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function delete(User $user, Product $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function restore(User $user, Product $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Product $model): bool
    {
        return false;
    }
}
