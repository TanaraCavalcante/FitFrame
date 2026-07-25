<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin && $user->id !== $target->id;
    }
}
