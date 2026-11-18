<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case CREATED = 'CREATED';
    case BOOKED = 'BOOKED';
    case PICKED_UP = 'PICKED_UP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    // Opsional: Fungsi helper untuk validasi transisi (bisa dipakai di Service nanti)
    public function getNextValidStatuses(): array
    {
        return match($this) {
            self::CREATED => [self::BOOKED],
            self::BOOKED => [self::PICKED_UP],
            self::PICKED_UP => [self::IN_TRANSIT],
            self::IN_TRANSIT => [self::DELIVERED],
            self::DELIVERED => [], // Status terminal, tidak bisa mundur
        };
    }
}