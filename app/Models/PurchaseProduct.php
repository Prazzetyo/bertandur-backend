<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseProduct extends Model
{
    use HasFactory;
    protected $table = 'purchase_product';
    protected $primaryKey = 'ID_PURCHASE';
    public $incrementing = false;
    public $timestamps = false;
}
