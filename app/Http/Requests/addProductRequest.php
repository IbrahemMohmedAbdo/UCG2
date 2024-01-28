<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class addProductRequest extends FormRequest
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
            'name' => 'required|string',
			//'images' =>'array',
           'videos' => 'sometimes',
			'date'=>'sometimes|string',
			//'file'=>'file|mimes:jpeg,png,jpg,gif,svg,mp4|max:50000',
            'price' => 'required|numeric',
			'original_price'=>'numeric',
           // 'quantity' => 'required|numeric',
			'user_id'=>'exists:users,id,type,storeManager',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string',
			'status' => 'required|in:0,1,2',
			'colors' => 'required|array',
            'colors.*.colorId' => 'required|exists:colors,id',
            'colors.*.sizeId' => 'required|exists:sizes,id',
            'colors.*.quantity' => 'required|numeric',
            'colors.*.images' => 'required|image',
			 /*'variations' => 'required|array',
            'variations.*.size_id' => 'required|exists:sizes,id',
            'variations.*.color_id' => 'required|exists:colors,id',
            'variations.*.quantity' => 'required|integer|min:1',*/

        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'price.numeric' => 'The price must be a numeric value.',

        ];
    }
	  public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation logic for original price and price
            $originalPrice = $this->input('original_price');
            $price = $this->input('price');

            if ($originalPrice >= $price) {
                $validator->errors()->add('original_price', 'Original price must be smaller than the price');
            }
        });
    }
}
