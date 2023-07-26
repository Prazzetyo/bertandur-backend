<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLand extends Model
{
    use HasFactory;
    protected $table = 'review_land';
    protected $primaryKey = 'ID_REVIEW_LAND';
    public $incrementing = true;
    public $timestamps = false;
}
