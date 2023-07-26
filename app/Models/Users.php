<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Users extends Model
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'ID_USER';
    protected $hidden = array('PASS_USER');
    public $incrementing = false;
    public $timestamps = false;
}
