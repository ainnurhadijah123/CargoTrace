<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TrackingEventController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;

// ==========================================
// 1. PUBLIC ROUTES (GUEST & TRACKING)
// ==========================================

// Public tracking portal
Route::get('/track', [TrackingController::class, 'index'])->name('track.index');
Route::post('/track', [TrackingController::class, 'search'])->name('track.search');

// Root route (Redirects authenticated users to role dashboard, or guest to tracking portal)
Route::get('/', function () {
    if (Auth::check()) {
        $role = is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role;
        return match ($role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'OPERATOR' => redirect()->route('operator.dashboard'),
            default => redirect()->route('customer.dashboard'),
        };
    }
    return redirect()->route('track.index');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 2. PROTECTED ROUTES (AUTHENTICATION REQUIRED)
// ==========================================
Route::middleware('auth')->group(function () {
    
    // Shared Operational Actions (Admin & Operator)
    Route::post('/shipments/{shipment}/tracking', [TrackingEventController::class, 'store'])->name('tracking.store');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');
    Route::patch('/exceptions/{exception}/resolve', [ExceptionController::class, 'resolve'])->name('exceptions.resolve');

    // In-App Notifications
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // Print Shipment Resi (Accessible by Admin, Operator, and Authorized Customer)
    Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
    Route::get('/admin/shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('admin.shipments.print');

    // Interactive REST API Documentation Portal & Postman Collection (Protected: Admin Only)
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::get('/api-docs', function () {
            return view('api_docs');
        })->name('api.docs');

        Route::get('/api-docs/download-postman', function () {
            return response()->download(base_path('docs/cargotrace_postman_collection.json'), 'cargotrace_postman_collection.json');
        })->name('api.docs.download');
    });

    // Administrator Section
    Route::middleware(['role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        
        Route::resource('locations', LocationController::class)->parameters([
            'locations' => 'location'
        ]);
        
        Route::resource('carriers', CarrierController::class);
        Route::resource('shipments', ShipmentController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
        Route::resource('users', UserController::class);
    });

    // Logistics Operator Section
    Route::middleware(['role:OPERATOR'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'operator'])->name('dashboard');
        Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{shipment_number}/detail', [DashboardController::class, 'showShipmentOperator'])->name('shipments.show');
        Route::get('/shipments/{shipment_number}/edit', [DashboardController::class, 'editStatus'])->name('shipments.edit');
        Route::put('/shipments/{shipment_number}/update', [DashboardController::class, 'updateStatus'])->name('shipments.update');
    });

    // Customer Section
    Route::middleware(['role:CUSTOMER'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'customer'])->name('dashboard');
        Route::get('/shipments/{shipment_number}', [DashboardController::class, 'showShipment'])->name('shipments.show');
    });
});
