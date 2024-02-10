<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_Name',
        'client_Number',
        'client_Location',
		 'client_City',
        'client_Cuurent_Location',
        'client_Cuurent_City',
        'representative_Id',
		'status',
		'shippment_type',
     	'user_id',
        'appKey',
    ];




  protected $enumStatus = ['packed', 'delivered', 'pending','cancelled','delivering','returned','postponed'];


    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }



public function products()
{
    return $this->belongsToMany(Product::class)->withPivot('quantity','total_price','color_id','size_id','total_price_after_discount');
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function promoCode()
{
    return $this->belongsTo(PromoCode::class);
}



}
