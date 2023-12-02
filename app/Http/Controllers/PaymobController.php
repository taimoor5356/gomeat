<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymobController extends Controller
{
    protected function cURL($url, $json)
    {
        // Create curl resource
        $ch = curl_init($url);

        // Request headers
        $headers = array();
        $headers[] = 'Content-Type: application/json';

        // Return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // $output contains the output string
        $output = curl_exec($ch);

        // Close curl resource to free up system resources
        curl_close($ch);
        return json_decode($output);
    }

    protected function GETcURL($url)
    {
        // Create curl resource
        $ch = curl_init($url);

        // Request headers
        $headers = array();
        $headers[] = 'Content-Type: application/json';

        // Return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // $output contains the output string
        $output = curl_exec($ch);

        // Close curl resource to free up system resources
        curl_close($ch);
        return json_decode($output);
    }

    public function credit($requestData = null)
    {
        $currency_code = Helpers::currency_code();
        $currency_code = 'PKR';
        if ($currency_code != "PKR") {
            Toastr::error('Paymob supports Rs currency only');
            return back();
        }
        $config = Helpers::get_business_settings('paymob_accept');
        try {
            $token = $this->getToken();
            if ($token['status'] == false) {
                return response()->json([
                    'status' => false,
                    'url' => ''
                ]);
            } else {
                $token = $token['data'];
            }
            if (!is_null($requestData)) {
                $order = $this->createOrder($token, $requestData);
                $paymentToken = $this->getPaymentToken($order, $token, $requestData);
                if ($requestData['payment_method'] == 'card_payment') {
                    if (strpos($paymentToken, 'HTTP')) {
                        return response()->json([
                            'status' => false,
                            'url' => ''
                        ]);
                    } else {
                        return response()->json([
                            'status' => true,
                            'url' => 'https://pakistan.paymob.com/api/acceptance/iframes/' . $config['iframe_id'] . '?payment_token=' . $paymentToken
                        ]);
                        // return 'https://pakistan.paymob.com/api/acceptance/iframes/' . $config['iframe_id'] . '?payment_token=' . $paymentToken;
                    }
                } else if ($requestData['payment_method'] == 'jazz_cash' || $requestData['payment_method'] == 'easy_paisa') {
                    if (strpos($paymentToken, 'HTTP')) {
                        return response()->json([
                            'status' => false,
                            'url' => ''
                        ]);
                    } else {
                        return response()->json([
                            'status' => true,
                            'url' => 'https://pakistan.paymob.com/iframe/' . $paymentToken
                        ]);
                        // return 'https://pakistan.paymob.com/iframe/' . $paymentToken;
                    }
                }
            } else {
                $order = $this->createOrder($token);
                $paymentToken = $this->getPaymentToken($order, $token);
                return \Redirect::away('https://pakistan.paymob.com/api/acceptance/iframes/' . $config['iframe_id'] . '?payment_token=' . $paymentToken);
                // return \Redirect::away('https://portal.weaccept.co/api/acceptance/iframes/' . $config['iframe_id'] . '?payment_token=' . $paymentToken);
            }
        }catch (\Exception $exception){
            return response()->json($exception);
            Toastr::error(translate('messages.country_permission_denied_or_misconfiguration'));
            return back();
        }
    }

    public function getToken()
    {
        try {
            $config = Helpers::get_business_settings('paymob_accept');
            $response = $this->cURL(
                // 'https://accept.paymobsolutions.com/api/auth/tokens',
                'https://pakistan.paymob.com/api/auth/tokens',
                ['api_key' => $config['api_key']]
            );
            if (!empty($response->error)) {
                return ['status' => false, 'msg' => 'Too many attempts', 'data' => NULL];
            }
            return ['status' => true, 'msg' => 'Too many attempts', 'data' => $response->token];
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function createOrder($token, $requestData = null)
    {
        try {
            $order = Order::with(['details'])->where(['id' => session('order_id')])->first();
            if (!is_null($requestData)) {
                $order = Order::with(['details'])->where(['id' => $requestData['order_id']])->first();
            }
            $items = [];
            foreach ($order->details as $detail) {
                if (isset($detail)) {
                    $itemDetail = json_decode($detail->item_details);
                    array_push($items, [
                        'name' => !empty($itemDetail->name) ? $itemDetail->name : 'NILL',
                        'amount_cents' => !empty($detail['price']) ? round($detail['price'],2) * 100 : 0.00,
                        'description' => !empty($itemDetail->description) ? $itemDetail->description : 'NILL',
                        'quantity' => !empty($detail['quantity']) ? $detail['quantity'] : 0
                    ]);
                }
            }
            $data = [
                "auth_token" => $token,
                "delivery_needed" => "false",
                "amount_cents" => round($order->order_amount,2) * 100,
                "currency" => "PKR",
                "items" => $items,
    
            ];
            $response = $this->cURL(
                // 'https://accept.paymob.com/api/ecommerce/orders',
                'https://pakistan.paymob.com/api/ecommerce/orders',
                $data
            );
            return $response;
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function getPaymentToken($order, $token, $requestData = null)
    {
        try {
            $ord = Order::with(['details'])->where(['id' => session('order_id')])->first();
            $config = Helpers::get_business_settings('paymob_accept');
            $integrationId = 130240;
            // cash_on_delivery, card_payment, jazz_cash, easy_paisa, stripe, paypal, wallet, crypto, gomt, 
            if (!is_null($requestData)) {
                $ord = Order::with(['details'])->where(['id' => $requestData['order_id']])->first();
                if ($requestData['payment_method'] == 'jazz_cash') {
                    $integrationId = 133977;
                } else if ($requestData['payment_method'] == 'easy_paisa') {
                    $integrationId = 133980;
                }
            }
            $value = $ord->order_amount;
            $billingData = [
                "apartment" => "not given",
                "email" => "not given",
                "floor" => "not given",
                "first_name" => "not given",
                "street" => "not given",
                "building" => "not given",
                "phone_number" => "not given",
                "shipping_method" => "PKG",
                "postal_code" => "not given",
                "city" => "not given",
                "country" => "not given",
                "last_name" => "not given",
                "state" => "not given",
            ];
            $data = [
                "auth_token" => $token,
                "amount_cents" => round($value,2) * 100,
                "expiration" => 3600,
                "order_id" => $order->id,
                "billing_data" => $billingData,
                "currency" => "PKR",
                "integration_id" => $integrationId
            ];
    
            $response = $this->cURL(
                // 'https://accept.paymob.com/api/acceptance/payment_keys',
                'https://pakistan.paymob.com/api/acceptance/payment_keys',
                $data
            );
            return $response->token;
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function callback(Request $request)
    {
        // return response()->json(['testing']);
        $config = Helpers::get_business_settings('paymob_accept');
        $data = $request->all();
        ksort($data);
        $hmac = $data['hmac'];
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if (in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        $secret = $config['hmac'];
        $hased = hash_hmac('sha512', $connectedString, $secret);
        $order = Order::where('id', session('order_id'))->first();

        if ($hased == $hmac) {
            $order->transaction_reference = 'tran-' . session('order_id');
            $order->payment_method = 'paymob_accept';
            $order->order_status = 'confirmed';
            $order->confirmed = now();
            $order->updated_at = now();
            $order->save();
            try {
                Helpers::send_order_notification($order);
            } catch (\Exception $e) {
            }

            if ($order->callback != null) {
                return redirect($order->callback . '&status=success');
            }else{
                return \redirect()->route('payment-success');
            }
        }

        $order->order_status = 'failed';
        $order->failed = now();
        $order->save();
        if ($order->callback != null) {
            return redirect($order->callback . '&status=fail');
        }else{
            return \redirect()->route('payment-fail');
        }
    }
}
