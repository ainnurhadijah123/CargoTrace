<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // customers are scoped to their own rows in the controller
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $user->isStaff() || $shipment->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}
