<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'type',
        'severity',
        'status',
        'description',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN' || $this->status === 'INVESTIGATING';
    }

    public function isResolved(): bool
    {
        return $this->status === 'RESOLVED';
    }
}
