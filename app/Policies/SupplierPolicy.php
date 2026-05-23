<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function view(User $user, Supplier $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('inventory-manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function update(User $user, Supplier $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function delete(User $user, Supplier $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('inventory-manager');
    }
    public function restore(User $user, Supplier $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Supplier $model): bool
    {
        return false;
    }
}
