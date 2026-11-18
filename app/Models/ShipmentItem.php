<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasFactory;

    // INI KUNCI UTAMANYA: Izinkan kolom-kolom ini untuk diisi!
    protected $fillable = [
        'shipment_id',
        'cargo_type',
        'description',
        'weight',
        'weight_kg',
        'quantity',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}