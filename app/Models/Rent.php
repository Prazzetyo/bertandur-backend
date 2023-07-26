<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $table = 'rent';
    protected $primaryKey = 'ID_RENT';
    public $incrementing = false;
    public $timestamps = false;
}
