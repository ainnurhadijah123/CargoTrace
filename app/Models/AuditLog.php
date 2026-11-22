<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($action, $model, $description, ?int $userId = null)
    {
        self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model' => $model,
            'description' => $description,
        ]);
    }
}