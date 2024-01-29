<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'client_name'=>$this->client_name,
            'client_number'=>$this->client_number,
            'client_location'=>$this->client_location,
			'client_city'=>$this->client_City,
            'representative_id'=>$this->representative_id,
            'representative_Name'=>$this->getrepresentativeName() ?? null,
            'store_manager_Name'=>$this->getstoreManagerName() ?? null,
            'driver_Name'=>$this->getdriverName() ?? null,
			'shippment_type'=>($this->shippment_type == 0) ? 'داخلي' : 'خارجي',
			'orderId_shipping'=>$this->orderId_shipping ?? null,
			'order_shipping_code'=>$this->order_shipping_code ?? null,
			'scan_code_id'=>$this->scan_code_id ?? null,
			 'status'=>$this->status,
			'total_price' => $this->getTotalPrice(),
			'comment'=>$this->comment ?? null,
			'created_at'=>$this->created_at,
          'products'=>OrderProductResource::collection($this->products),
        ];
    }
	private function getTotalPrice()
{

    $totalPrice = 0;


    foreach ($this->products as $product) {
        $totalPrice += $product->pivot->total_price;
    }

    return $totalPrice;
}

protected function getRepresentativeName()
{
    return User::where('id',$this->representative_id)->value('name');
}

protected function getstoreManagerName()
{
    return User::where('id',$this->storeManager_id)->value('name');
}

protected function getdriverName()
{
    return User::where('id',$this->driver_id)->value('name');
}


}
