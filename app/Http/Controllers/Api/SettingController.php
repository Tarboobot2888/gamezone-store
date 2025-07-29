<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Get application settings
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->get('group');
        
        $query = Setting::query();
        
        if ($group) {
            $query->where('group', $group);
        }
        
        // Only return public settings for non-admin users
        if (!auth()->user() || !auth()->user()->hasRole(['super-admin', 'admin'])) {
            $query->where('is_public', true);
        }
        
        $settings = $query->get()->keyBy('key');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get a specific setting
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }
        
        // Check if user can access this setting
        if (!$setting->is_public && (!auth()->user() || !auth()->user()->hasRole(['super-admin', 'admin']))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to setting'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    /**
     * Update settings (admin only)
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('manage-settings');
        
        $settings = $request->get('settings', []);
        
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        
        // Clear settings cache
        Cache::tags(['settings'])->flush();
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Get public settings for frontend
     */
    public function public(): JsonResponse
    {
        $settings = Cache::tags(['settings'])->remember('public_settings', 3600, function () {
            return Setting::where('is_public', true)
                         ->get()
                         ->keyBy('key')
                         ->map(function ($setting) {
                             return [
                                 'key' => $setting->key,
                                 'value' => $setting->value,
                                 'type' => $setting->type,
                             ];
                         });
        });
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get theme settings
     */
    public function theme(): JsonResponse
    {
        $themeSettings = Setting::where('group', 'theme')
                               ->where('is_public', true)
                               ->get()
                               ->keyBy('key');
        
        return response()->json([
            'success' => true,
            'data' => $themeSettings
        ]);
    }

    /**
     * Get payment settings
     */
    public function payment(): JsonResponse
    {
        $this->authorize('manage-settings');
        
        $paymentSettings = Setting::where('group', 'payment')
                                 ->get()
                                 ->keyBy('key');
        
        return response()->json([
            'success' => true,
            'data' => $paymentSettings
        ]);
    }

    /**
     * Get notification settings
     */
    public function notification(): JsonResponse
    {
        $this->authorize('manage-settings');
        
        $notificationSettings = Setting::where('group', 'notification')
                                      ->get()
                                      ->keyBy('key');
        
        return response()->json([
            'success' => true,
            'data' => $notificationSettings
        ]);
    }

    /**
     * Reset settings to default
     */
    public function reset(Request $request): JsonResponse
    {
        $this->authorize('manage-settings');
        
        $group = $request->get('group');
        
        if ($group) {
            Setting::where('group', $group)->delete();
        } else {
            Setting::truncate();
        }
        
        // Re-seed default settings
        \Artisan::call('db:seed', ['--class' => 'SettingSeeder']);
        
        // Clear cache
        Cache::tags(['settings'])->flush();
        
        return response()->json([
            'success' => true,
            'message' => 'Settings reset to default successfully'
        ]);
    }
}
