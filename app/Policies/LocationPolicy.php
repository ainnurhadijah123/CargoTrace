<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Location $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Location $model): bool
    {
        return $user->isAdmin();
    }
}
