<?php

namespace App\Http\Requests;
use App\Rules\CheckAvailableQuantity;
use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
            'client_name' => 'required|string|max:255',
            'client_number' => ['required', 'string'],
            'client_location' => 'string|max:255',
			 'client_city'=>'string|max:255',
            'client_cuurent_location'=>'string|max:255',
            'client_cuurent_city'=>'string|max:255',
           // 'representative_Id' => 'exists:users,id,type,innerRepresentative',
            // 'product_id' => 'required|exists:products,id', // Adjust this to your model

            // 'quantities' => 'required|integer',
           'products' => 'required|array',
          'products.*.id' => 'required|exists:products,id',
			 'variations' => 'required|array',
            'variations.*.size_id' => 'required|exists:sizes,id',
            'variations.*.color_id' => 'required|exists:colors,id',
            'variations.*.quantity' => 'required|integer',
			
         // 'products.*.quantity' =>  ['required', 'integer' ,'min:1'],

            // Add validation rules for other fields in your order
        ];
    }
}
