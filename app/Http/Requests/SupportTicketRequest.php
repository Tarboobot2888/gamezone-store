<?php

namespace App\Http\Requests;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;

class SupportTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|string|in:general,payment,technical,account,order,refund',
            'priority' => 'sometimes|string|in:' . implode(',', [
                SupportTicket::PRIORITY_LOW,
                SupportTicket::PRIORITY_MEDIUM,
                SupportTicket::PRIORITY_HIGH,
                SupportTicket::PRIORITY_URGENT
            ]),
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => 'Please provide a subject for your ticket.',
            'description.required' => 'Please describe your issue in detail.',
            'category.required' => 'Please select a category for your ticket.',
            'category.in' => 'Please select a valid category.',
            'priority.in' => 'Please select a valid priority level.',
        ];
    }
}
