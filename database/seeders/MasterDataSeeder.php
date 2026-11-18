<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\ShipmentException;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. SEED USERS & ROLES
        // ---------------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'System Administrator', 'password' => Hash::make('admin123'), 'role' => 'ADMIN']
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@gmail.com'],
            ['name' => 'Logistics Operator', 'password' => Hash::make('operator123'), 'role' => 'OPERATOR']
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'ain@gmail.com'],
            ['name' => 'Ain Nurhadijah', 'password' => Hash::make('ainnur123'), 'role' => 'CUSTOMER']
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'budi@gmail.com'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('budi123'), 'role' => 'CUSTOMER']
        );

        $customer3 = User::firstOrCreate(
            ['email' => 'charlie@gmail.com'],
            ['name' => 'Charlie Henderson', 'password' => Hash::make('charlie123'), 'role' => 'CUSTOMER']
        );

        // ---------------------------------------------------------------------
        // 2. SEED LOCATIONS WITH ACCURATE GLOBAL COORDINATES
        // ---------------------------------------------------------------------
        $locs = [
            'SIN' => ['name' => 'Port of Singapore', 'city' => 'Singapore', 'country' => 'Singapore', 'lat' => 1.2644, 'lng' => 103.8403],
            'JKT' => ['name' => 'Tanjung Priok Container Port', 'city' => 'Jakarta', 'country' => 'Indonesia', 'lat' => -6.1018, 'lng' => 106.8837],
            'MDN' => ['name' => 'Belawan International Port', 'city' => 'Medan', 'country' => 'Indonesia', 'lat' => 3.7854, 'lng' => 98.6836],
            'RTM' => ['name' => 'Port of Rotterdam', 'city' => 'Rotterdam', 'country' => 'Netherlands', 'lat' => 51.9054, 'lng' => 4.4666],
            'HAM' => ['name' => 'Port of Hamburg', 'city' => 'Hamburg', 'country' => 'Germany', 'lat' => 53.5353, 'lng' => 9.9872],
            'LAX' => ['name' => 'Port of Los Angeles', 'city' => 'Los Angeles', 'country' => 'United States', 'lat' => 33.7432, 'lng' => -118.2673],
            'SHA' => ['name' => 'Port of Shanghai', 'city' => 'Shanghai', 'country' => 'China', 'lat' => 31.2304, 'lng' => 121.4737],
            'SYD' => ['name' => 'Port Botany', 'city' => 'Sydney', 'country' => 'Australia', 'lat' => -33.9712, 'lng' => 151.2167],
            'SUEZ' => ['name' => 'Suez Canal Terminal Hub', 'city' => 'Suez', 'country' => 'Egypt', 'lat' => 29.9737, 'lng' => 32.5599],
        ];

        $locationModels = [];
        foreach ($locs as $code => $data) {
            $locationModels[$code] = Location::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'city' => $data['city'],
                    'country' => $data['country'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 3. SEED LOGISTICS CARRIERS
        // ---------------------------------------------------------------------
        $carriers = [
            'MSK' => ['name' => 'Maersk Line Global', 'contact' => '+45 33 63 33 63', 'active' => true],
            'MSC' => ['name' => 'Mediterranean Shipping Company (MSC)', 'contact' => '+41 22 703 8888', 'active' => true],
            'EVER' => ['name' => 'Evergreen Marine Corp', 'contact' => '+886 2 2505 7766', 'active' => true],
            'SUMLOG' => ['name' => 'Sumatera Logistik Express', 'contact' => '081122334455', 'active' => true],
            'CTGLOB' => ['name' => 'CargoTrace Global Line', 'contact' => 'dispatch@cargotrace.com', 'active' => true],
        ];

        $carrierModels = [];
        foreach ($carriers as $code => $data) {
            $carrierModels[$code] = Carrier::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'contact_info' => $data['contact'],
                    'is_active' => $data['active'],
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 4. SEED REALISTIC SHIPMENT SCENARIOS
        // ---------------------------------------------------------------------

        // SCENARIO 1: Group A - Southeast Asia -> Europe with CUSTOMS_HOLD Exception
        $s1 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-SEAEUR'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $carrierModels['MSK']->id,
                'origin_id' => $locationModels['SIN']->id,
                'destination_id' => $locationModels['RTM']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Industrial Electronics',
                'weight_kg' => 4500.00,
                'shipping_cost' => 125000000,
                'currency' => 'IDR',
                'expected_departure_at' => Carbon::now()->subDays(6),
                'expected_arrival_at' => Carbon::now()->addDays(5),
                'status' => 'EXCEPTION',
            ]
        );

        $s1->items()->firstOrCreate(
            ['description' => 'Precision Microcontrollers & Semiconductor Trays'],
            ['cargo_type' => 'Industrial Electronics', 'weight_kg' => 4500.00, 'quantity' => 24]
        );

        $s1->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED'],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Electronic manifest booked and export clearance initiated at Singapore.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(6),
            ]
        );

        $s1->trackingEvents()->firstOrCreate(
            ['status' => 'BOOKED'],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Vessel space confirmed on Maersk Mc-Kinney Moller.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(5)->subHours(12),
            ]
        );

        $s1->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT'],
            [
                'location_id' => $locationModels['SUEZ']->id,
                'latitude' => 29.9737,
                'longitude' => 32.5599,
                'description' => 'Vessel approached Suez Canal northern transit convoy.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(2),
            ]
        );

        $s1->trackingEvents()->firstOrCreate(
            ['status' => 'EXCEPTION'],
            [
                'location_id' => $locationModels['SUEZ']->id,
                'latitude' => 29.9737,
                'longitude' => 32.5599,
                'description' => 'Alert: CUSTOMS HOLD exception logged. Severity: HIGH. Secondary document audit required.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(14),
            ]
        );

        $s1->exceptions()->firstOrCreate(
            ['type' => 'CUSTOMS_HOLD', 'shipment_id' => $s1->id],
            [
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'description' => 'Customs authority flagged documentation discrepancy on origin certificate. Audit underway.',
                'detected_at' => Carbon::now()->subHours(14),
            ]
        );

        // SCENARIO 2: Group B - Asia -> North America with Weather Delay
        $s2 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-ASIANAM'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $carrierModels['EVER']->id,
                'origin_id' => $locationModels['SHA']->id,
                'destination_id' => $locationModels['LAX']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Solar Photovoltaic Modules',
                'weight_kg' => 8200.00,
                'shipping_cost' => 180000000,
                'currency' => 'IDR',
                'expected_departure_at' => Carbon::now()->subDays(12),
                'expected_arrival_at' => Carbon::now()->subHours(19), // Past expected arrival by 19h (triggers MEDIUM/HIGH delay)
                'status' => 'IN_TRANSIT',
            ]
        );

        $s2->items()->firstOrCreate(
            ['description' => 'Commercial Solar Panels Palletized'],
            ['cargo_type' => 'Solar Photovoltaic Modules', 'weight_kg' => 8200.00, 'quantity' => 120]
        );

        $s2->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED'],
            [
                'location_id' => $locationModels['SHA']->id,
                'latitude' => 31.2304,
                'longitude' => 121.4737,
                'description' => 'Cargo received and container sealed at Shanghai Port.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(12),
            ]
        );

        $s2->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'latitude' => 32.5,
                'longitude' => 165.0, // Mid-Pacific
                'description' => 'Mid-Pacific transit checkpoint. Rerouting 60 nm south due to Pacific storm gale.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(4),
            ]
        );

        // SCENARIO 3: Group C - Southeast Asia -> Australia (Delivered)
        $s3 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-SEAAUS'],
            [
                'customer_id' => $customer3->id,
                'carrier_id' => $carrierModels['MSC']->id,
                'origin_id' => $locationModels['JKT']->id,
                'destination_id' => $locationModels['SYD']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Specialty Coffee Beans',
                'weight_kg' => 12500.00,
                'shipping_cost' => 95000000,
                'currency' => 'IDR',
                'expected_departure_at' => Carbon::now()->subDays(10),
                'expected_arrival_at' => Carbon::now()->subDays(1),
                'status' => 'DELIVERED',
            ]
        );

        $s3->items()->firstOrCreate(
            ['description' => 'Grade-A Arabica Coffee Bags in Ventilated Container'],
            ['cargo_type' => 'Specialty Coffee Beans', 'weight_kg' => 12500.00, 'quantity' => 450]
        );

        $s3->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED'],
            [
                'location_id' => $locationModels['JKT']->id,
                'latitude' => -6.1018,
                'longitude' => 106.8837,
                'description' => 'Phytosanitary inspection passed at Tanjung Priok.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(10),
            ]
        );

        $s3->trackingEvents()->firstOrCreate(
            ['status' => 'BOOKED'],
            [
                'location_id' => $locationModels['JKT']->id,
                'latitude' => -6.1018,
                'longitude' => 106.8837,
                'description' => 'Container loaded aboard MSC Ariane.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(9),
            ]
        );

        $s3->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'latitude' => -20.5,
                'longitude' => 128.0,
                'description' => 'Crossing Timor Sea heading for Australian eastern seaboard.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(5),
            ]
        );

        $s3->trackingEvents()->firstOrCreate(
            ['status' => 'DELIVERED'],
            [
                'location_id' => $locationModels['SYD']->id,
                'latitude' => -33.9712,
                'longitude' => 151.2167,
                'description' => 'Successfully discharged and received at Port Botany warehouse.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(1)->subHours(2),
            ]
        );

        // SCENARIO 4: Domestic Inter-Island Regular Shipment
        $s4 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-DOMEST'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $carrierModels['SUMLOG']->id,
                'origin_id' => $locationModels['MDN']->id,
                'destination_id' => $locationModels['JKT']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Apparel & Garments',
                'weight_kg' => 1200.00,
                'shipping_cost' => 30000000,
                'currency' => 'IDR',
                'expected_departure_at' => Carbon::now()->subDays(2),
                'expected_arrival_at' => Carbon::now()->addDays(2),
                'status' => 'IN_TRANSIT',
            ]
        );

        $s4->items()->firstOrCreate(
            ['description' => 'Textiles and Retail Ready-to-Wear Apparel'],
            ['cargo_type' => 'Apparel & Garments', 'weight_kg' => 1200.00, 'quantity' => 50]
        );

        $s4->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED'],
            [
                'location_id' => $locationModels['MDN']->id,
                'latitude' => 3.7854,
                'longitude' => 98.6836,
                'description' => 'Order registered at Belawan Port distribution hub.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(2),
            ]
        );

        $s4->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT'],
            [
                'location_id' => $locationModels['MDN']->id,
                'latitude' => 3.7854,
                'longitude' => 98.6836,
                'description' => 'Vessel departed Belawan heading south through Malacca Strait.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDay(),
            ]
        );

        // SCENARIO 5: Early Lifecycle Shipment (BOOKED)
        $s5 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-BOOKED'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $carrierModels['CTGLOB']->id,
                'origin_id' => $locationModels['SIN']->id,
                'destination_id' => $locationModels['HAM']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Medical Diagnostics Equipment',
                'weight_kg' => 750.00,
                'shipping_cost' => 85000000,
                'currency' => 'IDR',
                'expected_departure_at' => Carbon::now()->addDay(),
                'expected_arrival_at' => Carbon::now()->addDays(14),
                'status' => 'BOOKED',
            ]
        );

        $s5->items()->firstOrCreate(
            ['description' => 'Temperature-controlled Diagnostic Reagents'],
            ['cargo_type' => 'Medical Diagnostics Equipment', 'weight_kg' => 750.00, 'quantity' => 15]
        );

        $s5->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED'],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Shipment created and scheduled for cold-chain staging.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(6),
            ]
        );

        $s5->trackingEvents()->firstOrCreate(
            ['status' => 'BOOKED'],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Cold container slot confirmed with carrier.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(2),
            ]
        );

        // ---------------------------------------------------------------------
        // 5. SEED AUDIT LOGS & NOTIFICATIONS
        // ---------------------------------------------------------------------
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'SYSTEM_INIT',
            'model' => 'System',
            'description' => 'Master data, carriers, and global ports initialized for CargoTrace.',
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'EXCEPTION_REPORTED',
            'model' => 'ShipmentException',
            'description' => 'Reported CUSTOMS_HOLD on shipment #CT-20261118-SEAEUR at Suez Canal.',
        ]);

        Notification::send(
            $customer1,
            'Exception Reported: CUSTOMS_HOLD',
            'Your shipment CT-20261118-SEAEUR encountered a customs inspection hold at Suez Canal Terminal.',
            'DANGER',
            $s1
        );

        Notification::send(
            $customer3,
            'Shipment Delivered Successfully',
            'Your shipment CT-20261118-SEAAUS has been safely delivered at Port Botany, Sydney.',
            'SUCCESS',
            $s3
        );
    }
}
