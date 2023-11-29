<?php

use App\Http\Controllers\JazzcashController;
use App\Models\Country;
use App\Models\CountryHasState;
use Illuminate\Support\Facades\Route;
use OpenSpout\Common\Entity\Row;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('mysuccesspage', function () {
    return 'Successfull';
});

Route::get('myerrorpage', function () {
    return 'Error';
});



Route::get('/insert-data', function (){
    $countries = [
        [
            'name' => 'Afghanistan',
            'short_name' => 'AF',
            'currency_name' => 'Afghan Afghani',
            'currency_symbol' => '؋',
        ],
        [
            'name' => 'Albania',
            'short_name' => 'AL',
            'currency_name' => 'Albanian lek',
            'currency_symbol' => 'L',
        ],
        [
            'name' => 'Algeria',
            'short_name' => 'DZ',
            'currency_name' => 'Algerian dinar',
            'currency_symbol' => 'د.ج',
        ],
        [
            'name' => 'Argentina',
            'short_name' => 'AR',
            'currency_name' => 'Argentine peso',
            'currency_symbol' => '$',
        ],
        [
            'name' => 'Australia',
            'short_name' => 'AU',
            'currency_name' => 'Australian dollar',
            'currency_symbol' => '$',
        ],
        [
            'name' => 'Brazil',
            'short_name' => 'BR',
            'currency_name' => 'Brazilian real',
            'currency_symbol' => 'R$',
        ],
        [
            'name' => 'Canada',
            'short_name' => 'CA',
            'currency_name' => 'Canadian dollar',
            'currency_symbol' => '$',
        ],
        [
            'name' => 'China',
            'short_name' => 'CN',
            'currency_name' => 'Chinese yuan',
            'currency_symbol' => '¥',
        ],
        [
            'name' => 'Egypt',
            'short_name' => 'EG',
            'currency_name' => 'Egyptian pound',
            'currency_symbol' => '£',
        ],
        [
            'name' => 'England',
            'short_name' => 'GB',
            'currency_name' => 'British pound',
            'currency_symbol' => '£',
        ],
        [
            'name' => 'France',
            'short_name' => 'FR',
            'currency_name' => 'Euro',
            'currency_symbol' => '€',
        ],
        [
            'name' => 'Germany',
            'short_name' => 'DE',
            'currency_name' => 'Euro',
            'currency_symbol' => '€',
        ],
        [
            'name' => 'India',
            'short_name' => 'IN',
            'currency_name' => 'Indian Rupee',
            'currency_symbol' => '₹',
        ],
        [
            'name' => 'Indonesia',
            'short_name' => 'ID',
            'currency_name' => 'Indonesian Rupiah',
            'currency_symbol' => 'Rp',
        ],
        [
            'name' => 'Italy',
            'short_name' => 'IT',
            'currency_name' => 'Euro',
            'currency_symbol' => '€',
        ],
        [
            'name' => 'Japan',
            'short_name' => 'JP',
            'currency_name' => 'Japanese yen',
            'currency_symbol' => '¥',
        ],
        [
            'name' => 'Mexico',
            'short_name' => 'MX',
            'currency_name' => 'Mexican peso',
            'currency_symbol' => '$',
        ],
        [
            'name' => 'Netherlands',
            'short_name' => 'NL',
            'currency_name' => 'Euro',
            'currency_symbol' => '€',
        ],
        [
            'name' => 'Pakistan',
            'short_name' => 'PK',
            'currency_name' => 'Pakistani Rupee',
            'currency_symbol' => '₨',
        ],
        [
            'name' => 'Russia',
            'short_name' => 'RU',
            'currency_name' => 'Russian ruble',
            'currency_symbol' => '₽',
        ],
        [
            'name' => 'Saudi Arabia',
            'short_name' => 'SA',
            'currency_name' => 'Saudi Riyal',
            'currency_symbol' => '﷼',
        ],
        [
            'name' => 'South Africa',
            'short_name' => 'ZA',
            'currency_name' => 'South African Rand',
            'currency_symbol' => 'R',
        ],
        [
            'name' => 'Spain',
            'short_name' => 'ES',
            'currency_name' => 'Euro',
            'currency_symbol' => '€',
        ],
        [
            'name' => 'United Kingdom',
            'short_name' => 'GB',
            'currency_name' => 'British pound',
            'currency_symbol' => '£',
        ],
        [
            'name' => 'United States',
            'short_name' => 'US',
            'currency_name' => 'United States Dollar',
            'currency_symbol' => '$',
        ],
    ];
    foreach ($countries as $key => $country) { 
        Country::create([
            'name' => $country['name'],
            'short_name' => $country['short_name'],
            'currency_name' => $country['currency_name'],
            'currency_symbol' => $country['currency_symbol'],
            'gst' => '0'
        ]);
    }
    return 'done';
});
Route::get('insert-usa-states', function () {
    $states = [
        'AL' => 'Alabama',
        // 'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        'DC' => 'District of Columbia',
        'AS' => 'American Samoa',
        'GU' => 'Guam',
        'MP' => 'Northern Mariana Islands',
        'PR' => 'Puerto Rico',
        'UM' => 'United States Minor Outlying Islands',
        'VI' => 'Virgin Islands, U.S.',
    ];
    foreach ($states as $key => $state) {
        CountryHasState::create([
            'country_id' => 25,
            'name' => $state,
            'store_online_payment' => 0.00,
            'store_cash_payment' => 0.00,
            'restaurant_online_payment' => 0.00,
            'restaurant_cash_payment' => 0.00,
        ]);
    }
    return 'Done';
});
Route::get('/', 'HomeController@index')->name('home');
Route::get('custom-token-add', 'HomeController@add_token')->name('custom-token-add');
Route::get('gomt-transaction-status/{txn}', 'HomeController@gomt_transaction_status')->name('gomt-transaction-status');
// Route::get('get-base-url', 'HomeController@baseUrl')->name('get-base-url');
Route::get('terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
Route::get('about-us', 'HomeController@about_us')->name('about-us');
Route::get('contact-us', 'HomeController@contact_us')->name('contact-us');
Route::get('privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::post('newsletter/subscribe', 'NewsletterController@newsLetterSubscribe')->name('newsletter.subscribe');

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', 'PaymentController@payment')->name('payment-mobile');
    Route::get('set-payment-method/{name}', 'PaymentController@set_payment_method')->name('set-payment-method');
});

// SSLCOMMERZ Start
/*Route::get('/example1', 'SslCommerzPaymentController@exampleEasyCheckout');
Route::get('/example2', 'SslCommerzPaymentController@exampleHostedCheckout');*/
Route::post('pay-ssl', 'SslCommerzPaymentController@index');
Route::post('/success', 'SslCommerzPaymentController@success');
Route::post('/fail', 'SslCommerzPaymentController@fail');
Route::post('/cancel', 'SslCommerzPaymentController@cancel');
Route::post('/ipn', 'SslCommerzPaymentController@ipn');
//SSLCOMMERZ END

/*paypal*/
/*Route::get('/paypal', function (){return view('paypal-test');})->name('paypal');*/
Route::post('pay-paypal', 'PaypalPaymentController@payWithpaypal')->name('pay-paypal');
Route::get('paypal-status', 'PaypalPaymentController@getPaymentStatus')->name('paypal-status');
/*paypal*/


/*coinbase*/
Route::get('pay-coinbase', 'CoinbasePaymentController@payWithCoinbase')->name('pay-coinbase');
// Route::get('pay-coinbase/{customer_id}/{order_id}', 'CoinbasePaymentController@payWithCoinbase')->name('pay-coinbase');
Route::get('pay-coinbase/success/{order_id}/{transaction_ref}/{platform}/{customer_id}', 'CoinbasePaymentController@success')->name('pay-coinbase.success');
Route::get('pay-coinbase/fail/{order_id}/{platform}/{customer_id}', 'CoinbasePaymentController@fail')->name('pay-coinbase.fail');

/*Route::get('stripe', function (){
return view('stripe-test');
});*/

Route::get('pay-stripe', 'StripePaymentController@payment_process_3d')->name('pay-stripe');
Route::get('pay-stripe/success', 'StripePaymentController@success')->name('pay-stripe.success');
Route::get('pay-stripe/fail', 'StripePaymentController@fail')->name('pay-stripe.fail');

// Get Route For Show Payment Form
Route::get('paywithrazorpay', 'RazorPayController@payWithRazorpay')->name('paywithrazorpay');
Route::post('payment-razor/{order_id}', 'RazorPayController@payment')->name('payment-razor');

/*Route::fallback(function () {
return redirect('/admin/auth/login');
});*/

Route::get('payment-success', 'PaymentController@success')->name('payment-success');
Route::get('payment-fail', 'PaymentController@fail')->name('payment-fail');

///////////////////////////////////////////////////////////////////////////////////////////////////
// JazzCash payment
///////////////////////////////////////////////////////////////////////////////////////////////////

Route::get('/jazzcash-checkout/{product_id}', [JazzcashController::class, 'show'])->name('jazzcash.show');
Route::post('/checkout-jazzcash', [JazzcashController::class, 'checkOut'])->name('jazzcash.checkout');

///////////////////////////////////////////////////////////////////////////////////////////////////
// JazzCash payment
///////////////////////////////////////////////////////////////////////////////////////////////////

Route::get('coinbase-payment-success/{order_id}/{customer_id}', 'PaymentController@coinbase_success')->name('coinbase-payment-success');
Route::get('coinbase-payment-fail/{order_id}/{customer_id}', 'PaymentController@coinbase_fail')->name('coinbase-payment-fail');

//senang pay
Route::match(['get', 'post'], '/return-senang-pay', 'SenangPayController@return_senang_pay')->name('return-senang-pay');

// paymob
Route::post('/paymob-credit', 'PaymobController@credit')->name('paymob-credit');
Route::get('/paymob-callback', 'PaymobController@callback')->name('paymob-callback');

//paystack
Route::post('/paystack-pay', 'PaystackController@redirectToGateway')->name('paystack-pay');
Route::get('/paystack-callback', 'PaystackController@handleGatewayCallback')->name('paystack-callback');
Route::get('/paystack', function () {
    return view('paystack');
});


// The route that the button calls to initialize payment
Route::post('/flutterwave-pay', 'FlutterwaveController@initialize')->name('flutterwave_pay');
// The callback url after a payment
Route::get('/rave/callback', 'FlutterwaveController@callback')->name('flutterwave_callback');


// The callback url after a payment
Route::get('mercadopago/home', 'MercadoPagoController@index')->name('mercadopago.index');
Route::post('mercadopago/make-payment', 'MercadoPagoController@make_payment')->name('mercadopago.make_payment');
Route::get('mercadopago/get-user', 'MercadoPagoController@get_test_user')->name('mercadopago.get-user');

//paytabs
Route::any('/paytabs-payment', 'PaytabsController@payment')->name('paytabs-payment');
Route::any('/paytabs-response', 'PaytabsController@callback_response')->name('paytabs-response');

//bkash
Route::group(['prefix' => 'bkash'], function () {
    // Payment Routes for bKash
    Route::post('get-token', 'BkashPaymentController@getToken')->name('bkash-get-token');
    Route::post('create-payment', 'BkashPaymentController@createPayment')->name('bkash-create-payment');
    Route::post('execute-payment', 'BkashPaymentController@executePayment')->name('bkash-execute-payment');
    Route::get('query-payment', 'BkashPaymentController@queryPayment')->name('bkash-query-payment');
    Route::post('success', 'BkashPaymentController@bkashSuccess')->name('bkash-success');

    // Refund Routes for bKash
    // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
    // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
});

// The callback url after a payment PAYTM
Route::get('paytm-payment', 'PaytmController@payment')->name('paytm-payment');
Route::any('paytm-response', 'PaytmController@callback')->name('paytm-response');

// The callback url after a payment LIQPAY
Route::get('liqpay-payment', 'LiqPayController@payment')->name('liqpay-payment');
Route::any('liqpay-callback', 'LiqPayController@callback')->name('liqpay-callback');


Route::get('/test', function () {
    dd('Hello tester');
});

Route::get('module-test', function () {
});

//Restaurant Registration
Route::group(['prefix' => 'store', 'as' => 'restaurant.'], function () {
    Route::get('apply', 'VendorController@create')->name('create');
    Route::post('apply', 'VendorController@store')->name('store');
});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', 'DeliveryManController@create')->name('create');
    Route::post('apply', 'DeliveryManController@store')->name('store');
});
