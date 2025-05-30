<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'product_id',
        'stripe_payment_intent_id',
        'amount',
        'status',
        'email',
    ];
}
