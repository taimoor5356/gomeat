<?php

return [
    'client_id' => env('PAYPAL_CLIENT_ID','AdOADI7wN4Rp3EmALaj9_eNzAf_lnuJLwKQd4hHbEb-joI-C8ohKtvTHxtv8JUUuWqllwqkN2yFMwpBQ'),
    'secret' => env('PAYPAL_SECRET','EK0a8AJTJE2zoRDEOMRIbHEjQKXaXuSsKTEHReCabxiJWuD22mXhU9OebkrskT95vFn-MdyYH6aK08Gm'),
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'settings' => array(
        'mode' => env('PAYPAL_MODE','live'),
        'http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => storage_path() . '/logs/paypal.log',
        'log.LogLevel' => 'ERROR'
    ),
];


// return [
//     'mode'    => env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
//     'sandbox' => [
//         'client_id'         => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
//         'client_secret'     => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
//         'app_id'            => 'APP-80W284485P519543T',
//     ],
//     'live' => [
//         'client_id'         => env('PAYPAL_LIVE_CLIENT_ID', ''),
//         'client_secret'     => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
//         'app_id'            => env('PAYPAL_LIVE_APP_ID', ''),
//     ],

//     'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
//     'currency'       => env('PAYPAL_CURRENCY', 'USD'),
//     'notify_url'     => env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
//     'locale'         => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
//     'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
// ];
