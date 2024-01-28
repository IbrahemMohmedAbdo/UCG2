<?php

namespace App\Http\Resources;

use App\Models\Size;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationResource extends JsonResource
{
 /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Assuming 'color' relationship is defined in the ColorVariation model
      //  $color = $this->whenLoaded('variations');
		  $colorValues = $this->getColorValues();
		
        return [
            'color_id' => $this->pivot->color_id ?? null,
            'color_value'=>$this->colorValue() ?? null,
            'color_name'=>$this->colorName() ?? null,
            'details' => [
                [
                    'size_id' => $this->pivot->size_id,
                    'size_name' => $this->sizeName() ?? null,
                    'quantity' => $this->pivot->quantity,
                    'image_path' => $this->pivot->image_path,
                    // Add more pivot data as needed
                ],
                // You can include additional variations if available
            ],
			//  'color_values' => $colorValues,
        ];
    }
	
protected function getColorValues()
{
    return Color::whereIn('id', $this->variations->pluck('pivot.color_id')->unique()->toArray())
        ->pluck('value')
        ->all();
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
    $colorName=$color->name;
    return $colorName;
}

protected function sizeName()
{
$size =Size::find($this->pivot->size_id);
$sizeName = $size->name ?? null ;
return $sizeName;
}


}
