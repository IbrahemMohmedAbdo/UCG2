<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
    {
        $imagePath = DB::table('product_color_size')
        ->where('product_id', $this->pivot->product_id)
        ->where('size_id', $this->pivot->size_id)
        ->where('color_id', $this->pivot->color_id)
        ->value('image_path');

        return[
            'id' => $this->id ?? 'not-found',
            'videos' =>null,

            'name' => $this->name ?? 'not-found',

            'brand' => $this->brand ?? 'not-found',
            'price' => $this->price ?? 'not-found',
			'created_at'=>$this->created_at,
            'variations'=>[
                         //'total_price' => $this->price * $this->pivot->quantity,

			            'sizeId' => $this->pivot->size_id,
			             'colorId' => $this->pivot->color_id,
			            'quantity' => $this->pivot->quantity,

                        'image_path' => $imagePath,
				]


        ];
    }
	protected function getColorNameByColorId($colorId)
    {
        $color = Color::find($colorId);
			
        return $color ? $color->name : null;
    }
}
