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

        $customer1 = User::updateOrCreate(
            ['email' => 'ain@gmail.com'],
            ['name' => 'Ain Nurhadijah', 'password' => Hash::make('ainnur123'), 'role' => 'CUSTOMER']
        );

        // Group B Scenario Customer: Fahmi Fadillah (replaces old dummy budi@gmail.com)
        User::where('email', 'budi@gmail.com')->update([
            'name' => 'Fahmi Fadillah',
            'email' => 'fahmi@gmail.com',
            'password' => Hash::make('fahmi123'),
        ]);
        $customer2 = User::updateOrCreate(
            ['email' => 'fahmi@gmail.com'],
            ['name' => 'Fahmi Fadillah', 'password' => Hash::make('fahmi123'), 'role' => 'CUSTOMER']
        );

        // Group C Scenario Customer: Suhaila Azmi (replaces old dummy charlie@gmail.com)
        User::where('email', 'charlie@gmail.com')->update([
            'name' => 'Suhaila Azmi',
            'email' => 'suhaila@gmail.com',
            'password' => Hash::make('suhaila123'),
        ]);
        $customer3 = User::updateOrCreate(
            ['email' => 'suhaila@gmail.com'],
            ['name' => 'Suhaila Azmi', 'password' => Hash::make('suhaila123'), 'role' => 'CUSTOMER']
        );

        // ---------------------------------------------------------------------
        // 2. SEED MASTER LOCATIONS WITH ACCURATE GLOBAL COORDINATES
        // ---------------------------------------------------------------------
        $locs = [
            'SIN' => ['name' => 'Port of Singapore Hub', 'city' => 'Singapore', 'country' => 'Singapore', 'lat' => 1.2644, 'lng' => 103.8403],
            'JKT' => ['name' => 'Tanjung Priok Container Terminal', 'city' => 'Jakarta', 'country' => 'Indonesia', 'lat' => -6.1018, 'lng' => 106.8837],
            'MDN' => ['name' => 'Belawan International Port', 'city' => 'Medan', 'country' => 'Indonesia', 'lat' => 3.7854, 'lng' => 98.6836],
            'RTM' => ['name' => 'Port of Rotterdam (Maasvlakte)', 'city' => 'Rotterdam', 'country' => 'Netherlands', 'lat' => 51.9054, 'lng' => 4.4666],
            'HAM' => ['name' => 'Port of Hamburg Terminal', 'city' => 'Hamburg', 'country' => 'Germany', 'lat' => 53.5353, 'lng' => 9.9872],
            'LAX' => ['name' => 'Port of Los Angeles Pier 400', 'city' => 'Los Angeles', 'country' => 'United States', 'lat' => 33.7432, 'lng' => -118.2673],
            'SHA' => ['name' => 'Port of Shanghai (Yangshan)', 'city' => 'Shanghai', 'country' => 'China', 'lat' => 31.2304, 'lng' => 121.4737],
            'SYD' => ['name' => 'Port Botany Freight Hub', 'city' => 'Sydney', 'country' => 'Australia', 'lat' => -33.9712, 'lng' => 151.2167],
            'SUEZ' => ['name' => 'Suez Canal Convoy Hub', 'city' => 'Suez', 'country' => 'Egypt', 'lat' => 29.9737, 'lng' => 32.5599],
            'CMB' => ['name' => 'Port of Colombo Transshipment', 'city' => 'Colombo', 'country' => 'Sri Lanka', 'lat' => 6.9497, 'lng' => 79.8437],
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
        // 4. SEED REALISTIC SHIPMENT SCENARIOS (MATCHING SLIDE 08 & SLIDE 23)
        // ---------------------------------------------------------------------

        // SCENARIO 1: Group A - Southeast Asia -> Europe with CUSTOMS_HOLD (Threshold 12h)
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
                'shipping_cost' => 8500,
                'currency' => 'USD',
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
            ['status' => 'CREATED', 'shipment_id' => $s1->id],
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
            ['status' => 'BOOKED', 'shipment_id' => $s1->id],
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
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s1->id],
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
            ['status' => 'EXCEPTION', 'shipment_id' => $s1->id],
            [
                'location_id' => $locationModels['SUEZ']->id,
                'latitude' => 29.9737,
                'longitude' => 32.5599,
                'description' => 'Alert: CUSTOMS HOLD exception logged. Severity: HIGH. Origin certificate audit required.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(14),
            ]
        );

        $s1->exceptions()->firstOrCreate(
            ['type' => 'CUSTOMS_HOLD', 'shipment_id' => $s1->id],
            [
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'description' => 'Customs authority flagged documentation discrepancy on origin certificate. Secondary physical inspection underway.',
                'detected_at' => Carbon::now()->subHours(14),
            ]
        );

        // SCENARIO 2: Group B - Asia -> North America with WEATHER_DELAY (Threshold 18h, Delayed by 19h)
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
                'shipping_cost' => 12500,
                'currency' => 'USD',
                'expected_departure_at' => Carbon::now()->subDays(12),
                'expected_arrival_at' => Carbon::now()->subHours(19), // Past expected arrival by 19h (Severity HIGH based on 18h threshold)
                'status' => 'EXCEPTION',
            ]
        );

        $s2->items()->firstOrCreate(
            ['description' => 'Commercial Solar Panels Palletized'],
            ['cargo_type' => 'Solar Photovoltaic Modules', 'weight_kg' => 8200.00, 'quantity' => 120]
        );

        $s2->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED', 'shipment_id' => $s2->id],
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
            ['status' => 'BOOKED', 'shipment_id' => $s2->id],
            [
                'location_id' => $locationModels['SHA']->id,
                'latitude' => 31.2304,
                'longitude' => 121.4737,
                'description' => 'Slot allocated aboard Ever Golden vessel.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(11),
            ]
        );

        $s2->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s2->id],
            [
                'location_id' => null,
                'latitude' => 32.5,
                'longitude' => 165.0, // Mid-Pacific
                'description' => 'Mid-Pacific transit checkpoint. Rerouting 90 nm south due to Pacific storm gale.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(4),
            ]
        );

        $s2->trackingEvents()->firstOrCreate(
            ['status' => 'EXCEPTION', 'shipment_id' => $s2->id],
            [
                'location_id' => null,
                'latitude' => 32.5,
                'longitude' => 165.0,
                'description' => 'Alert: WEATHER DELAY exception logged. Severity: HIGH. 12m swell forces vessel speed reduction.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(19),
            ]
        );

        $s2->exceptions()->firstOrCreate(
            ['type' => 'WEATHER_DELAY', 'shipment_id' => $s2->id],
            [
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'description' => 'Severe Pacific gale force storm system requires navigation diversion and speed reduction, causing estimated 19 hours arrival delay.',
                'detected_at' => Carbon::now()->subHours(19),
            ]
        );

        // SCENARIO 3: Group C - Southeast Asia -> Australia with Resolved DOCUMENTATION_ISSUE (Delivered)
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
                'shipping_cost' => 6200,
                'currency' => 'USD',
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
            ['status' => 'CREATED', 'shipment_id' => $s3->id],
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
            ['status' => 'BOOKED', 'shipment_id' => $s3->id],
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
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s3->id],
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
            ['status' => 'DELIVERED', 'shipment_id' => $s3->id],
            [
                'location_id' => $locationModels['SYD']->id,
                'latitude' => -33.9712,
                'longitude' => 151.2167,
                'description' => 'Successfully discharged and received at Port Botany warehouse.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(1)->subHours(2),
            ]
        );

        $s3->exceptions()->firstOrCreate(
            ['type' => 'DOCUMENTATION_ISSUE', 'shipment_id' => $s3->id],
            [
                'severity' => 'MEDIUM',
                'status' => 'RESOLVED',
                'description' => 'Australian quarantine authority requested digital re-verification of fumigation certificate. Cleared within 6 hours.',
                'detected_at' => Carbon::now()->subDays(3),
                'resolved_at' => Carbon::now()->subDays(2),
            ]
        );

        // SCENARIO 4: Domestic Inter-Island Regular Shipment (Medan -> Jakarta)
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
            ['status' => 'CREATED', 'shipment_id' => $s4->id],
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
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s4->id],
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
                'shipping_cost' => 3400,
                'currency' => 'USD',
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
            ['status' => 'CREATED', 'shipment_id' => $s5->id],
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
            ['status' => 'BOOKED', 'shipment_id' => $s5->id],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Cold container slot confirmed with carrier.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(2),
            ]
        );

        // SCENARIO 6: PORT_CONGESTION Exception (Shanghai -> Rotterdam)
        $s6 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-PORTCON'],
            [
                'customer_id' => $customer3->id,
                'carrier_id' => $carrierModels['MSK']->id,
                'origin_id' => $locationModels['SHA']->id,
                'destination_id' => $locationModels['RTM']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Automotive Transmission Parts',
                'weight_kg' => 6400.00,
                'shipping_cost' => 9200,
                'currency' => 'USD',
                'expected_departure_at' => Carbon::now()->subDays(8),
                'expected_arrival_at' => Carbon::now()->addDays(3),
                'status' => 'EXCEPTION',
            ]
        );

        $s6->items()->firstOrCreate(
            ['description' => 'Automotive Gear Assemblies & Shafts'],
            ['cargo_type' => 'Automotive Transmission Parts', 'weight_kg' => 6400.00, 'quantity' => 80]
        );

        $s6->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED', 'shipment_id' => $s6->id],
            [
                'location_id' => $locationModels['SHA']->id,
                'latitude' => 31.2304,
                'longitude' => 121.4737,
                'description' => 'Container sealed at Yangshan Port.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(8),
            ]
        );

        $s6->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s6->id],
            [
                'location_id' => $locationModels['RTM']->id,
                'latitude' => 51.9054,
                'longitude' => 4.4666,
                'description' => 'Vessel anchored outside Rotterdam outer harbor roadstead.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(10),
            ]
        );

        $s6->trackingEvents()->firstOrCreate(
            ['status' => 'EXCEPTION', 'shipment_id' => $s6->id],
            [
                'location_id' => $locationModels['RTM']->id,
                'latitude' => 51.9054,
                'longitude' => 4.4666,
                'description' => 'Alert: PORT CONGESTION exception logged. Severity: MEDIUM. Berthing delayed.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(8),
            ]
        );

        $s6->exceptions()->firstOrCreate(
            ['type' => 'PORT_CONGESTION', 'shipment_id' => $s6->id],
            [
                'severity' => 'MEDIUM',
                'status' => 'INVESTIGATING',
                'description' => 'Heavy peak container ship congestion at Maasvlakte deepwater terminal. Port authority estimated 36-hour berthing queue.',
                'detected_at' => Carbon::now()->subHours(8),
            ]
        );

        // SCENARIO 7: DAMAGED Exception (Singapore -> Los Angeles)
        $s7 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-DAMAGED'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $carrierModels['MSC']->id,
                'origin_id' => $locationModels['SIN']->id,
                'destination_id' => $locationModels['LAX']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Precision Optical Lenses',
                'weight_kg' => 950.00,
                'shipping_cost' => 7400,
                'currency' => 'USD',
                'expected_departure_at' => Carbon::now()->subDays(4),
                'expected_arrival_at' => Carbon::now()->addDays(8),
                'status' => 'EXCEPTION',
            ]
        );

        $s7->items()->firstOrCreate(
            ['description' => 'Laboratory Laser & Optical Telescopic Assemblies'],
            ['cargo_type' => 'Precision Optical Lenses', 'weight_kg' => 950.00, 'quantity' => 18]
        );

        $s7->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED', 'shipment_id' => $s7->id],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Cargo received at Pasir Panjang container yard.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(4),
            ]
        );

        $s7->trackingEvents()->firstOrCreate(
            ['status' => 'EXCEPTION', 'shipment_id' => $s7->id],
            [
                'location_id' => $locationModels['SIN']->id,
                'latitude' => 1.2644,
                'longitude' => 103.8403,
                'description' => 'Alert: DAMAGED exception logged. Severity: HIGH. External crate puncture noted.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subHours(5),
            ]
        );

        $s7->exceptions()->firstOrCreate(
            ['type' => 'DAMAGED', 'shipment_id' => $s7->id],
            [
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'description' => 'Forklift puncture damage detected on wooden exterior crate during terminal staging. Survey team dispatched for joint inspection.',
                'detected_at' => Carbon::now()->subHours(5),
            ]
        );

        // SCENARIO 8: MISROUTED Exception (Resolved) (Jakarta -> Hamburg)
        $s8 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-20261118-MISROUT'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $carrierModels['EVER']->id,
                'origin_id' => $locationModels['JKT']->id,
                'destination_id' => $locationModels['HAM']->id,
                'created_by' => $operator->id,
                'cargo_type' => 'Boutique Rattan Furniture',
                'weight_kg' => 3100.00,
                'shipping_cost' => 6800,
                'currency' => 'USD',
                'expected_departure_at' => Carbon::now()->subDays(7),
                'expected_arrival_at' => Carbon::now()->addDays(10),
                'status' => 'IN_TRANSIT',
            ]
        );

        $s8->items()->firstOrCreate(
            ['description' => 'Handcrafted Rattan Living Sets'],
            ['cargo_type' => 'Boutique Rattan Furniture', 'weight_kg' => 3100.00, 'quantity' => 40]
        );

        $s8->trackingEvents()->firstOrCreate(
            ['status' => 'CREATED', 'shipment_id' => $s8->id],
            [
                'location_id' => $locationModels['JKT']->id,
                'latitude' => -6.1018,
                'longitude' => 106.8837,
                'description' => 'Handover complete at Tanjung Priok.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(7),
            ]
        );

        $s8->trackingEvents()->firstOrCreate(
            ['status' => 'EXCEPTION', 'shipment_id' => $s8->id],
            [
                'location_id' => $locationModels['CMB']->id,
                'latitude' => 6.9497,
                'longitude' => 79.8437,
                'description' => 'Alert: MISROUTED exception logged. Container discharged at Colombo instead of Salalah.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDays(3),
            ]
        );

        $s8->exceptions()->firstOrCreate(
            ['type' => 'MISROUTED', 'shipment_id' => $s8->id],
            [
                'severity' => 'MEDIUM',
                'status' => 'RESOLVED',
                'description' => 'Transshipment feeder misdirected container to Colombo south terminal. Re-manifested onto Evergreen mother vessel heading to Hamburg.',
                'detected_at' => Carbon::now()->subDays(3),
                'resolved_at' => Carbon::now()->subDay(),
            ]
        );

        $s8->trackingEvents()->firstOrCreate(
            ['status' => 'IN_TRANSIT', 'shipment_id' => $s8->id],
            [
                'location_id' => $locationModels['CMB']->id,
                'latitude' => 6.9497,
                'longitude' => 79.8437,
                'description' => 'Re-routed container loaded onto vessel and resumed main trade lane transit.',
                'created_by' => $operator->id,
                'event_time' => Carbon::now()->subDay(),
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

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'EXCEPTION_REPORTED',
            'model' => 'ShipmentException',
            'description' => 'Reported WEATHER_DELAY on shipment #CT-20261118-ASIANAM in Northern Pacific.',
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'EXCEPTION_RESOLVED',
            'model' => 'ShipmentException',
            'description' => 'Resolved DOCUMENTATION_ISSUE on shipment #CT-20261118-SEAAUS following biosecurity verification.',
        ]);

        // Notifications
        Notification::send(
            $customer1,
            'Exception Reported: CUSTOMS_HOLD',
            'Your shipment CT-20261118-SEAEUR encountered a customs inspection hold at Suez Canal Terminal.',
            'DANGER',
            $s1
        );

        Notification::send(
            $customer1,
            'Package Staging Alert: DAMAGED',
            'Incident reported for shipment CT-20261118-DAMAGED at Singapore Pasir Panjang. Joint inspection scheduled.',
            'DANGER',
            $s7
        );

        Notification::send(
            $customer2,
            'Shipment Delayed: WEATHER_DELAY',
            'Shipment CT-20261118-ASIANAM is experiencing high swell delay across the Pacific. Expected delay: ~19 hours.',
            'DANGER',
            $s2
        );

        Notification::send(
            $customer2,
            'Transit Resumed: MISROUTED Cleared',
            'Shipment CT-20261118-MISROUT has been successfully re-routed and loaded onto main European vessel.',
            'SUCCESS',
            $s8
        );

        Notification::send(
            $customer3,
            'Shipment Delivered Successfully',
            'Your shipment CT-20261118-SEAAUS has been safely delivered at Port Botany, Sydney.',
            'SUCCESS',
            $s3
        );

        Notification::send(
            $customer3,
            'Port Delay Notice: PORT_CONGESTION',
            'Shipment CT-20261118-PORTCON is waiting in Rotterdam roadstead due to deepwater terminal congestion.',
            'INFO',
            $s6
        );
    }
}
