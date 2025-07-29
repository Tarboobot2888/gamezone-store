<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\NewsletterSubscription;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    /**
     * Store contact form submission
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Send email to admin
            Mail::to(config('mail.admin_email', 'admin@gamezone.com'))
                ->send(new ContactFormMail($request->all()));

            // Store in database for tracking
            \DB::table('contact_submissions')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.contact_form_sent')
            ]);

        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.contact_form_error')
            ], 500);
        }
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeNewsletter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:newsletter_subscriptions,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email')
            ], 422);
        }

        try {
            NewsletterSubscription::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'subscribed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.newsletter_subscribed')
            ]);

        } catch (\Exception $e) {
            \Log::error('Newsletter subscription error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.newsletter_error')
            ], 500);
        }
    }
}
