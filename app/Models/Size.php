<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    protected $fillable = ['name','appKey'];
      public function variations()
    {
        return $this->belongsToMany(Color::class, 'product_color_size')
            ->withPivot('quantity', 'color_id')
            ->withTimestamps();
    }

    
	  protected static function booted()
    {
        static::deleting(function ($size) {
            // Detach related colors from the pivot table when a size is deleted
            $size->variations()->detach();
        });
    }

	
	
	
}
