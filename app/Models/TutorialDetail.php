<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialDetail extends Model
{
    use HasFactory;

    protected $table = 'tutorial_detail';
    protected $primaryKey = 'ID_TD';
    public $incrementing = true;
    public $timestamps = false;
}
