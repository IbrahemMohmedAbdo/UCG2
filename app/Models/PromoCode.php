<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $fillable = ['name','valid','value'];

    public function order()
    {
        return $this->hasOne(Order::class);
    }


    public function getNameAttribute()
    {
    
        $prefix = 'CG';

        return $prefix . $this->attributes['name'];
    }




}
