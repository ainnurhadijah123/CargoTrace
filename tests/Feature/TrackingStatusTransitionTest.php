<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TrackingStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create(['role' => 'OPERATOR']);
        $customer = User::factory()->create(['role' => 'CUSTOMER']);

        $carrier = Carrier::create(['code' => 'CARRIER1', 'name' => 'Carrier 1', 'is_active' => true]);
        $origin = Location::create(['code' => 'LOC1', 'name' => 'Loc 1', 'city' => 'City 1', 'country' => 'Country 1']);
        $destination = Location::create(['code' => 'LOC2', 'name' => 'Loc 2', 'city' => 'City 2', 'country' => 'Country 2']);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-LIFECYCLE-01',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'created_by' => $this->operator->id,
            'status' => 'CREATED',
        ]);
    }

    public function test_valid_lifecycle_status_transitions(): void
    {
        $service = app(TrackingService::class);

        // 1. CREATED -> BOOKED
        $service->recordEvent($this->shipment, ['status' => 'BOOKED', 'description' => 'Booking accepted'], $this->operator->id);
        $this->assertEquals('BOOKED', $this->shipment->fresh()->status);

        // 2. BOOKED -> PICKED_UP
        $service->recordEvent($this->shipment, ['status' => 'PICKED_UP', 'description' => 'Cargo picked up'], $this->operator->id);
        $this->assertEquals('PICKED_UP', $this->shipment->fresh()->status);

        // 3. PICKED_UP -> IN_TRANSIT
        $service->recordEvent($this->shipment, ['status' => 'IN_TRANSIT', 'description' => 'Vessel departed'], $this->operator->id);
        $this->assertEquals('IN_TRANSIT', $this->shipment->fresh()->status);

        // 4. IN_TRANSIT -> DELIVERED
        $service->recordEvent($this->shipment, ['status' => 'DELIVERED', 'description' => 'Delivered to consignee'], $this->operator->id);
        $this->assertEquals('DELIVERED', $this->shipment->fresh()->status);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $service = app(TrackingService::class);

        // Cannot skip directly from CREATED to DELIVERED
        $this->expectException(InvalidArgumentException::class);
        $service->recordEvent($this->shipment, ['status' => 'DELIVERED'], $this->operator->id);
    }

    public function test_cannot_transition_from_terminal_status_delivered(): void
    {
        $service = app(TrackingService::class);

        // Fast-forward to DELIVERED
        $this->shipment->update(['status' => 'DELIVERED']);

        // Attempting to change DELIVERED to IN_TRANSIT must throw exception
        $this->expectException(InvalidArgumentException::class);
        $service->recordEvent($this->shipment, ['status' => 'IN_TRANSIT'], $this->operator->id);
    }
}
