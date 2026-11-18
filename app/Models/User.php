<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'CUSTOMER',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        $roleValue = is_object($this->role) ? $this->role->value : $this->role;
        return $roleValue === 'ADMIN' || $roleValue === UserRole::ADMIN->value;
    }

    public function isOperator(): bool
    {
        $roleValue = is_object($this->role) ? $this->role->value : $this->role;
        return $roleValue === 'OPERATOR' || $roleValue === UserRole::OPERATOR->value;
    }

    public function isCustomer(): bool
    {
        $roleValue = is_object($this->role) ? $this->role->value : $this->role;
        return $roleValue === 'CUSTOMER' || $roleValue === UserRole::CUSTOMER->value;
    }

    /** Admin or logistics operator. */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    public function createdShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'created_by');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }
}
