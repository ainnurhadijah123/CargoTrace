<?php

namespace App\Policies;

use App\Models\Carrier;
use App\Models\User;

class CarrierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Carrier $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Carrier $model): bool
    {
        return $user->isAdmin();
    }
}
