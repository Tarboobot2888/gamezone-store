<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:super-admin|admin']);
    }

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $rules = [
            'app_name' => 'required|string|max:255',
            'app_description' => 'required|string|max:500',
            'app_keywords' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'support_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'discord_url' => 'nullable|url|max:255',
            'default_currency' => 'required|string|size:3',
            'default_language' => 'required|string|size:2',
            'supported_languages' => 'required|string',
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string|max:500',
            'allow_registration' => 'boolean',
            'email_verification_required' => 'boolean',
            'max_login_attempts' => 'required|integer|min:1|max:10',
            'session_lifetime' => 'required|integer|min:1|max:1440',
            'stripe_enabled' => 'boolean',
            'paypal_enabled' => 'boolean',
            'min_order_amount' => 'required|numeric|min:0',
            'max_order_amount' => 'required|numeric|min:0',
            'order_processing_fee' => 'required|numeric|min:0|max:100',
            'auto_approve_orders' => 'boolean',
            'order_expiry_hours' => 'required|integer|min:1|max:168',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'backup_enabled' => 'boolean',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_retention_days' => 'required|integer|min:1|max:365',
            'analytics_enabled' => 'boolean',
            'google_analytics_id' => 'nullable|string|max:20',
            'facebook_pixel_id' => 'nullable|string|max:20',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->all() as $key => $value) {
            if (array_key_exists($key, $rules)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        // Clear settings cache
        Cache::forget('app_settings');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Settings updated successfully')
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', __('Settings updated successfully'));
    }

    public function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => __('Setting not found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $setting->value
        ]);
    }

    public function updateSetting(Request $request, $key)
    {
        $request->validate([
            'value' => 'required'
        ]);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $request->value]
        );

        // Clear settings cache
        Cache::forget('app_settings');

        return response()->json([
            'success' => true,
            'message' => __('Setting updated successfully')
        ]);
    }

    public function clearCache()
    {
        Cache::flush();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Cache cleared successfully')
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', __('Cache cleared successfully'));
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Send test email
            \Mail::raw('This is a test email from GameZone Marketplace.', function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Test Email - GameZone Marketplace');
            });

            return response()->json([
                'success' => true,
                'message' => __('Test email sent successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to send test email: ') . $e->getMessage()
            ], 500);
        }
    }
}
