# CargoTrace — System Architecture & Design Guidelines

## 1. Architectural Overview

CargoTrace follows a strict **Layered Architecture** pattern adhering to clean architecture principles. HTTP request handling is decoupled from business domain logic to maintain high maintainability, testability, and auditability.

```mermaid
flowchart TD
    subgraph Client ["Client Presentation Layer"]
        B[Web Browser / Blade UI]
        P[Postman / REST Clients]
    end

    subgraph Routing ["Routing & Middleware Layer"]
        R[Laravel Routing & Middleware]
        A[CheckRole & Sanctum Auth Middleware]
    end

    subgraph ControllerLayer ["HTTP Controller Layer (Thin)"]
        WC[Web Controllers]
        AC[REST API Controllers]
        FR[Form Requests Validation]
    end

    subgraph ServiceLayer ["Business Domain Service Layer"]
        SS[ShipmentService]
        TS[TrackingService]
        ES[EtaService]
        XS[ExceptionService]
        WS[WeatherService / GeocodingService]
    end

    subgraph PersistenceLayer ["Data & External Persistence"]
        EL[Eloquent ORM Models]
        DB[(MySQL 8+ / SQLite)]
        EXT[Open-Meteo & OSM Nominatim External APIs]
    end

    B --> R
    P --> R
    R --> A
    A --> FR
    FR --> WC
    FR --> AC
    WC --> SS & TS & ES & XS & WS
    AC --> SS & TS & ES & XS & WS
    SS & TS & ES & XS --> EL
    WS --> EXT
    EL --> DB
```

---

## 2. Layer Responsibilities & Architectural Rules

| Layer | Primary Responsibility | Components / Files |
|---|---|---|
| **Routing & Middleware** | URL dispatching, authentication token verification (`auth:sanctum`), and role authorization gates (`role:ADMIN`, `role:OPERATOR`, `role:CUSTOMER`). | `routes/web.php`, `routes/api.php`, `App\Http\Middleware\CheckRole` |
| **Form Requests** | Input sanitization, data validation rules, and preliminary request-level permissions. | `App\Http\Requests\StoreShipmentRequest` |
| **Controllers (Thin)** | HTTP concerns only: extracts validated input, invokes services, delegates database transactions to services, formats Blade views or JSON envelopes. | `App\Http\Controllers\*`, `App\Http\Controllers\Api\*` |
| **Service Layer** | Encapsulates all domain rules, state machine transitions, delay telemetry math, exception resolution, and transactional integrity. | `ShipmentService`, `TrackingService`, `EtaService`, `ExceptionService`, `WeatherService` |
| **Eloquent Models** | Data schema modeling, relationship cardinality (`hasMany`, `belongsTo`), type casting, accessors, and guarded mass assignment. | `Shipment`, `TrackingEvent`, `ShipmentException`, `Carrier`, `Location`, `User`, `AuditLog`, `Notification` |
| **External Integrations** | Resilient external telemetry requests with timeouts, error handling, caching, and graceful fallbacks. | `WeatherService` (Open-Meteo), `GeocodingService` (Nominatim) |

---

## 3. Core Business Rules & State Machines

### 3.1 Shipment Lifecycle State Transitions

Status transitions are strictly validated in `TrackingService::recordEvent()` and cannot be arbitrarily modified via direct input.

```mermaid
stateDiagram-v2
    [*] --> CREATED: Shipment registered
    CREATED --> BOOKED: Slot confirmed
    CREATED --> CANCELLED: Order cancelled
    BOOKED --> PICKED_UP: Cargo received at origin hub
    BOOKED --> CANCELLED: Order cancelled
    PICKED_UP --> IN_TRANSIT: Departed on voyage
    IN_TRANSIT --> IN_TRANSIT: Checkpoint scan update
    IN_TRANSIT --> EXCEPTION: Incident flagged (Weather / Customs)
    EXCEPTION --> IN_TRANSIT: Incident resolved
    EXCEPTION --> DELIVERED: Direct delivery upon release
    IN_TRANSIT --> DELIVERED: Arrived at final destination
    DELIVERED --> [*]: Terminal state (Immutable)
    CANCELLED --> [*]: Terminal state (Immutable)
```

### 3.2 Deterministic ETA & Delay Rule (`EtaService`)

Delay detection is rule-based and deterministic:
1. **Target**: `$expectedArrival = $shipment->expected_arrival_at`
2. **Comparison Point**:
   - If `$shipment->status === 'DELIVERED'`: uses the delivery tracking event timestamp.
   - If active (`IN_TRANSIT`, `EXCEPTION`): uses `now()`.
3. **Calculation**:
   $$\text{delayHours} = \text{ceil}(\text{ComparisonPoint} - \text{ExpectedArrival})$$
4. **Severity Matrix**:
   - $\le 6 \text{ hours}$: `LOW` severity
   - $> 6 \text{ and } \le 24 \text{ hours}$: `MEDIUM` severity
   - $> 24 \text{ hours}$: `HIGH` severity (triggers Priority Operational Queue)

---

## 4. Security & Access Control Policy (RBAC)

1. **Server-Side Enforcement**: All routes are guarded by `CheckRole` middleware on the server. Hiding frontend buttons is never relied upon as a security mechanism.
2. **Customer Scoping**: Customers can only view shipments where `customer_id === auth()->id()`. Querying another customer's shipment by ID returns a `403 Forbidden` or `404 Not Found`.
3. **Audit Trail**: High-consequence mutations (`Shipment Created`, `Status Transition`, `Exception Reported`, `Exception Resolved`, `Shipment Deleted`) automatically trigger `AuditLog::record()`.
4. **Secret Management**: API keys and database credentials reside exclusively in `.env` and are strictly excluded from Git commits via `.gitignore`.
