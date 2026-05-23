<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin');
    }
    public function view(User $user, Role $model): bool
    {
        return $user->hasRole('super-admin');
    }
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }
    public function update(User $user, Role $model): bool
    {
        return $user->hasRole('super-admin');
    }
    public function delete(User $user, Role $model): bool
    {
        return $user->hasRole('super-admin');
    }
    public function restore(User $user, Role $model): bool
    {
        return false;
    }
    public function forceDelete(User $user, Role $model): bool
    {
        return false;
    }
}
