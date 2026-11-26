<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $admin;
    protected User $customerAin;
    protected User $customerFahmi;
    protected Shipment $shipmentAin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN', 'name' => 'Admin System']);
        $this->operator = User::factory()->create(['role' => 'OPERATOR', 'name' => 'Operator Staff']);
        $this->customerAin = User::factory()->create([
            'role' => 'CUSTOMER',
            'name' => 'Ain Nurhadijah',
            'email' => 'ain@gmail.com',
            'password' => bcrypt('ainnur123'),
        ]);
        $this->customerFahmi = User::factory()->create([
            'role' => 'CUSTOMER',
            'name' => 'Fahmi Fadillah',
            'email' => 'fahmi@gmail.com',
            'password' => bcrypt('fahmi123'),
        ]);

        $carrier = Carrier::create([
            'code' => 'MAERSK',
            'name' => 'Maersk Line Global',
            'is_active' => true,
        ]);

        $origin = Location::create([
            'code' => 'SIN_TEST',
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8403,
        ]);

        $dest = Location::create([
            'code' => 'RTM_TEST',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9244,
            'longitude' => 4.4777,
        ]);

        $this->shipmentAin = Shipment::create([
            'shipment_number' => 'CT-20261118-SEAEUR',
            'customer_id' => $this->customerAin->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'created_by' => $this->operator->id,
            'status' => 'IN_TRANSIT',
            'cargo_type' => 'Industrial Electronics',
            'weight_kg' => 4500,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_tracking_cargo(): void
    {
        $response = $this->post(route('track.search'), [
            'tracking_number' => $this->shipmentAin->shipment_number,
        ]);

        $response->assertRedirect(route('login', [
            'resi' => $this->shipmentAin->shipment_number,
        ]));
        $response->assertSessionHas('warning');
    }

    public function test_customer_can_view_own_shipment(): void
    {
        $response = $this->actingAs($this->customerAin)->post(route('track.search'), [
            'tracking_number' => $this->shipmentAin->shipment_number,
        ]);

        $response->assertStatus(200);
        $response->assertSee('CT-20261118-SEAEUR');
        $response->assertSee('Ain Nurhadijah');
        $response->assertSee('Pemilik Terverifikasi');
    }

    public function test_customer_cannot_view_another_customers_shipment(): void
    {
        // Fahmi tries to access Ain's cargo
        $response = $this->actingAs($this->customerFahmi)->post(route('track.search'), [
            'tracking_number' => $this->shipmentAin->shipment_number,
        ]);

        $response->assertRedirect(route('track.index'));
        $response->assertSessionHas('error');
    }

    public function test_operator_and_admin_can_view_any_shipment(): void
    {
        // Operator
        $responseOp = $this->actingAs($this->operator)->post(route('track.search'), [
            'tracking_number' => $this->shipmentAin->shipment_number,
        ]);
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Pengawas Logistik');

        // Admin
        $responseAdm = $this->actingAs($this->admin)->post(route('track.search'), [
            'tracking_number' => $this->shipmentAin->shipment_number,
        ]);
        $responseAdm->assertStatus(200);
        $responseAdm->assertSee('Pengawas Logistik');
    }

    public function test_post_login_with_intended_resi_redirects_authorized_customer(): void
    {
        $response = $this->post(route('login.submit'), [
            'email' => $this->customerAin->email,
            'password' => 'ainnur123',
            'intended_resi' => $this->shipmentAin->shipment_number,
        ]);

        $response->assertRedirect(route('track.index', ['resi' => $this->shipmentAin->shipment_number]));
        $response->assertSessionHas('success');
    }

    public function test_post_login_with_intended_resi_redirects_unauthorized_customer_to_dashboard(): void
    {
        // Fahmi attempts to log in targeting Ain's shipment resi
        $response = $this->post(route('login.submit'), [
            'email' => $this->customerFahmi->email,
            'password' => 'fahmi123',
            'intended_resi' => $this->shipmentAin->shipment_number,
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('warning');
    }
}
