<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;


class CheckAvailableQuantity implements Rule
{
    public function passes($attribute, $value)
    {
        // $attribute is the field name ('quantity' in this case)
        // $value is the selected quantity

        // Retrieve the product
        $productId = request('id');
        $product = Product::find($productId);
	
		
	
        return $product && $value >= $product->quantity;
    }


    public function message()
    {
        return 'The selected quantity is not available for this product.';
    }
}
