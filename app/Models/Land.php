<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Land extends Model
{
    protected $table = 'land';
    protected $primaryKey = 'ID_LAND';
    public $incrementing = false;
    public $timestamps = false;
}
