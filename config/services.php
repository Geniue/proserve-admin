<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'proserve' => [
        'api_key' => env('PROSERVE_API_KEY'),
        'max_upload_kb' => env('PROSERVE_MAX_UPLOAD_KB', 10240),
        'image_max_width' => env('PROSERVE_IMAGE_MAX_WIDTH', 1200),
        'image_max_height' => env('PROSERVE_IMAGE_MAX_HEIGHT', 1200),
        'image_quality' => env('PROSERVE_IMAGE_QUALITY', 80),
    ],

];
