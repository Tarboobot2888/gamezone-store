<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'package_type' => 'required|string|max:100',
            'package_value' => 'required|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'processing_time' => 'nullable|string|max:255',
            'requirements' => 'nullable|array',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['game_id'] = 'sometimes|required|exists:games,id';
            $rules['name'] = 'sometimes|required|string|max:255';
            $rules['price'] = 'sometimes|required|numeric|min:0';
            $rules['currency'] = 'sometimes|required|string|size:3';
            $rules['package_type'] = 'sometimes|required|string|max:100';
            $rules['package_value'] = 'sometimes|required|string|max:100';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'game_id.required' => 'Please select a game.',
            'game_id.exists' => 'The selected game is invalid.',
            'name.required' => 'Service name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be exactly 3 characters.',
            'package_type.required' => 'Package type is required.',
            'package_value.required' => 'Package value is required.',
        ];
    }
}
