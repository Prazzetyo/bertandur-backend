<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'md_city';
    protected $primaryKey = 'ID_CITY';
    public $incrementing = false;
    public $timestamps = false;
}
