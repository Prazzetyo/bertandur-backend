<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

    protected $table = 'tutorial';
    protected $primaryKey = 'ID_TUTORIAL';
    public $incrementing = true;
    public $timestamps = false;
}
