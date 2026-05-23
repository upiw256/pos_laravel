<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Brand;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function view(User $user, Brand $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function update(User $user, Brand $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function delete(User $user, Brand $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function restore(User $user, Brand $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Brand $model): bool
    {
        return false;
    }
}
