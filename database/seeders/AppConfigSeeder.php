<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'key' => 'app_name',
                'value' => 'ProServe',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name displayed in the app',
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@proserve.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Primary contact email address',
            ],
            [
                'key' => 'support_phone',
                'value' => '+1-800-PRO-SERV',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Customer support phone number',
            ],
            [
                'key' => 'terms_url',
                'value' => 'https://proserve.com/terms',
                'type' => 'string',
                'group' => 'legal',
                'description' => 'Terms and conditions URL',
            ],
            [
                'key' => 'privacy_url',
                'value' => 'https://proserve.com/privacy',
                'type' => 'string',
                'group' => 'legal',
                'description' => 'Privacy policy URL',
            ],
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Default currency code',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Currency symbol',
            ],
            [
                'key' => 'booking_cancellation_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'booking',
                'description' => 'Hours before service to allow cancellation',
            ],
            [
                'key' => 'enable_push_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable push notifications',
            ],
            [
                'key' => 'enable_in_app_chat',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable in-app chat feature',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable maintenance mode',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'We are currently performing system maintenance. Please check back soon.',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Message displayed during maintenance',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/proserve',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook page URL',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/proserve',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram profile URL',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/proserve',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter profile URL',
            ],
        ];

        foreach ($configs as $config) {
            AppConfig::create($config);
        }
    }
}
