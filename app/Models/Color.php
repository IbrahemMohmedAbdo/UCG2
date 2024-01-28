<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'value', 'appKey'];

    public function variations()
    {
        return $this->belongsToMany(Size::class, 'product_color_size')
            ->withPivot('quantity','color_id')
            ->withTimestamps();
    }
}
