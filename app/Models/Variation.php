<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    use HasFactory;
    protected $table='product_color_size';
    protected $fillable = ['size_id', 'color_id', 'quantity', 'product_id','image_path'];
}
