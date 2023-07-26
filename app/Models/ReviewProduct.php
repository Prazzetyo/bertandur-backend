<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewProduct extends Model
{
    use HasFactory;
    protected $table = 'review_product';
    protected $primaryKey = 'ID_REVIEW_PRODUCT';
    public $incrementing = true;
    public $timestamps = false;
}
