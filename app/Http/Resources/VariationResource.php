<?php

namespace App\Http\Resources;

use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
{

    $colorId = $this->pivot->color_id;
    $productId = $this->pivot->product_id;
	$sizeId =Size::find($this->pivot->size_id);
	$sizeName = $sizeId->name;
  
    $variations = $this->variations()
        ->where('color_id', $colorId)
        ->where('product_id', $productId)
        ->get();

    $colorValue = $this->colorValue() ?? null;
    $colorName = $this->colorName() ?? null;

    $details = $variations->map(function ($variation) {
		$size = Size::find($variation->pivot->size_id);
        return [
            'size_id' => $variation->pivot->size_id,
            'size_name' => $size->name ?? null,
            'quantity' => $variation->pivot->quantity,
            'image_path' => $this->pivot->image_path,
            
        ];
    });
	if(!$variations)
	{
	
	return [];
		
	}else{
		    return [
        'color_id' => $colorId ?? null,
        'color_value' => $colorValue,
        'color_name' => $colorName,
        'details' => $details,
    ];
		
	}
	
	


}


	
    protected function colorValue()
{
    $color =Color::find($this->pivot->color_id);
    $colorValue=$color->value;
    return $colorValue;
}

protected function colorName()
{
 $color =Color::find($this->pivot->color_id);
    $colorName=$color->name ?? null;
    return $colorName;
}

protected function sizeName()
{
$size =Size::find($this->pivot->size_id);
$sizeName = $size->name ?? null;
return $sizeName;
}


	



}
