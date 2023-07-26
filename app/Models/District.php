<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'md_district';
    protected $primaryKey = 'ID_DISTRICT';
    public $incrementing = false;
    public $timestamps = false;
}
