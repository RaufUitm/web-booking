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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // Removed toyyibPay configuration

        // Billplz configuration
        'billplz' => [
            'enabled' => env('BILLPLZ_API_KEY') ? true : false,
            'api_key' => env('BILLPLZ_API_KEY'),
            'x_signature' => env('BILLPLZ_X_SIGNATURE'),
            'collection_id' => env('BILLPLZ_COLLECTION_ID'),
            'sandbox' => env('BILLPLZ_SANDBOX', true),
            // Billplz v3 REST API base URL (required for /bills)
            'api_url' => env('BILLPLZ_SANDBOX', true)
                ? 'https://www.billplz-sandbox.com/api/v3'
                : 'https://www.billplz.com/api/v3',
        ],

        // ToyyibPay configuration
        'toyyibpay' => [
            'enabled' => env('TOYYIBPAY_SECRET_KEY') ? true : false,
            'secret_key' => env('TOYYIBPAY_SECRET_KEY'),
            'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
            'sandbox' => env('TOYYIBPAY_SANDBOX', true),
            'base_url' => env('TOYYIBPAY_BASE_URL') ?: (env('TOYYIBPAY_SANDBOX', true)
                ? 'https://dev.toyyibpay.com'
                : 'https://toyyibpay.com'),
            'callback_url' => env('TOYYIBPAY_CALLBACK_URL'),
            'return_url' => env('TOYYIBPAY_RETURN_URL'),
        ],

];
