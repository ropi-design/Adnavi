<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Ads API Configuration
    |--------------------------------------------------------------------------
    */

    'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
    'login_customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),

    'oauth2' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

    'api_version' => env('GOOGLE_ADS_API_VERSION', 'v16'),

];
