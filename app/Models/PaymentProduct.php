<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProduct extends Model
{
    use HasFactory;
    protected $table = 'payment_product_1';
    protected $primaryKey = 'order_id';
    public $incrementing = true;
    public $timestamps = false;
}
