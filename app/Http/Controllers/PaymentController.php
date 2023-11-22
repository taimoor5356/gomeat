<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\RequestException;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        if ($request->has('callback')) {
            Order::where(['id' => $request->order_id])->update(['callback' => $request['callback']]);
        }
        session()->put('customer_id', $request['customer_id']);
        session()->put('order_id', $request->order_id);
        session()->put('payment_method', $request->payment_method);

        $customer = User::find($request['customer_id']);

        $order = Order::where(['id' => $request->order_id, 'user_id' => $request['customer_id']])->first();

        if (isset($customer) && isset($order)) {
            $data = [
                'name' => $customer['f_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
            ];
            session()->put('data', $data);
            return view('payment-view');
        }

        return response()->json(['errors' => ['code' => 'order-payment', 'message' => 'Data not found']], 403);
    }

    public function success()
    {
        $order = Order::where(['id' => session('order_id'), 'user_id'=>session('customer_id')])->first();
        if (isset($order) && $order->callback != null) {
            return redirect($order->callback . '&status=success');
        }
        return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function fail()
    {
        $order = Order::where(['id' => session('order_id'), 'user_id'=>session('customer_id')])->first();
        if ($order->callback != null) {
            return redirect($order->callback . '&status=fail');
        }
        return response()->json(['message' => 'Payment failed'], 403);
    }

    public function coinbase_success($order_id,$customer_id)
    {
        $order = Order::where(['id' => $order_id, 'user_id'=>$customer_id])->first();
        // if (isset($order) && $order->callback != null) {
        //     return redirect($order->callback . '&status=success');
        // }
        // return redirect('http://localhost:59219/order-successful?id='.$order_id);
        return redirect('https://orders.gomeat.io/order-successful?id='.$order_id);
        // return redirect('https://orders.gomeat.io/order-successful?id='.session('order_id'));
        // return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function coinbase_fail($order_id,$customer_id)
    {
        $order = Order::where(['id' => $order_id, 'user_id'=>$customer_id])->first();
        // if ($order->callback != null) {
        //     return redirect($order->callback . '&status=fail');
        // }
        // return redirect('https://orders.gomeat.io/order-fail');
        // return redirect('http://localhost:59219/order-successful?id='.$order_id);
        return redirect('https://orders.gomeat.io/order-successful?id='.$order_id);
        // return redirect('https://orders.gomeat.io/order-successful?id='.session('order_id'));
        // return response()->json(['message' => 'Payment failed'], 403);
    }

    public function getPaymentTokens(Request $request)
    {
        if ($request->payment_method == 'stripe') {
            $stripePayment = app(StripePaymentController::class);
            $stripeURL = $stripePayment->payment_process_3d($request->all());
            return response()->json([
                'url' => $stripeURL
            ]);
        } else if ($request->payment_method == 'paypal') {
            $paypalPayment = app(PaypalPaymentController::class);
            $paypalToken = $paypalPayment->payWithpaypal($request->all());
            return response()->json([
                'url' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$paypalToken,
                'alert' => 'Remember we are using the live paypal credentials'
            ]);
        } else if ($request->payment_method == 'card_payment') {
            $paymobPayment = app(PaymobController::class);
            $paymobURL = $paymobPayment->credit($request->all());
            return response()->json([
                'url' => $paymobURL
            ]);
        }
    }
}
