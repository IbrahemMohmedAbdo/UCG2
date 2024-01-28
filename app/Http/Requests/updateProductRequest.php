<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
			'videos' =>'sometimes',
            //'images.*' => 'sometimes',
			'date'=>'sometimes|string',
            'price' => 'sometimes|numeric',
			'original_price'=> 'sometimes|numeric',
          //  'quantity' => 'sometimes|numeric',
			'user_id'=>'sometimes|exists:users,id,type,storeManager',
            'category_id' => 'sometimes|exists:categories,id',
            'brand' => 'sometimes|string|max:255',
			'status' => 'sometimes|in:0,1,2',
			'colors' => 'sometimes|array',
            'colors.*.colorId' => 'sometimes|exists:colors,id',
            'colors.*.sizeId' => 'sometimes|exists:sizes,id',
            'colors.*.quantity' => 'sometimes|numeric',
            'colors.*.images' => 'sometimes|image',
			/* 'variations' => 'sometimes|array',
            'variations.*.size_id' => 'sometimes|exists:sizes,id',
            'variations.*.color_id' => 'sometimes|exists:colors,id',
            'variations.*.quantity' => 'sometimes|integer|min:1',*/
        ];
    }
	  public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateOriginalPriceSmallerThanPrice($validator);
        });
    }

    private function validateOriginalPriceSmallerThanPrice($validator)
    {
        $originalPrice = $this->input('original_price');
        $price = $this->input('price');

        if ($originalPrice >= $price) {
            $validator->errors()->add('original_price', 'Original price must be smaller than the price');
        }
    }
}
