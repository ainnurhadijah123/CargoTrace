<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case OPERATOR = 'OPERATOR';
    case CUSTOMER = 'CUSTOMER';

    // Opsional: Jika Anda butuh mengambil label untuk ditampilkan di UI (misalnya "Administrator")
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::OPERATOR => 'Logistic Operator',
            self::CUSTOMER => 'Customer',
        };
    }
}