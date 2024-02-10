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
             'total_price_after_discount' => $this->getTotalPriceAfterDiscount() ?? null,

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


public function getTotalPriceAfterDiscount()
{
    // Initialize total price after discount
    $totalPriceAfterDiscount = 0;

    // Iterate through the products and accumulate total price after discount
    foreach ($this->products as $product) {
        $totalPriceAfterDiscount += $product->pivot->total_price_after_discount;
    }

    return $totalPriceAfterDiscount;
}




}
