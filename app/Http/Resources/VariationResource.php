<?php

namespace App\Http\Resources;

use App\Models\Color;
use App\Models\Size;
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
        return [
            'color_id' => $this->color_id,
            'color_value' => $this->colorValue(),
            'color_name' => $this->colorName(),
            'details' => $this->mapDetails(),
        ];
    }

    protected function colorValue()
    {
        $color = Color::find($this->color_id);
        return optional($color)->value;
    }

    protected function colorName()
    {
        $color = Color::find($this->color_id);
        return optional($color)->name;
    }

    protected function mapDetails()
    {

            $sizeId = $this->size_id;
            $size = Size::find($sizeId);

            return [
                'size_id' => $sizeId,
                'size_name' => optional($size)->name,
                'quantity' => $this->quantity,
                'image_path' => $this->image_path,
            ];


        return null;
    }
}
