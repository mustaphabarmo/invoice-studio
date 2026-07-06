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
    'kudisms' => [
        'otpurl' => env('KUDISMS_OTP_API_URL', 'https://my.kudisms.net/api/otp'), // Use default if not in .env
        'coperateurl' => env('KUDISMS_COPERATE_API_URL', 'https://my.kudisms.net/api/corporate'), // Use default if not in .env
        'token' => env('KUDISMS_API_TOKEN'),
        'sender_id' => env('KUDISMS_SENDER_ID'),
        'app_name_code' => env('KUDISMS_APP_NAME_CODE'),
        'template_code' => env('KUDISMS_TEMPLATE_CODE'),
    ],
    'xpouch' => [
        'base_url' => env('XPOUCH_BASE_URL', 'https://backend.xpouch.co/api/merchant/v1'),
        'api_key' => env('XPOUCH_API_KEY'),
        'api_secret' => env('XPOUCH_API_SECRET'),
        'webhook_secret' => env('XPOUCH_WEBHOOK_SECRET'),
        'initialize_path' => env('XPOUCH_INITIALIZE_PATH', '/payments/initialize'),
        'verify_path' => env('XPOUCH_VERIFY_PATH', '/payments/verify/{reference}'),
        'callback_url' => env('XPOUCH_CALLBACK_URL'),
        'webhook_url' => env('XPOUCH_WEBHOOK_URL'),
    ],

];
