<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Services\ExceptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ExceptionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Shipment $shipment;
    protected ExceptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ExceptionService::class);
        $this->operator = User::factory()->create(['role' => 'OPERATOR']);
        $customer = User::factory()->create(['role' => 'CUSTOMER']);

        $carrier = Carrier::create(['code' => 'CARRIER_EXC', 'name' => 'Carrier Exc', 'is_active' => true]);
        $origin = Location::create(['code' => 'LOC_A', 'name' => 'Loc A', 'city' => 'City A', 'country' => 'Country A']);
        $destination = Location::create(['code' => 'LOC_B', 'name' => 'Loc B', 'city' => 'City B', 'country' => 'Country B']);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-EXC-TEST',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
        ]);
    }

    public function test_operator_can_report_exception_which_transitions_shipment_status(): void
    {
        $exception = $this->service->reportException($this->shipment, [
            'type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'description' => 'Severe typhoon grounded container transport.',
        ], $this->operator->id);

        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exception->id,
            'type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'status' => 'OPEN',
        ]);

        // Shipment status should automatically transition to EXCEPTION
        $this->assertEquals('EXCEPTION', $this->shipment->fresh()->status);

        // A tracking event with status EXCEPTION must be created
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $this->shipment->id,
            'status' => 'EXCEPTION',
        ]);
    }

    public function test_resolving_exception_resumes_transit_status(): void
    {
        $exception = $this->service->reportException($this->shipment, [
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'MEDIUM',
            'description' => 'Awaiting declaration stamps.',
        ], $this->operator->id);

        $this->assertEquals('EXCEPTION', $this->shipment->fresh()->status);

        // Resolve the exception
        $resolved = $this->service->resolveException($exception, 'Documents cleared by customs broker.', $this->operator->id);

        $this->assertEquals('RESOLVED', $resolved->fresh()->status);
        $this->assertNotNull($resolved->fresh()->resolved_at);

        // Shipment status should resume to IN_TRANSIT
        $this->assertEquals('IN_TRANSIT', $this->shipment->fresh()->status);
    }

    public function test_cannot_report_exception_on_delivered_shipment(): void
    {
        $this->shipment->update(['status' => 'DELIVERED']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->reportException($this->shipment, [
            'type' => 'DAMAGED',
            'severity' => 'LOW',
            'description' => 'Too late to file active exception.',
        ]);
    }
}
