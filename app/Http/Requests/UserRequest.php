<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|unique:users|email',
            'phone' => 'required|regex:/^[0-9]+$/|unique:users',
            'city' => 'required',
			'type' => 'required|in:accountant,innerRepresentative,driver,storeManager,outerRepresentative,innerRepresentativesManager,outerRepresentativesManager,driversManager,storeManagersManager,accountantsManager',
            'birth' => 'required',
            'image' => 'required_if:type,driver',
			'personal_id'=>'required_if:type,driver,storeManager',
            'password' => 'required|confirmed',
        ];
    }
}
