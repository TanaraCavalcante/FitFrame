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

    /**
     * NOTA: questa ability è valida solo per target gym_admin (usata da GymAdminController).
     * Non riutilizzarla per azioni su un target super_admin: servirà un check dedicato
     * quando verrà implementato SuperAdminController (Task 10).
     */
    public function update(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin && $target->role === UserRole::GymAdmin;
    }

    /**
     * NOTA: questa ability è valida solo per target gym_admin (usata da GymAdminController).
     * Non riutilizzarla per azioni su un target super_admin: servirà un check dedicato
     * quando verrà implementato SuperAdminController (Task 10).
     */
    public function delete(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin
            && $target->role === UserRole::GymAdmin
            && $user->id !== $target->id;
    }
}
