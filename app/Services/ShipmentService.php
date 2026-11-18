<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

/**
 * Service Layer for Shipment Management.
 * Encapsulates all shipment lifecycle, creation, items, and business validation.
 */
class ShipmentService
{
    public function create(array $data, User $actor): Shipment
    {
        return DB::transaction(function () use ($data, $actor) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $shipmentNumber = $this->generateNumber();

            // Set default status to CREATED
            $data['shipment_number'] = $shipmentNumber;
            $data['created_by'] = $actor->id;
            $data['status'] = 'CREATED';

            if (!isset($data['shipping_cost']) || empty($data['shipping_cost'])) {
                $weight = (float) ($data['weight_kg'] ?? 1);
                $data['shipping_cost'] = max(50000, (int) ($weight * 25000));
            }

            if (!isset($data['currency'])) {
                $data['currency'] = 'IDR';
            }

            $shipment = Shipment::create($data);

            if (!empty($items)) {
                $shipment->items()->createMany($items);
            } else {
                $shipment->items()->create([
                    'cargo_type' => $data['cargo_type'] ?? 'General Cargo',
                    'description' => 'Standard shipment package',
                    'weight_kg' => $data['weight_kg'] ?? 10.00,
                    'quantity' => 1,
                ]);
            }

            // Create initial tracking event
            $shipment->trackingEvents()->create([
                'status' => 'CREATED',
                'location_id' => $shipment->origin_id,
                'description' => 'Shipment order created and registered in the system.',
                'created_by' => $actor->id,
                'event_time' => now(),
            ]);

            // Audit log
            AuditLog::record('CREATED', 'Shipment', "Shipment #{$shipment->shipment_number} was created by {$actor->name}.");

            // Customer notification
            if ($customer = User::find($shipment->customer_id)) {
                Notification::send(
                    $customer,
                    'Shipment Created',
                    "Your shipment #{$shipment->shipment_number} has been created and registered.",
                    'INFO',
                    $shipment
                );
            }

            return $shipment;
        });
    }

    public function update(Shipment $shipment, array $data, ?User $actor = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $shipment->update($data);

            if ($items !== null) {
                $shipment->items()->delete();
                $shipment->items()->createMany($items);
            }

            $actorName = $actor ? $actor->name : 'System';
            AuditLog::record('UPDATED', 'Shipment', "Shipment #{$shipment->shipment_number} details updated by {$actorName}.");

            return $shipment;
        });
    }

    public function delete(Shipment $shipment, ?User $actor = null): bool
    {
        return DB::transaction(function () use ($shipment, $actor) {
            $shipmentNumber = $shipment->shipment_number;
            $actorName = $actor ? $actor->name : 'System';

            $shipment->items()->delete();
            $shipment->trackingEvents()->delete();
            $shipment->exceptions()->delete();
            $deleted = $shipment->delete();

            AuditLog::record('DELETED', 'Shipment', "Shipment #{$shipmentNumber} was deleted by {$actorName}.");

            return (bool) $deleted;
        });
    }

    public function generateNumber(): string
    {
        do {
            $number = 'CT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }
}
