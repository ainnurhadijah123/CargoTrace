<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    // Baris ini wajib ada agar data dari form diizinkan masuk ke database
    protected $fillable = [
        'code',
        'name',
        'contact_info',
        'is_active',
    ];
}