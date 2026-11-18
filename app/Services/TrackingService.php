<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\Location;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    /**
     * Allowed status transitions table (Enforces business rules).
     */
    protected array $allowedTransitions = [
        'CREATED'    => ['BOOKED', 'CANCELLED'],
        'BOOKED'     => ['PICKED_UP', 'CANCELLED'],
        'PICKED_UP'  => ['IN_TRANSIT', 'CANCELLED'],
        'IN_TRANSIT' => ['IN_TRANSIT', 'EXCEPTION', 'DELIVERED'],
        'EXCEPTION'  => ['IN_TRANSIT', 'DELIVERED', 'CANCELLED'],
        'DELIVERED'  => [], // Terminal status
        'CANCELLED'  => [], // Terminal status
    ];

    /**
     * Records a new tracking event and updates the shipment status.
     *
     * @param Shipment $shipment
     * @param array $data
     * @param int|null $userId
     * @return TrackingEvent
     * @throws InvalidArgumentException
     */
    public function recordEvent(Shipment $shipment, array $data, ?int $userId = null): TrackingEvent
    {
        $newStatus = strtoupper($data['status'] ?? $shipment->status);
        $currentStatus = strtoupper($shipment->status);

        // Terminal status check: cannot change if already DELIVERED or CANCELLED
        if (in_array($currentStatus, ['DELIVERED', 'CANCELLED'], true)) {
            throw new InvalidArgumentException("Cannot transition shipment from terminal status '{$currentStatus}'.");
        }

        // Validate allowed status transition
        if ($newStatus !== $currentStatus) {
            $validNext = $this->allowedTransitions[$currentStatus] ?? [];
            if (!in_array($newStatus, $validNext, true)) {
                $allowedList = empty($validNext) ? 'None' : implode(', ', $validNext);
                throw new InvalidArgumentException(
                    "Invalid status transition from '{$currentStatus}' to '{$newStatus}'. Allowed next statuses: {$allowedList}."
                );
            }
        }

        return DB::transaction(function () use ($shipment, $data, $newStatus, $currentStatus, $userId) {
            $resolvedUserId = $userId ?? auth()->id() ?? $shipment->created_by;

            // Coordinate resolution: fallback to Location coordinates if not specified
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;
            $locationId = $data['location_id'] ?? null;

            if (($latitude === null || $longitude === null) && $locationId) {
                $location = Location::find($locationId);
                if ($location) {
                    $latitude = $latitude ?? $location->latitude;
                    $longitude = $longitude ?? $location->longitude;
                }
            }

            $event = $shipment->trackingEvents()->create([
                'status' => $newStatus,
                'location_id' => $locationId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $data['description'] ?? "Status updated to {$newStatus}.",
                'created_by' => $resolvedUserId,
                'event_time' => $data['event_time'] ?? now(),
            ]);

            // Update shipment status if changed
            if ($newStatus !== $currentStatus) {
                $shipment->update(['status' => $newStatus]);

                AuditLog::record(
                    'STATUS_TRANSITION',
                    'Shipment',
                    "Shipment #{$shipment->shipment_number} transitioned from {$currentStatus} to {$newStatus}."
                );

                // Notify customer on key milestones
                if ($shipment->customer) {
                    $notifyType = $newStatus === 'DELIVERED' ? 'SUCCESS' : ($newStatus === 'EXCEPTION' ? 'DANGER' : 'INFO');
                    Notification::send(
                        $shipment->customer,
                        "Shipment Status: {$newStatus}",
                        "Shipment #{$shipment->shipment_number} has been updated to {$newStatus}.",
                        $notifyType,
                        $shipment
                    );
                }
            }

            return $event;
        });
    }

    /**
     * Get allowed next statuses for a shipment
     */
    public function getNextPossibleStatuses(Shipment $shipment): array
    {
        return $this->allowedTransitions[strtoupper($shipment->status)] ?? [];
    }
}
