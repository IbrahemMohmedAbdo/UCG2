<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['name','date' ,'price', 'user_id' ,'category_id', 'original_price','brand', 'status','appKey'];

    protected $casts = [
        'tags' => 'json',
    ];



    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable');
    }
	 public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function details()
    {
        return $this->hasMany(ProductDetail::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function getPriceAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

	public function getOriginalPriceAttribute($value)
{
    return number_format($value, 2, '.', '');
}



	   public function getStatusAttribute($value)
    {
        switch ($value) {
            case 0:
                return 'تسليم فوري';
            case 1:
                return 'حجز';
            case 2:
                return 'في الجمارك';
            // Add more cases as needed
            default:
                return 'unknown';
        }
    }


    public function orders()
{
    return $this->belongsToMany(Order::class)->withPivot('quantity','total_price');
}


// public function variations()
// {
//     return $this->belongsToMany(Color::class, 'product_color_size')
//                 ->withPivot('size_id', 'quantity', 'image_path');
// }
public function variations()
{
    return $this->hasMany(Variation::class);
}

public function getColorNameAttribute()
{
    return $this->variations->pluck('pivot.color.name')->unique()->implode(', ');
}


public function addVariation($size_id, $color_id, $quantity)
    {


        $this->variations()->attach($size_id, [
            'color_id' =>  $color_id,
            'quantity' => $quantity,


        ]);
    }

public function updateVariation($size_id, $color_id, $quantity)
{
    foreach ($this->variations as $variation) {
        if ($variation->id == $size_id) {
            // If the variation with the given size_id exists, update it
            $variation->pivot->update([
                'color_id' => $color_id,
                'quantity' => $quantity,
            ]);
            return;
        }
    }

    // If the variation doesn't exist, attach a new one
    $this->variations()->attach($size_id, [
        'color_id' => $color_id,
        'quantity' => $quantity,
    ]);
}



}
