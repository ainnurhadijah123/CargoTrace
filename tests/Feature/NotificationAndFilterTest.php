<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Location $origin;
    protected Location $destination;
    protected Carrier $carrier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'role' => 'ADMIN',
        ]);

        $this->operator = User::factory()->create([
            'email' => 'operator_test@example.com',
            'role' => 'OPERATOR',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer_test@example.com',
            'role' => 'CUSTOMER',
        ]);

        $this->origin = Location::create([
            'name' => 'Port of Singapore',
            'code' => 'SIN_TEST',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8403,
        ]);

        $this->destination = Location::create([
            'name' => 'Port of Rotterdam',
            'code' => 'RTM_TEST',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9054,
            'longitude' => 4.4666,
        ]);

        $this->carrier = Carrier::create([
            'name' => 'Maersk Test Line',
            'code' => 'MSK_TEST',
            'is_active' => true,
        ]);
    }

    public function test_customer_can_mark_notification_as_read(): void
    {
        $notification = Notification::create([
            'user_id' => $this->customer->id,
            'type' => 'INFO',
            'title' => 'Test Notification',
            'message' => 'Shipment departed from Singapore.',
        ]);

        $this->assertFalse($notification->isRead());

        $response = $this->actingAs($this->customer)
            ->post("/notifications/{$notification->id}/read");

        $response->assertStatus(302);
        $this->assertTrue($notification->fresh()->isRead());
    }

    public function test_customer_can_mark_all_notifications_as_read(): void
    {
        Notification::create([
            'user_id' => $this->customer->id,
            'type' => 'INFO',
            'title' => 'Notif 1',
            'message' => 'Message 1',
        ]);

        Notification::create([
            'user_id' => $this->customer->id,
            'type' => 'DANGER',
            'title' => 'Notif 2',
            'message' => 'Message 2',
        ]);

        $this->assertEquals(2, Notification::where('user_id', $this->customer->id)->whereNull('read_at')->count());

        $response = $this->actingAs($this->customer)
            ->post('/notifications/mark-all-read');

        $response->assertStatus(302);
        $this->assertEquals(0, Notification::where('user_id', $this->customer->id)->whereNull('read_at')->count());
    }

    public function test_admin_can_filter_shipments_by_search_and_status(): void
    {
        Shipment::create([
            'shipment_number' => 'CT-FILTER-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'cargo_type' => 'Medical Equipment',
            'weight_kg' => 1500,
            'status' => 'IN_TRANSIT',
        ]);

        Shipment::create([
            'shipment_number' => 'CT-FILTER-002',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'cargo_type' => 'Coffee Beans',
            'weight_kg' => 5000,
            'status' => 'DELIVERED',
        ]);

        // Search by shipment number
        $response = $this->actingAs($this->admin)
            ->get('/admin/shipments?search=CT-FILTER-001');

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-001');
        $response->assertDontSee('CT-FILTER-002');

        // Filter by status
        $responseStatus = $this->actingAs($this->admin)
            ->get('/admin/shipments?status=DELIVERED');

        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('CT-FILTER-002');
        $responseStatus->assertDontSee('CT-FILTER-001');
    }

    public function test_operator_can_access_dashboard_and_create_shipment(): void
    {
        $response = $this->actingAs($this->operator)
            ->get('/operator/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Panel Operasional Pengiriman');

        // Operator create shipment via web route
        $createResponse = $this->actingAs($this->operator)
            ->post('/operator/shipments', [
                'customer_id' => $this->customer->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Automotive Parts',
                'weight_kg' => 2000,
                'expected_departure_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'expected_arrival_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            ]);

        $createResponse->assertStatus(302);
        $this->assertDatabaseHas('shipments', [
            'cargo_type' => 'Automotive Parts',
            'customer_id' => $this->customer->id,
            'status' => 'CREATED',
        ]);
    }

    public function test_operator_and_owner_can_access_print_label(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-PRINT-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'created_by' => $this->operator->id,
            'cargo_type' => 'Industrial Generator',
            'weight_kg' => 3200,
            'status' => 'IN_TRANSIT',
        ]);

        // Operator can access print label via /admin/shipments/{id}/print without 403
        $operatorResponse = $this->actingAs($this->operator)
            ->get("/admin/shipments/{$shipment->id}/print");
        $operatorResponse->assertStatus(200);
        $operatorResponse->assertSee('CT-PRINT-001');

        // Owner customer can access print label
        $customerResponse = $this->actingAs($this->customer)
            ->get("/admin/shipments/{$shipment->id}/print");
        $customerResponse->assertStatus(200);
        $customerResponse->assertSee('CT-PRINT-001');

        // Other customer cannot access
        $otherCustomer = User::factory()->create([
            'email' => 'other_cust@example.com',
            'role' => 'CUSTOMER',
        ]);
        $unauthorizedResponse = $this->actingAs($otherCustomer)
            ->get("/admin/shipments/{$shipment->id}/print");
        $unauthorizedResponse->assertStatus(403);
    }
}
