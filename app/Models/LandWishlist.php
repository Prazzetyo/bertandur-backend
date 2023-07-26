<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandWishlist extends Model
{
    use HasFactory;
    protected $table = 'land_wishlist';
    protected $primaryKey = 'ID_LW';
    public $incrementing = true;
    public $timestamps = false;
}
