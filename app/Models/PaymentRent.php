<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRent extends Model
{
    protected $table = 'payment_rent_1';
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    public $timestamps = false;
}
