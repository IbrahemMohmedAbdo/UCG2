<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneOrderResource extends JsonResource
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
            'client_Name'=>$this->client_name,
            'price' =>  $this->products->sum('pivot.total_price'),

          'status'=>$this->status,
			'created_at'=>$this->created_at,
        ];
    }
    private function getTotalPrice()
    {
        // Calculate the total price by summing the total_price values from the pivot table
        return $this->products->sum('pivot.total_price');
    }
}
