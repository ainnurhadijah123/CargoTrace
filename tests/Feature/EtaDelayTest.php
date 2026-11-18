<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaDelayTest extends TestCase
{
    use RefreshDatabase;

    protected Shipment $shipment;
    protected EtaService $etaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etaService = app(EtaService::class);
        $user = User::factory()->create();
        $carrier = Carrier::create(['code' => 'CARRIER', 'name' => 'Carrier Test', 'is_active' => true]);
        $origin = Location::create(['code' => 'ORG', 'name' => 'Origin', 'city' => 'City', 'country' => 'Country']);
        $destination = Location::create(['code' => 'DST', 'name' => 'Destination', 'city' => 'City', 'country' => 'Country']);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-ETA-TEST',
            'customer_id' => $user->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'created_by' => $user->id,
            'status' => 'IN_TRANSIT',
        ]);
    }

    public function test_on_time_shipment_reports_no_delay(): void
    {
        // Expected arrival is 2 days in the future
        $this->shipment->update([
            'expected_arrival_at' => Carbon::now()->addDays(2),
        ]);

        $result = $this->etaService->calculateDelay($this->shipment);

        $this->assertFalse($result['is_delayed']);
        $this->assertEquals(0, $result['delay_hours']);
        $this->assertEquals('NONE', $result['severity']);
    }

    public function test_delayed_shipment_calculates_correct_delay_hours(): void
    {
        // Expected arrival was 10 hours ago
        $this->shipment->update([
            'expected_arrival_at' => Carbon::now()->subHours(10),
        ]);

        $result = $this->etaService->calculateDelay($this->shipment);

        $this->assertTrue($result['is_delayed']);
        $this->assertGreaterThanOrEqual(9, $result['delay_hours']);
        $this->assertEquals('MEDIUM', $result['severity']); // >6h and <=24h is MEDIUM
    }

    public function test_delay_severity_categorization_high(): void
    {
        // Expected arrival was 30 hours ago (>24 hours is HIGH severity)
        $this->shipment->update([
            'expected_arrival_at' => Carbon::now()->subHours(30),
        ]);

        $result = $this->etaService->calculateDelay($this->shipment);

        $this->assertTrue($result['is_delayed']);
        $this->assertEquals('HIGH', $result['severity']);
    }
}
