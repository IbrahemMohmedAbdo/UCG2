<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table='transactions';
    protected $fillable = [
        'user_id',
        'wallet_id',
        'order_id',
        'amount',
        'description',
        'appKey',
        'meta',
        'type',
    ];
}
