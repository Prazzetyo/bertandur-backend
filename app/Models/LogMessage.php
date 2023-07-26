<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMessage extends Model
{
    use HasFactory;
    protected $table = 'log_message';
    protected $primaryKey = 'ID_LOG';
    public $incrementing = true;
    public $timestamps = false;
}
