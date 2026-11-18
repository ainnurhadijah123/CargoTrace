<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create(['role' => 'OPERATOR']);
        $this->customer = User::factory()->create(['role' => 'CUSTOMER']);

        $this->carrier = Carrier::create(['code' => 'API_CARRIER', 'name' => 'API Carrier', 'is_active' => true]);
        $this->origin = Location::create(['code' => 'API_SIN', 'name' => 'Singapore API Port', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.26, 'longitude' => 103.84]);
        $this->destination = Location::create(['code' => 'API_RTM', 'name' => 'Rotterdam API Port', 'city' => 'Rotterdam', 'country' => 'Netherlands', 'latitude' => 51.9, 'longitude' => 4.46]);
    }

    public function test_api_shipments_requires_authentication(): void
    {
        $response = $this->getJson('/api/shipments');
        $response->assertStatus(401);
    }

    public function test_operator_can_create_shipment_via_api(): void
    {
        $token = $this->operator->createToken('test_token')->plainTextToken;

        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'API Electronics',
            'weight_kg' => 1200.0,
            'expected_departure_at' => now()->toIso8601String(),
            'expected_arrival_at' => now()->addDays(7)->toIso8601String(),
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/shipments', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'CREATED',
                    'cargo_type' => 'API Electronics',
                ],
            ]);
    }

    public function test_customer_cannot_create_shipment_via_api(): void
    {
        $token = $this->customer->createToken('cust_token')->plainTextToken;

        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Forbidden Freight',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/shipments', $payload);

        $response->assertStatus(403);
    }

    public function test_api_add_tracking_event_and_status_advance(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-API-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'CREATED',
        ]);

        $token = $this->operator->createToken('op_token')->plainTextToken;

        // Valid transition from CREATED to BOOKED
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/shipments/{$shipment->id}/tracking", [
                'status' => 'BOOKED',
                'description' => 'Slot confirmed on container ship.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'shipment_status' => 'BOOKED',
                ],
            ]);

        $this->assertEquals('BOOKED', $shipment->fresh()->status);
    }

    public function test_api_public_tracking_endpoint(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-PUB-999',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
        ]);

        $response = $this->getJson("/api/public/track/{$shipment->shipment_number}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'shipment' => [
                        'shipment_number' => 'CT-PUB-999',
                    ],
                ],
            ]);
    }
}
