<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'GameZone', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Professional gaming marketplace and top-up platform', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_keywords', 'value' => 'gaming, marketplace, top-up, accounts', 'type' => 'string', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'support@gamezone.com', 'type' => 'string', 'group' => 'general'],
            ['key' => 'support_phone', 'value' => '+1-555-0123', 'type' => 'string', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general'],
            ['key' => 'default_language', 'value' => 'en', 'type' => 'string', 'group' => 'general'],
            ['key' => 'default_theme', 'value' => 'dark', 'type' => 'string', 'group' => 'general'],

            // Payment Settings
            ['key' => 'stripe_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'paypal_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'min_order_amount', 'value' => 1.00, 'type' => 'string', 'group' => 'payment'],
            ['key' => 'max_order_amount', 'value' => 1000.00, 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_processing_fee', 'value' => 0.03, 'type' => 'string', 'group' => 'payment'],

            // Email Settings
            ['key' => 'order_confirmation_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'email'],
            ['key' => 'order_status_notifications', 'value' => true, 'type' => 'boolean', 'group' => 'email'],
            ['key' => 'marketing_emails_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'email'],

            // Security Settings
            ['key' => 'two_factor_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'email_verification_required', 'value' => true, 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'max_login_attempts', 'value' => 5, 'type' => 'integer', 'group' => 'security'],
            ['key' => 'session_timeout', 'value' => 120, 'type' => 'integer', 'group' => 'security'],

            // Social Media
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/gamezone', 'type' => 'string', 'group' => 'social'],
            ['key' => 'twitter_url', 'value' => 'https://twitter.com/gamezone', 'type' => 'string', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/gamezone', 'type' => 'string', 'group' => 'social'],
            ['key' => 'discord_url', 'value' => 'https://discord.gg/gamezone', 'type' => 'string', 'group' => 'social'],

            // Features
            ['key' => 'guest_checkout_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'wishlist_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'reviews_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'referral_program_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'features'],

            // Maintenance
            ['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean', 'group' => 'maintenance'],
            ['key' => 'maintenance_message', 'value' => 'We are currently performing scheduled maintenance. Please check back soon.', 'type' => 'string', 'group' => 'maintenance'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
