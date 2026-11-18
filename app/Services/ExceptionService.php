<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Services\TrackingService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    public const VALID_TYPES = [
        'CUSTOMS_HOLD',
        'WEATHER_DELAY',
        'PORT_CONGESTION',
        'DAMAGED',
        'MISROUTED',
        'DOCUMENTATION_ISSUE',
    ];

    public const VALID_SEVERITIES = ['LOW', 'MEDIUM', 'HIGH'];
    public const VALID_STATUSES = ['OPEN', 'INVESTIGATING', 'RESOLVED'];

    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Reports an exception on a shipment.
     *
     * @param Shipment $shipment
     * @param array $data
     * @param int|null $userId
     * @return ShipmentException
     * @throws InvalidArgumentException
     */
    public function reportException(Shipment $shipment, array $data, ?int $userId = null): ShipmentException
    {
        $currentStatus = strtoupper($shipment->status);
        if (in_array($currentStatus, ['DELIVERED', 'CANCELLED'], true)) {
            throw new InvalidArgumentException("Cannot report an exception on a shipment with terminal status '{$currentStatus}'.");
        }

        $type = strtoupper($data['type'] ?? '');
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Invalid exception type '{$type}'. Valid types: " . implode(', ', self::VALID_TYPES));
        }

        $severity = strtoupper($data['severity'] ?? 'MEDIUM');
        if (!in_array($severity, self::VALID_SEVERITIES, true)) {
            $severity = 'MEDIUM';
        }

        return DB::transaction(function () use ($shipment, $data, $type, $severity, $userId) {
            $exception = $shipment->exceptions()->create([
                'type' => $type,
                'severity' => $severity,
                'status' => 'OPEN',
                'description' => $data['description'] ?? "Exception reported: {$type}",
                'detected_at' => $data['detected_at'] ?? now(),
            ]);

            // Transition shipment status to EXCEPTION via TrackingService
            $readableType = str_replace('_', ' ', $type);
            $eventDescription = "Alert: {$readableType} exception logged. Severity: {$severity}. " . ($data['description'] ?? '');

            $this->trackingService->recordEvent($shipment, [
                'status' => 'EXCEPTION',
                'description' => $eventDescription,
                'location_id' => $data['location_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ], $userId);

            // Audit log
            AuditLog::record(
                'EXCEPTION_REPORTED',
                'ShipmentException',
                "Exception {$type} (Severity: {$severity}) reported for shipment #{$shipment->shipment_number}."
            );

            // Customer notification
            if ($shipment->customer) {
                Notification::send(
                    $shipment->customer,
                    "Shipment Alert: {$readableType}",
                    "Shipment #{$shipment->shipment_number} encountered an incident: {$readableType}. Our operations team is investigating.",
                    'DANGER',
                    $shipment
                );
            }

            return $exception;
        });
    }

    /**
     * Resolves an open exception.
     *
     * @param ShipmentException $exception
     * @param string|null $resolutionNote
     * @param int|null $userId
     * @return ShipmentException
     */
    public function resolveException(ShipmentException $exception, ?string $resolutionNote = null, ?int $userId = null): ShipmentException
    {
        return DB::transaction(function () use ($exception, $resolutionNote, $userId) {
            $exception->update([
                'status' => 'RESOLVED',
                'resolved_at' => now(),
                'description' => $resolutionNote ? ($exception->description . "\nResolution: " . $resolutionNote) : $exception->description,
            ]);

            $shipment = $exception->shipment;

            // Check if there are any remaining open exceptions
            $hasOtherOpen = $shipment->exceptions()
                ->whereIn('status', ['OPEN', 'INVESTIGATING'])
                ->exists();

            // If no other open exceptions and shipment was EXCEPTION, resume to IN_TRANSIT
            if (!$hasOtherOpen && strtoupper($shipment->status) === 'EXCEPTION') {
                $this->trackingService->recordEvent($shipment, [
                    'status' => 'IN_TRANSIT',
                    'description' => "Exception resolved ({$exception->type}). Shipment resumed regular transit.",
                ], $userId);
            }

            AuditLog::record(
                'EXCEPTION_RESOLVED',
                'ShipmentException',
                "Exception #{$exception->id} ({$exception->type}) for shipment #{$shipment->shipment_number} marked as RESOLVED."
            );

            if ($shipment->customer) {
                Notification::send(
                    $shipment->customer,
                    "Exception Resolved",
                    "The issue regarding shipment #{$shipment->shipment_number} has been resolved. Transit resumed.",
                    'SUCCESS',
                    $shipment
                );
            }

            return $exception;
        });
    }

    /**
     * Update exception status (e.g. to INVESTIGATING)
     */
    public function updateStatus(ShipmentException $exception, string $status, ?string $note = null, ?int $userId = null): ShipmentException
    {
        $status = strtoupper($status);
        if ($status === 'RESOLVED') {
            return $this->resolveException($exception, $note, $userId);
        }

        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid exception status '{$status}'.");
        }

        $exception->update(['status' => $status]);

        AuditLog::record(
            'EXCEPTION_UPDATED',
            'ShipmentException',
            "Exception #{$exception->id} status changed to {$status}."
        );

        return $exception;
    }
}
