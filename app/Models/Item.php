<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table='product_color_size';
    protected $fillable = ['size_id', 'color_id', 'quantity', 'product_id','image_path'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function supplies()
    {
        return $this->hasMany(Supply::class);
    }


}
