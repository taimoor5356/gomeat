<?php

namespace App\Http\Controllers;

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
use App\Models\User;
use PHPUnit\Exception;
use Illuminate\Http\Request;
// Redirect
// use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Http;


class CoinbasePaymentController extends Controller
{
    public function payWithCoinbase()
    {

        $customer = User::where('id',session('customer_id'))->first();
        // return $customer->f_name;

        // $order_id = session('order_id');


        if($customer)
        {
            $order = Order::with(['details'])->where(['id' => session('order_id')])->first();

            // return $order;
            
            if($order)
            {
                $products = [];
                foreach ($order->details as $detail) {
                    array_push($products, [
                        'name' => $detail->item?$detail->item['name']:$detail->campaign['name']
                    ]);
                }

                // return $products;

                $tran = Str::random(6) . '-' . rand(1, 1000);
                session()->put('transaction_ref', $tran);
                $response = Http::withHeaders([
                    'X-CC-Api-Key' => '2faca458-a3ef-4ff3-a708-95c795f34a10',
                    'X-CC-Version' => '2018-03-22',
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->post('https://api.commerce.coinbase.com/charges', [
                    // 'body' => '{"local_price":{"amount":100,"currency":"USD"},"metadata":{"customer_id":"1234","customer_name":"huzaifa"},"pricing_type":"fixed_price","name":"Charge name, 100 characters or less","description":"More detailed description, 200 characters or less","redirect_url":"https://gomeat.io","cancel_url":"https://gomeat.io"}',
                        'name' => 'order id: '.$order->id,
                        'description' => 'Please pay via following methods.',
                        'local_price' => [
                            'amount' => $order->order_amount,
                            'currency' => 'USD',
                        ],
                        'pricing_type' => 'fixed_price',
                        'metadata' =>  [
                            'customer_id' =>  $customer->id,
                            'customer_name' =>  $customer->f_name.' '.$customer->l_name
                        ],
                        'redirect_url' =>  env('APP_ENV').'/pay-coinbase/success/'.$order->id.'/'.$tran,
                        'cancel_url' => env('APP_ENV').'/pay-coinbase/fail/'.$order->id,
                ]);
        
                $resp = json_decode($response->getBody(),true);
                $resp = $resp['data'];
                return redirect($resp['hosted_url']);

            }

        }


        // return response()->json(['id' => $checkout_session->id]);
    }

    public function success($order_id,$transaction_ref,$platform,$customer_id)
    {
        // sleep(10);
        $order = Order::find($order_id);
        $order->order_status='confirmed';
        $order->payment_method='coinbase';
        $order->transaction_reference=$transaction_ref;
        $order->payment_status='paid';
        $order->confirmed=now();
        // return $order; 
        $order->save();
        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {

        }

        // sleep(10);
        // if ($order->callback != null) {
            // return redirect('https://dashboard.gomeat.io?order_id='.session('order_id') . '&status=success');
        // }

        // return \redirect()->route('payment-success');



        // if ($order->callback != null) {
        //     return redirect($order->callback . '&status=success');
        // }

        if($platform==='web')
        {
            return redirect('https://orders.gomeat.io/order-successful?id='.$order_id);
            // return \redirect()->route('coinbase-payment-success',['order_id'=>$order_id, 'customer_id'=>$customer_id]);
        }
        
        return redirect()->away('https://gomeat.page.link/N9CY');

    }

    public function fail($order_id,$platform,$customer_id)
    {
        DB::table('orders')
        // ->where('id', session('order_id'))
        ->where('id', $order_id)
        ->update(['order_status' => 'failed',  'payment_status' => 'unpaid', 'failed'=>now()]);
        // $order = Order::find(session('order_id'));
        // if ($order->callback != null) {
        //     return redirect($order->callback . '&status=fail');
        // }
        // return \redirect()->route('payment-fail');
        // return redirect()->to('https://gomeat.page.link/N9CY');

        if($platform==='web')
        {
            // return \redirect()->route('coinbase-payment-fail', ['order_id'=>$order_id, 'customer_id'=>$customer_id]);
            return redirect('https://orders.gomeat.io/order-successful?id='.$order_id);
        }
        return redirect()->away('https://gomeat.page.link/N9CY');
    }
    
}
