<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'md_province';
    protected $primaryKey = 'ID_PROVINCE';
    public $incrementing = false;
    public $timestamps = false;
}
