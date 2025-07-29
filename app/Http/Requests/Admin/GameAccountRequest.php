<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GameAccountRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole(['super-admin', 'admin']);
    }

    public function rules()
    {
        return [
            'game_id' => 'required|exists:games,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'level' => 'nullable|integer|min:1',
            'rank' => 'nullable|string|max:100',
            'server' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'images' => 'sometimes|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'credentials' => 'required|array',
            'credentials.username' => 'required|string|max:255',
            'credentials.password' => 'required|string|max:255',
            'credentials.email' => 'nullable|email|max:255',
            'features' => 'array',
            'features.*' => 'string|max:255',
            'status' => 'required|in:available,unavailable,sold',
            'is_featured' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'game_id.required' => 'Please select a game.',
            'game_id.exists' => 'Selected game does not exist.',
            'title.required' => 'Account title is required.',
            'description.required' => 'Account description is required.',
            'price.required' => 'Account price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be exactly 3 characters.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'All files must be images.',
            'images.*.mimes' => 'Images must be jpeg, png, jpg, or gif.',
            'images.*.max' => 'Each image cannot exceed 2MB.',
            'credentials.required' => 'Account credentials are required.',
            'credentials.username.required' => 'Username is required.',
            'credentials.password.required' => 'Password is required.',
            'status.required' => 'Account status is required.',
            'status.in' => 'Invalid account status.',
        ];
    }
}
