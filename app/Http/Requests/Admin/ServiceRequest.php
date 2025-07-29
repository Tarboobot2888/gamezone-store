<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole(['super-admin', 'admin']);
    }

    public function rules()
    {
        $serviceId = $this->route('service') ? $this->route('service')->id : null;

        return [
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'type' => 'required|in:topup,boost,item,subscription',
            'category' => 'required|string|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'min_quantity' => 'integer|min:1',
            'max_quantity' => 'integer|min:1',
            'processing_time' => 'string|max:100',
            'requirements' => 'array',
            'requirements.*.field' => 'required|string|max:100',
            'requirements.*.label' => 'required|string|max:100',
            'requirements.*.type' => 'required|in:text,number,select,email',
            'requirements.*.required' => 'boolean',
            'requirements.*.options' => 'array',
        ];
    }

    public function messages()
    {
        return [
            'game_id.required' => 'Please select a game.',
            'game_id.exists' => 'Selected game does not exist.',
            'name.required' => 'Service name is required.',
            'description.required' => 'Service description is required.',
            'price.required' => 'Service price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be exactly 3 characters.',
            'type.required' => 'Service type is required.',
            'type.in' => 'Invalid service type selected.',
            'category.required' => 'Service category is required.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, png, jpg, or gif.',
            'image.max' => 'Image size cannot exceed 2MB.',
        ];
    }
}
