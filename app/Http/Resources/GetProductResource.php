<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
	public function toArray(Request $request): array
    {
     
$this->load('media');
        return [

            'id' => $this->id ?? 'not-found',
            'videos' =>  null,



            'name' => $this->name ?? 'not-found',
            'brand' => $this->brand ?? 'not-found',
			'price'=>$this->price ?? null,
			'original_price'=>$this->original_price ?? null,
            'category_name' => $this->category->name ?? 'not-found',
			'status'=>$this->status,

			'date'=>$this->date ?? null,
			'created_at'=>$this->created_at,


          'variations' =>[new VariationResource($this->variations->first())],



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
