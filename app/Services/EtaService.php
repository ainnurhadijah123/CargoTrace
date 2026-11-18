<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Default threshold parameters in hours.
     * Can be overridden per group scenario (e.g. Group A: 12h, Group B: 18h, Group C: 24h).
     */
    protected int $lowThreshold;
    protected int $mediumThreshold;
    protected int $highThreshold;

    public function __construct()
    {
        $this->lowThreshold = (int) env('DELAY_THRESHOLD_LOW', 6);
        $this->mediumThreshold = (int) env('DELAY_THRESHOLD_MEDIUM', 24);
        $this->highThreshold = (int) env('DELAY_THRESHOLD_HIGH', 48);
    }

    /**
     * Calculate delay status for a shipment deterministically.
     *
     * @param Shipment $shipment
     * @param Carbon|null $referenceTime
     * @return array
     */
    public function calculateDelay(Shipment $shipment, ?Carbon $referenceTime = null): array
    {
        $expectedArrival = $shipment->expected_arrival_at ?? $shipment->expected_arrival ?? null;

        if (!$expectedArrival) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_days' => 0,
                'severity' => 'NONE',
                'status_label' => 'Schedule Pending',
                'message' => 'Expected arrival time has not been configured.',
                'thresholds' => [
                    'low' => $this->lowThreshold,
                    'medium' => $this->mediumThreshold,
                    'high' => $this->highThreshold,
                ],
            ];
        }

        $expected = Carbon::parse($expectedArrival);

        // Comparison reference point:
        // If DELIVERED, use the actual delivery event timestamp
        // Otherwise, use the reference time or current wall clock
        if (strtoupper($shipment->status) === 'DELIVERED') {
            $deliveryEvent = $shipment->trackingEvents()->where('status', 'DELIVERED')->first();
            $comparisonPoint = $deliveryEvent ? Carbon::parse($deliveryEvent->event_time) : $shipment->updated_at;
        } else {
            $comparisonPoint = $referenceTime ?? now();
        }

        if ($comparisonPoint->greaterThan($expected)) {
            $delayHours = (int) ceil(abs($comparisonPoint->floatDiffInHours($expected)));
            $delayDays = round($delayHours / 24, 1);

            // Determine severity deterministically
            if ($delayHours > $this->mediumThreshold) {
                $severity = 'HIGH';
            } elseif ($delayHours > $this->lowThreshold) {
                $severity = 'MEDIUM';
            } else {
                $severity = 'LOW';
            }

            return [
                'is_delayed' => true,
                'delay_hours' => $delayHours,
                'delay_days' => $delayDays,
                'severity' => $severity,
                'status_label' => "Delayed ({$severity})",
                'message' => "Shipment is delayed by {$delayHours} hours ({$delayDays} days) past expected arrival ({$expected->format('Y-m-d H:i')}).",
                'expected_arrival' => $expected->toIso8601String(),
                'comparison_time' => $comparisonPoint->toIso8601String(),
                'thresholds' => [
                    'low' => $this->lowThreshold,
                    'medium' => $this->mediumThreshold,
                    'high' => $this->highThreshold,
                ],
            ];
        }

        // On time
        $remainingHours = (int) floor($expected->floatDiffInHours($comparisonPoint));
        return [
            'is_delayed' => false,
            'delay_hours' => 0,
            'delay_days' => 0,
            'severity' => 'NONE',
            'status_label' => 'On Time',
            'message' => "Shipment is on schedule. Expected arrival in {$remainingHours} hours ({$expected->format('Y-m-d H:i')}).",
            'expected_arrival' => $expected->toIso8601String(),
            'comparison_time' => $comparisonPoint->toIso8601String(),
            'thresholds' => [
                'low' => $this->lowThreshold,
                'medium' => $this->mediumThreshold,
                'high' => $this->highThreshold,
            ],
        ];
    }
}
