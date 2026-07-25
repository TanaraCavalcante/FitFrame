<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;

class GymPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function update(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function delete(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    /**
     * Usata dai controller "Contenuti" (spec futura): super_admin gestisce
     * qualunque struttura, gym_admin solo la propria.
     */
    public function manage(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin || $user->gym_id === $gym->id;
    }
}
