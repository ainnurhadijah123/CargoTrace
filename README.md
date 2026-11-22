# CargoTrace — Global Shipment Visibility & Tracking Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Leaflet](https://img.shields.io/badge/Leaflet-1.9.4-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com)
[![PHPUnit Tests](https://img.shields.io/badge/Tests-32%20Passed-success?style=for-the-badge&logo=phpunit&logoColor=white)](https://phpunit.de)

> **Web Programming 2 — Final Project (UAS)**  
> *A software portfolio project demonstrating enterprise software engineering, domain-driven service architecture, REST API design, external API integration, deterministic business logic, and automated testing.*

---

## 📌 Executive Summary & Problem Solved

In modern international supply chains, shipment data is often fragmented across multiple freight carriers, ocean shipping lines, and local customs brokers. Customers face blind spots regarding their cargo's real-time whereabouts, while logistics operators lack a unified control tower to triage delays and cargo exceptions.

**CargoTrace** solves this problem by delivering a unified, end-to-end shipment visibility and tracking platform:
- **Centralized Telemetry**: Tracks shipments across ocean, air, and overland routes with interactive GIS mapping.
- **Strict State Lifecycle**: Enforces deterministic status transitions (`CREATED` &rarr; `BOOKED` &rarr; `PICKED_UP` &rarr; `IN_TRANSIT` &rarr; `DELIVERED`).
- **Deterministic Delay & ETA Engine**: Computes exact delay duration and classifies severity without brittle heuristics.
- **Incident & Exception Workflow**: Manages disruptions (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`) with incident reporting and resolution state machines.
- **External API Integrations**: Queries live weather telemetry from Open-Meteo and reverse geocoding from OpenStreetMap Nominatim.
- **Role-Based Security**: Complete server-side authorization separating Administrator, Logistics Operator, and Customer privileges.

---

## 🏛️ System Architecture

CargoTrace strictly implements a **Service-Layer Architecture**, keeping controllers thin and isolating business logic into reusable, testable domain services.

```
Browser / REST Client
       │
   [Routes + CheckRole / Sanctum Middleware]
       │
   [Form Requests (Validation)]
       │
   [HTTP Controllers (Thin)]
       │
   [Domain Service Layer]
   ├── ShipmentService (Lifecycle & generation)
   ├── TrackingService (State transitions & milestones)
   ├── EtaService (Deterministic delay calculation)
   ├── ExceptionService (Incident reporting & resolution)
   └── External\WeatherService (Open-Meteo HTTP Client)
       │
   [Eloquent ORM Models]
       │
   [MySQL / SQLite Database]
```

Detailed architectural specifications and layer responsibilities are documented in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

---

## 📊 Database Design & ERD

The database schema follows 3rd Normal Form (3NF) with foreign key constraints, indexes, cascade rules, and audit logging.

```
users (1) ───────< shipments (M) ───────< shipment_items (M)
                     │       │
                     │       └──────────< tracking_events (M) >────── (1) locations
                     │
                     ├──────────────────< shipment_exceptions (M)
                     │
                     ├────── (1) carriers
                     │
                     └────── (1) origin / destination locations
```

Full Mermaid diagram, cardinality rules, and detailed column-by-column Data Dictionary are available in [docs/ERD.md](docs/ERD.md).

---

## 🚀 Key Features

### 1. Shipment Lifecycle Management
- Statuses: `CREATED`, `BOOKED`, `PICKED_UP`, `IN_TRANSIT`, `DELIVERED`, `EXCEPTION`, `CANCELLED`.
- Validated backend transitions: terminal states (`DELIVERED`, `CANCELLED`) cannot be modified without proper authorization.
- Automatic consignment reference generation: `CT-YYYYMMDD-XXXXXX`.

### 2. Immutable Tracking Timeline
- Milestone events are append-only; historical logs cannot be overwritten.
- Captures status, coordinate latitude/longitude, location facility, timestamp, and the actor who recorded the event.
- Visual timeline with distinct styling for normal milestones vs incident exceptions.

### 3. Deterministic ETA & Delay Telemetry
- Compares real-world delivery or current wall clock against SLA target (`expected_arrival_at`).
- Severity thresholds:
  - $\le 6 \text{ hours}$: `LOW` severity
  - $> 6 \text{ and } \le 24 \text{ hours}$: `MEDIUM` severity
  - $> 24 \text{ hours}$: `HIGH` severity (triggers Priority Operational Queue)
- Configurable via `.env` parameter thresholds.

### 4. Incident & Exception Management
- Standard categories: `CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`.
- Operational lifecycle: `OPEN` &rarr; `INVESTIGATING` &rarr; `RESOLVED`.
- Resolving the final open incident automatically resumes shipment status to `IN_TRANSIT`.

### 5. Interactive GIS Map & Live Weather Integration
- **Leaflet.js + OpenStreetMap**: Renders route polylines, origin/destination pins, and live checkpoint markers with proper attribution.
- **External Weather API**: Fetches real-time temperature, wind speed, and conditions at cargo checkpoints via the Open-Meteo API with timeout handling and resilient fallbacks.

### 6. Role-Based Access Control (RBAC)
- **Administrator**: Full access to dashboard, carrier management, location master data, user accounts, and immutable audit logs.
- **Logistics Operator**: Creates shipments, appends tracking milestones, logs exceptions, and modifies status.
- **Customer**: Strictly scoped to view their own shipments, timeline, route map, and notifications.

---

## 🔌 REST API Endpoints

All REST API endpoints use Laravel Sanctum for Bearer Token authentication, enforce server-side validation, server-side pagination, and standard JSON response envelopes.

| Method | Endpoint | Description | Access |
|---|---|---|---|
| `POST` | `/api/register` | Register customer account | Public |
| `POST` | `/api/login` | Obtain Sanctum Bearer token | Public |
| `POST` | `/api/logout` | Revoke current token | Authenticated |
| `GET` | `/api/user` | Get authenticated user profile | Authenticated |
| `GET` | `/api/shipments` | List shipments (search, filter, pagination) | Customer / Staff |
| `POST` | `/api/shipments` | Create new shipment with items | Staff Only |
| `GET` | `/api/shipments/{id}` | Detailed shipment with ETA & weather | Customer / Staff |
| `PATCH` | `/api/shipments/{id}` | Update shipment specifications | Staff Only |
| `DELETE` | `/api/shipments/{id}` | Delete shipment in `CREATED` status | Admin Only |
| `GET` | `/api/shipments/{id}/tracking` | Chronological tracking events | Customer / Staff |
| `POST` | `/api/shipments/{id}/tracking` | Log tracking milestone & advance status | Staff Only |
| `GET` | `/api/shipments/{id}/exceptions` | List exceptions for shipment | Customer / Staff |
| `POST` | `/api/shipments/{id}/exceptions` | Report incident exception | Staff Only |
| `PATCH` | `/api/exceptions/{id}` | Resolve or update exception status | Staff Only |
| `GET` | `/api/carriers` | List active carriers | Authenticated |
| `POST` | `/api/carriers` | Create carrier record | Admin Only |
| `GET` | `/api/locations` | List global hubs and ports | Authenticated |
| `POST` | `/api/locations` | Create location record | Admin Only |
| `GET` | `/api/public/track/{number}` | Public telemetry lookup by resi | Public |

A ready-to-import Postman Collection v2.1 is included at [`docs/cargotrace_postman_collection.json`](docs/cargotrace_postman_collection.json).

---

## 👥 Seed Data, Group Scenarios & Demo Accounts

CargoTrace is pre-configured with complete, realistic seed data specifically mapped to the **Group Scenarios** defined in Slide 23 of the UAS brief, covering all 6 exception types (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`) across all lifecycle states:

| Role | Email | Password | Scenario / Scope / Assigned Shipments |
|---|---|---|---|
| **Administrator** | `admin@gmail.com` | `admin123` | Control Tower Dashboard, Audit Logs, Master Data Management |
| **Logistics Operator** | `operator@gmail.com` | `operator123` | Operational Dispatch, Tracking Milestones, Incident Filing & Resolution |
| **Customer (Group A)** | `ain@gmail.com` | `ainnur123` | **Group A Scenario**: Southeast Asia &rarr; Europe (`CT-20261118-SEAEUR`), Threshold: 12h, Exception: `CUSTOMS_HOLD` (Open). Also owns `CT-20261118-DOMEST` & `CT-20261118-DAMAGED` |
| **Customer (Group B)** | `budi@gmail.com` | `budi123` | **Group B Scenario**: Asia &rarr; North America (`CT-20261118-ASIANAM`), Threshold: 18h, Exception: `WEATHER_DELAY` (Delayed by 19h). Also owns `CT-20261118-BOOKED` & `CT-20261118-MISROUT` |
| **Customer (Group C)** | `charlie@gmail.com` | `charlie123` | **Group C Scenario**: Southeast Asia &rarr; Australia (`CT-20261118-SEAAUS`), Threshold: 24h, Exception: `DOCUMENTATION_ISSUE` (Resolved, Delivered). Also owns `CT-20261118-PORTCON` |

### Detailed Seeded Shipment Scenarios

1. **`CT-20261118-SEAEUR` (Group A)**: Singapore (`SIN`) &rarr; Rotterdam (`RTM`) via Maersk (`MSK`). Currently at Suez Canal holding a `CUSTOMS_HOLD` exception (`HIGH` severity, status `OPEN`).
2. **`CT-20261118-ASIANAM` (Group B)**: Shanghai (`SHA`) &rarr; Los Angeles (`LAX`) via Evergreen (`EVER`). Mid-Pacific checkpoint detour caused by gale swell, delayed by 19 hours past target ETA, triggering a `WEATHER_DELAY` exception (`HIGH` severity, status `OPEN`).
3. **`CT-20261118-SEAAUS` (Group C)**: Jakarta (`JKT`) &rarr; Sydney (`SYD`) via MSC. Successfully completed voyage and reached terminal status `DELIVERED` after resolving a `DOCUMENTATION_ISSUE` exception (`MEDIUM` severity, status `RESOLVED`).
4. **`CT-20261118-DOMEST`**: Belawan Medan (`MDN`) &rarr; Tanjung Priok Jakarta (`JKT`) via Sumatera Logistik (`SUMLOG`). Domestic regular cargo, status `IN_TRANSIT`, perfectly on schedule.
5. **`CT-20261118-BOOKED`**: Singapore (`SIN`) &rarr; Hamburg (`HAM`) via CargoTrace Global (`CTGLOB`). Cold-chain diagnostic reagents, status `BOOKED`.
6. **`CT-20261118-PORTCON`**: Shanghai (`SHA`) &rarr; Rotterdam (`RTM`) via Maersk (`MSK`). Anchor queue delay in Rotterdam roadstead, holding a `PORT_CONGESTION` exception (`MEDIUM` severity, status `INVESTIGATING`).
7. **`CT-20261118-DAMAGED`**: Singapore (`SIN`) &rarr; Los Angeles (`LAX`) via MSC. Exterior crate puncture during container yard staging, holding a `DAMAGED` exception (`HIGH` severity, status `OPEN`).
8. **`CT-20261118-MISROUT`**: Jakarta (`JKT`) &rarr; Hamburg (`HAM`) via Evergreen (`EVER`). Feeder offloaded at Colombo, holding a resolved `MISROUTED` exception (`MEDIUM` severity, status `RESOLVED`), now resumed `IN_TRANSIT`.

---

## 🛠️ Installation & Setup Guide

### Prerequisites
- PHP 8.2 or 8.3+ with `pdo`, `mbstring`, `openssl`, `curl` extensions.
- Composer 2.x
- MySQL 8.x or SQLite 3

### Step-by-Step Setup

```bash
# 1. Clone repository
git clone https://github.com/your-username/cargotrace.git
cd cargotrace

# 2. Install Composer dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database configuration (.env)
# Default out-of-the-box uses SQLite:
#   DB_CONNECTION=sqlite
# For MySQL:
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=db_cargotrace
#   DB_USERNAME=root
#   DB_PASSWORD=

# 5. Run migrations & realistic seeders
php artisan migrate:fresh --seed

# 6. Start the development server
php artisan serve
```

Access the application in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Automated Testing

CargoTrace includes an extensive test suite covering Authentication, In-App Notifications, Shipment Lifecycle, Server-Side Filtering & Sorting, State Machine Status Transitions, Deterministic Delay Mathematics, Exception Incident Workflows, and REST API Contracts.

```bash
# Execute the full automated test suite
php artisan test
```

### Test Evidence
```
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AuthenticationTest
  ✓ user can login with valid credentials
  ✓ user cannot login with invalid password
  ✓ user can register new customer account
  ✓ api login returns sanctum bearer token
  ✓ api register creates account and token

   PASS  Tests\Feature\EtaDelayTest
  ✓ on time shipment reports no delay
  ✓ delayed shipment calculates correct delay hours
  ✓ delay severity categorization high

   PASS  Tests\Feature\ExampleTest
  ✓ root redirects to track portal
  ✓ tracking portal returns successful response

   PASS  Tests\Feature\ExceptionManagementTest
  ✓ operator can report exception which transitions shipment status
  ✓ resolving exception resumes transit status
  ✓ cannot report exception on delivered shipment

   PASS  Tests\Feature\NotificationAndFilterTest
  ✓ customer can mark notification as read
  ✓ customer can mark all notifications as read
  ✓ admin can filter shipments by search and status
  ✓ operator can access dashboard and create shipment

   PASS  Tests\Feature\RestApiTest
  ✓ api shipments requires authentication
  ✓ operator can create shipment via api
  ✓ customer cannot create shipment via api
  ✓ api add tracking event and status advance
  ✓ api public tracking endpoint

   PASS  Tests\Feature\ShipmentLifecycleTest
  ✓ operator can create shipment with items and initial tracking
  ✓ customer can only view own shipments
  ✓ admin can delete shipment in created status
  ✓ cannot delete shipment already in transit

   PASS  Tests\Feature\TrackingStatusTransitionTest
  ✓ valid lifecycle status transitions
  ✓ invalid status transition is rejected
  ✓ cannot transition from terminal status delivered

  ✓ operator and owner can access print label

  Tests:    31 passed (91 assertions)
  Duration: 13.85s
```

---

## 🌐 Live Deployment Guide

CargoTrace can be deployed to any modern cloud VPS (DigitalOcean, AWS EC2, Railway, Hetzner, or cPanel):

1. **Web Server**: Configure Nginx or Apache pointing the Document Root to `/public`.
2. **Environment**:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=cargotrace_prod
   DB_USERNAME=cargotrace_user
   DB_PASSWORD=strong_password
   ```
3. **Optimization**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 📜 Attribution & License

- **Framework**: [Laravel](https://laravel.com) under the MIT License.
- **Mapping**: Map data &copy; [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors. Leaflet.js under the BSD-2-Clause license.
- **Weather API**: Live weather telemetry provided by [Open-Meteo](https://open-meteo.com/) under non-commercial CC BY 4.0.
- **Charts**: [Chart.js](https://www.chartjs.org/) under the MIT License.
- **Project License**: Developed for academic evaluation in Web Programming 2 — UAS.
