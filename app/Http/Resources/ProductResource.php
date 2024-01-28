<?php

namespace App\Http\Resources;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
	$pp = [];

foreach ($this->variations as $cc) {
    $colorIdExists = false;

    foreach ($pp as $ff) {
        if ($cc->pivot->color_id == $ff->pivot->color_id) {
            $colorIdExists = true;
            break; // Exit the inner loop once a match is found
        }
    }

    if (!$colorIdExists) {
        $pp[] = $cc;
    }
}
		$this->load('media');
        return [

            'id' => $this->id ?? 'not-found',
          'videos' => $this->media->filename ?? null ? asset('images/' . $this->media->filename) : null,

            'name' => $this->name ?? 'not-found',
            'brand' => $this->brand ?? 'not-found',
			'price'=>$this->price ?? null,
			'original_price'=>$this->original_price ?? null,
            'category_name' => $this->category->name ?? 'not-found',
			'status'=>$this->status,

			'date'=>$this->date ?? null,
			'created_at'=>$this->created_at,
			  /* 'variations' => $this->variations->map(function ($variation) {

                    return [
                        'size' => $variation->name, // Assuming 'name' is the attribute in your Size model
                        'color' => $this->getColorNameByColorId($variation->pivot->color_id),
                        'quantity' => $variation->pivot->quantity,
                    ];
                }),*/

          'variations' => VariationResource::collection($pp),



        ];
    }
	 protected function getVariations()
    {
        return VariationResource::collection($this->variations);
    }


    protected function getColorNameByColorId($colorId)
    {
        $color = Color::find($colorId);

        return $color ? $color->name : null;
    }
}
