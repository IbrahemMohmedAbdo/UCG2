<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;
    protected $table='invoices';
    protected $fillable = ['product_color_size_id','product_id', 'original_price', 'quantity','amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);

    }


    public function variation()
    {
        return $this->belongsTo(Item::class);

    }
}
