<?php

namespace App\Http\Controllers;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Stripe\Charge;
use Stripe\Stripe;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use PHPUnit\Exception;


class StripePaymentController extends Controller
{

    public function getStripeToken(Request $request)
    {
        $stripeURL = $this->payment_process_3d($request->all());
        return $stripeURL;
    }

    public function payment_process_3d($request = null)
    {
        $tran = Str::random(6) . '-' . rand(1, 1000);
        $order_id = session('order_id');
        if (is_null($order_id)) {
            $order_id = $request['order_id'];
        }
        session()->put('transaction_ref', $tran);
        $order = Order::with(['details'])->where(['id' => $order_id])->first();
        $config = Helpers::get_business_settings('stripe');
        Stripe::setApiKey($config['api_key']);
        header('Content-Type: application/json');

        // $YOUR_DOMAIN = env(APP_URL); //url('/');
        $YOUR_DOMAIN = env('APP_URL'); //url('/');

        $products = [];
        foreach ($order->details as $detail) {
            array_push($products, [
                'name' => $detail->item?$detail->item['name']:$detail->campaign['name'],
                'image' => 'def.png'
            ]);
        }

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => Helpers::currency_code(),
                    'unit_amount' => ($order->order_amount) * 100,
                    'product_data' => [
                        'name' => BusinessSetting::where(['key' => 'business_name'])->first()->value,
                        'images' => [asset('storage/app/public/business') . '/' . BusinessSetting::where(['key' => 'logo'])->first()->value],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . 'pay-stripe/success?success=true&order_id='.$order->id.'&transaction_reference='.$tran,
            'cancel_url' => url()->previous(),
        ]);

        if (!empty($request['payment_method'])) {
            if ($request['payment_method'] == 'stripe') {
                return response()->json([
                    'status' => true,
                    'url' => $checkout_session->url
                ]);
                return $checkout_session->url;
            }
        }
        // sleep(1);

        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request)
    {
        // sleep(10);
        $order = Order::find(session('order_id'));
        if (!isset($order)) {
            $order = Order::find($request['order_id']);
        }
        if ($order->wallet_amount > 0) {
            CustomerLogic::create_wallet_transaction($order->user_id, $order->wallet_amount, 'order_place', $order->id);
        }
        $order->order_status = 'confirmed';
        $order->payment_method = 'stripe';
        $transactionReference = session('transaction_ref');
        if (empty($transactionReference)) {
            $transactionReference = $request['transaction_reference'];
        }
        $order->transaction_reference = $transactionReference;
        $order->payment_status = 'paid';
        $order->confirmed = now();
        // return $order; 
        $order->save();
        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {

        }

        // sleep(10);
        // if ($order->callback != null) {
            // return redirect(env(APP_URL).'?order_id='.session('order_id') . '&status=success');
        // }

        // return \redirect()->route('payment-success');



        if ($order->callback != null) {
            return redirect($order->callback . '&status=success');
        }

        return \redirect()->route('payment-success');
    }

    public function fail()
    {
        DB::table('orders')
        ->where('id', session('order_id'))
        ->update(['order_status' => 'failed',  'payment_status' => 'unpaid', 'failed'=>now()]);
        $order = Order::find(session('order_id'));
        if ($order->callback != null) {
            return redirect($order->callback . '&status=fail');
        }
        return \redirect()->route('payment-fail');
    }
}
