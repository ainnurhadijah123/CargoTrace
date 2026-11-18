<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN', 'name' => 'Admin Test']);
        $this->operator = User::factory()->create(['role' => 'OPERATOR', 'name' => 'Operator Test']);
        $this->customer1 = User::factory()->create(['role' => 'CUSTOMER', 'name' => 'Customer One']);
        $this->customer2 = User::factory()->create(['role' => 'CUSTOMER', 'name' => 'Customer Two']);

        $this->carrier = Carrier::create([
            'code' => 'MAERSK',
            'name' => 'Maersk Line Test',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'SIN_TEST',
            'name' => 'Singapore Port',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8403,
        ]);

        $this->destination = Location::create([
            'code' => 'RTM_TEST',
            'name' => 'Rotterdam Port',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9054,
            'longitude' => 4.4666,
        ]);
    }

    public function test_operator_can_create_shipment_with_items_and_initial_tracking(): void
    {
        $service = app(ShipmentService::class);

        $payload = [
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Consumer Electronics',
            'weight_kg' => 1500.50,
            'expected_departure_at' => now(),
            'expected_arrival_at' => now()->addDays(10),
            'items' => [
                ['description' => 'Laptops and Tablets', 'weight_kg' => 800.0, 'quantity' => 100],
                ['description' => 'Audio Equipment', 'weight_kg' => 700.5, 'quantity' => 50],
            ],
        ];

        $shipment = $service->create($payload, $this->operator);

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'customer_id' => $this->customer1->id,
            'status' => 'CREATED',
        ]);

        $this->assertCount(2, $shipment->items);

        // Verify initial tracking event was registered
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'status' => 'CREATED',
        ]);
    }

    public function test_customer_can_only_view_own_shipments(): void
    {
        // Shipment owned by Customer 1
        $shipment1 = Shipment::create([
            'shipment_number' => 'CT-TEST-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
        ]);

        // Shipment owned by Customer 2
        $shipment2 = Shipment::create([
            'shipment_number' => 'CT-TEST-002',
            'customer_id' => $this->customer2->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
        ]);

        // Customer 1 visits their shipment
        $response = $this->actingAs($this->customer1)->get(route('customer.shipments.show', $shipment1->shipment_number));
        $response->assertStatus(200);

        // Customer 1 tries to visit Customer 2's shipment -> 404/403
        $forbiddenResponse = $this->actingAs($this->customer1)->get("/customer/shipments/{$shipment2->shipment_number}");
        $forbiddenResponse->assertStatus(404);
    }

    public function test_admin_can_delete_shipment_in_created_status(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-TEST-DEL',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'CREATED',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.shipments.destroy', $shipment));
        $response->assertRedirect(route('admin.shipments.index'));

        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }

    public function test_cannot_delete_shipment_already_in_transit(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-TEST-NODEL',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.shipments.destroy', $shipment));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
    }
}
