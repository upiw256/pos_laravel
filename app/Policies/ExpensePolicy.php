<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager');
    }
    public function view(User $user, Expense $model): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }
    public function update(User $user, Expense $model): bool
    {
        return $user->hasRole('super-admin');
    }
    public function delete(User $user, Expense $model): bool
    {
        return $user->hasRole('super-admin');
    }
    public function restore(User $user, Expense $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Expense $model): bool
    {
        return false;
    }
}
