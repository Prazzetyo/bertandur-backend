<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $table = 'md_product_category';
    protected $primaryKey = 'ID_PCAT';
    public $incrementing = true;
    public $timestamps = false;
}
