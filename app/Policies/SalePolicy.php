<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('cashier');
    }
    public function view(User $user, Sale $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager') || $user->hasRole('cashier');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('cashier');
    }
    public function update(User $user, Sale $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('cashier');
    }
    public function delete(User $user, Sale $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('cashier');
    }
    public function restore(User $user, Sale $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Sale $model): bool
    {
        return false;
    }
}
