<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Razorpay API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Razorpay API credentials.
    |
    */

    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for Razorpay transactions.
    |
    */
    
    'currency' => env('RAZORPAY_CURRENCY', 'INR'),
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret key for verifying Razorpay webhooks.
    |
    */
    
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    
];
