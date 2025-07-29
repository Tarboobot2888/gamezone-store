<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportTicketReplyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'message' => 'required|string|max:5000',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'Please enter your reply message.',
            'message.max' => 'Reply message cannot exceed 5000 characters.',
        ];
    }
}
