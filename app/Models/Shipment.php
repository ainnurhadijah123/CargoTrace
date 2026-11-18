<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TrackingEvent;
use App\Models\ShipmentException;
use App\Models\Notification;
use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\ShipmentItem;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'created_by',
        'cargo_type',
        'weight_kg',
        'expected_departure_at',
        'expected_arrival_at',
        'status',
        'shipping_cost',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'expected_departure_at' => 'datetime',
            'expected_arrival_at' => 'datetime',
            'weight_kg' => 'decimal:2',
            'shipping_cost' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'desc');
    }

    public function chronologicalEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class)->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function getLatestEventAttribute()
    {
        return $this->trackingEvents->first();
    }

    public function getProgressPercentAttribute(): int
    {
        return match ($this->status) {
            'CREATED' => 10,
            'BOOKED' => 30,
            'PICKED_UP' => 50,
            'IN_TRANSIT' => 75,
            'EXCEPTION' => 75,
            'DELIVERED' => 100,
            'CANCELLED' => 0,
            default => 20,
        };
    }
}
