<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'client_name'=>$this->client_Name,
            'client_number'=>$this->client_Number,
            'client_location'=>$this->client_Location,
             'representative_id'=>$this->representative_Id,
		    'shippment_type'=>($this->shippment_type == 0) ? 'داخلي' : 'خارجي',
			'created_at'=>$this->created_at,
			 'total_price' => $this->getTotalPrice(),
			

          'products'=>OrderProductResource::collection($this->products),
        ];
    }
private function getTotalPrice()
{
    // Initialize total price
    $totalPrice = 0;

    // Iterate through the products and accumulate total price
    foreach ($this->products as $product) {
        $totalPrice += $product->pivot->total_price;
    }

    return $totalPrice;
}
}
