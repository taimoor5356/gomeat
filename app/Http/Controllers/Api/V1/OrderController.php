<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use App\Models\Zone;
use App\Models\Order;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\OrderDetail;
use App\Mail\PlaceOrder;
use App\Models\ItemCampaign;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\CouponLogic;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Stripe\Product;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function track_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $order = Order::with(['store', 'delivery_man.rating', 'parcel_category'])->withCount('details')->where(['id' => $request['order_id'], 'user_id' => $request->user()->id])->Notpos()->first();
        if ($order) {
            $order['store'] = $order['store'] ? Helpers::store_data_formatting($order['store']) : $order['store'];
            $order['delivery_address'] = $order['delivery_address'] ? json_decode($order['delivery_address']) : $order['delivery_address'];
            $order['delivery_man'] = $order['delivery_man'] ? Helpers::deliverymen_data_formatting([$order['delivery_man']]) : $order['delivery_man'];
            unset($order['details']);
        } else {
            return response()->json([
                'errors' => [
                    ['code' => 'schedule_at', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }
        return response()->json($order, 200);
    }

    // to place order before payment is done
    public function place_order(Request $request)
    {
        // return response()->json('place api called',200);
        //*********************** validating request data ********/ 
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required',
            'payment_method' => 'required|in:cash_on_delivery,card_payment,jazz_cash,easy_paisa,stripe,paypal,wallet,crypto,gomt',
            'order_type' => 'required|in:take_away,delivery,parcel',
            'store_id' => 'required_unless:order_type,parcel',
            'distance' => 'required_unless:order_type,take_away',
            'address' => 'required_unless:order_type,take_away',
            'longitude' => 'required_unless:order_type,take_away',
            'latitude' => 'required_unless:order_type,take_away',
            'parcel_category_id' => 'required_if:order_type,parcel',
            'receiver_details' => 'required_if:order_type,parcel',
            'charge_payer' => 'required_if:order_type,parcel|in:sender,receiver',
            'dm_tips' => 'nullable|numeric'
        ]);

        //*********************** validation check ********/ 
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $timeNow = Helpers::setTImeZone($request->store_id);

        $coupon = null;
        $delivery_charge = null;
        $schedule_at = $request->schedule_at ? \Carbon\Carbon::parse($request->schedule_at) : $timeNow;
        $store = null;
        $free_delivery_by = null;

        
        
        
        if($request->order_type !=='take_away')
        {
            // return 'in if';
            $store_fetched = Store::where('id',$request->store_id)->first();

            // return $store_fetched;

            $theta = $request->longitude - $store_fetched->longitude;
            $miles = (sin(deg2rad($request->latitude))) * sin(deg2rad($store_fetched->latitude)) + (cos(deg2rad($request->latitude)) * cos(deg2rad($store_fetched->latitude)) * cos(deg2rad($theta)));
            $miles = acos($miles);
            $miles = rad2deg($miles);
            $radius = $miles * 60 * 1.1515;
            // $radius = $radius * 0.8684;

            // return $radius;
            
            if($radius > $store_fetched->radius)
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'coordinates', 'message' => 'Store is Too Far from your selected Address']
                    ]
                ], 404);
            }



        }



        //*********************** validating store, coupon, schedule, delivery charges(parcel or order) ********/ 
        if ($request->order_type !== 'parcel') {
            if ($request->schedule_at && $schedule_at < $timeNow) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.you_can_not_schedule_a_order_in_past')]
                    ]
                ], 406);
            }
            $store = Store::with('discount')->selectRaw('*, IF(((select count(*) from `store_schedule` where `stores`.`id` = `store_schedule`.`store_id` and `store_schedule`.`day` = ' . $schedule_at->format('w') . ' and `store_schedule`.`opening_time` < "' . $schedule_at->format('H:i:s') . '" and `store_schedule`.`closing_time` >"' . $schedule_at->format('H:i:s') . '") > 0), true, false) as open')->where('id', $request->store_id)->first();

            if (!$store) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.store_not_found')]
                    ]
                ], 404);
            }

            if ($request->schedule_at && !$store->schedule_order) {
                return response()->json([
                    'errors' => [
                        ['code' => 'schedule_at', 'message' => translate('messages.schedule_order_not_available')]
                    ]
                ], 406);
            }

            if ($store->open == false) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.store_is_closed_at_order_time')]
                    ]
                ], 406);
            }

            if ($request['coupon_code']) {
                $coupon = Coupon::active()->where(['code' => $request['coupon_code']])->first();
                if (isset($coupon)) {
                    $staus = CouponLogic::is_valide($coupon, $request->user()->id, $request['store_id']);
                    if ($staus == 407) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.coupon_expire')]
                            ]
                        ], 407);
                    } else if ($staus == 406) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.coupon_usage_limit_over')]
                            ]
                        ], 406);
                    } else if ($staus == 404) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.not_found')]
                            ]
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'errors' => [
                            ['code' => 'coupon', 'message' => translate('messages.not_found')]
                        ]
                    ], 401);
                }
            }
            $per_km_shipping_charge = (float)BusinessSetting::where(['key' => 'per_km_shipping_charge'])->first()->value;
            $minimum_shipping_charge = (float)BusinessSetting::where(['key' => 'minimum_shipping_charge'])->first()->value;
            $original_delivery_charge = ($request->distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge : $minimum_shipping_charge;
            if ($request['order_type'] != 'take_away' && !$store->free_delivery) {
                if ($store->self_delivery_system) {
                    $delivery_charge = !isset($delivery_charge) ? $store->delivery_charge : $delivery_charge;
                    $original_delivery_charge = $store->delivery_charge;
                } else {
                    $delivery_charge = $request->payment_method=='gomt'?0.00:(!isset($delivery_charge) ? $original_delivery_charge : $delivery_charge);
                }
            }
        } else {
            $per_km_shipping_charge = (float)BusinessSetting::where(['key' => 'parcel_per_km_shipping_charge'])->first()->value;
            $minimum_shipping_charge = (float)BusinessSetting::where(['key' => 'parcel_minimum_shipping_charge'])->first()->value;
            $original_delivery_charge = ($request->distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge : $minimum_shipping_charge;
        }
        
        $zone = null;
        if ($request->latitude && $request->longitude) {
            $point = new Point($request->latitude, $request->longitude);
            if(config('module.current_module_data') && config('module.current_module_data')['all_zone_service']) {
                $zone_id = json_decode($request->header('zoneId'), true);
            } else {
                $zone_id = isset($store) ? [$store->zone_id] : json_decode($request->header('zoneId'), true);
            }

            $zone = Zone::whereIn('id', $zone_id)->contains('coordinates', $point)->latest()->first();
            
            if (!$zone) {
                $errors = [];
                array_push($errors, ['code' => 'coordinates', 'message' => translate('messages.out_of_coverage')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
        }

        $address = [
            'contact_person_name' => $request->contact_person_name ? $request->contact_person_name : $request->user()->f_name . ' ' . $request->user()->f_name,
            'contact_person_number' => $request->contact_person_number ? $request->contact_person_number : $request->user()->phone,
            'address_type' => $request->address_type ? $request->address_type : 'Delivery',
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
        ];

        $total_addon_price = 0;
        $product_price = 0;
        $store_discount_amount = 0;
        $product_data = [];

        $order_details = [];
        $order = new Order();
        $order->id = 200000 + Order::count() + 1;
        if (Order::find($order->id)) {
            $order->id = Order::orderBy('id', 'desc')->first()->id + 1;
        }
        $order->user_id = $request->user()->id;
        
        // custom order calculations

        // **** required from app side **** 

        // subtotal without sales tax 
        // return $store_discount_amount;
        
        
        $sub_total = $request['sub_total'] ?? 0.00 ;
        
        // dd($sub_total);

        
        // total sales tax
        $sales_tax = $request['tax_amount'] ?? 0.00 ;
        
        // service fee percentage
        $service_fee_percent = $request['service_fee_percent'] ?? 0.00 ;
        
        // service fee value
        $service_fee_amount = $request['service_fee_amount'] ?? 0.00 ;
        
        // total discount 
        // $discount_amount = $request['discount_amount'] ?? 0.00 ;
        $discount_amount = $request['discount_amount'] ?? 0.00 ;
        

        // delivery charges
        $delivery_charges = $request['payment_method']=='gomt'?0.00:($request['delivery_charges'] ?? 0.00 );
        
        $gm_commission_percent = 
        DB::table('stores')->select('gm_commission')->where('id',$request['store_id'])->get();
        // Store::where('id', $request['store_id'])->first();
        // return response()->json($gm_commission_percent[0]->gm_commission,200);
        // return $gm_commission_percent;

        $gm_commission = $sub_total * ($gm_commission_percent[0]->gm_commission/100);

        $gomeat_revenue = $service_fee_amount + $gm_commission + $delivery_charges - $discount_amount;

        $net_to_store = $sub_total + $sales_tax - $gm_commission;
        

        // gomeat revenue = gm service fee dollars + gm commission dollars + delivery charge - discount
        // net to store = subtotal + sales tax dollars - gm commission dollars
        
        
        // ****** TBC *****

        // gm_commission 
                



        $order->sub_total = $sub_total;
        $order->sales_tax = $sales_tax;
        $order->service_fee_percent = $service_fee_percent;
        $order->service_fee_amount = $service_fee_amount;
        $order->discount_amount = $discount_amount;
        $order->delivery_charges = $delivery_charges;
        $order->gm_commission_percent = $gm_commission_percent[0]->gm_commission;
        $order->gm_commission = $gm_commission;
        $order->gomeat_revenue = $gomeat_revenue;
        $order->net_to_store = $net_to_store;


        // return response()->json($order,200);
        

        $order->order_amount = $request['order_amount']; // total amount
        $order->payment_status = $request['payment_method']=='wallet'?'paid':'unpaid';
        // $order->order_status = $request['payment_method']=='digital_payment'?'Waiting for Payment':($request->payment_method == 'wallet'?'confirmed':'pending');
        
        $order->order_status = $request->payment_method == 'wallet' ? 'confirmed' : ($request->payment_method == 'cash_on_delivery' ? 'pending' : 'Waiting for Payment');

        $order->coupon_code = $request['coupon_code'];
        $order->payment_method = $request->payment_method;
        $order->transaction_reference = null;
        $order->order_note = $request['order_note'];
        $order->order_type = $request['order_type'];
        $order->store_id = $request['store_id'];
        $order->gomt_discount_percent = $request['gomt_discount_percentage']??0.00;
        // $order->delivery_charge = round($delivery_charge, config('round_up_to_digit')) ?? 0;
        $order->delivery_charge = $request['payment_method']=='gomt'?0.00:(round($delivery_charge, config('round_up_to_digit')) ?? 0);
        $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
        $order->delivery_address = json_encode($address);
        $order->schedule_at = $schedule_at;
        $order->scheduled = $request->schedule_at ? 1 : 0;
        $order->otp = rand(1000, 9999);
        $order->zone_id = isset($zone) ? $zone->id : end(json_decode($request->header('zoneId'), true));        
        $order->module_id = $request->header('moduleId');
        $order->parcel_category_id = $request->parcel_category_id;
        $order->receiver_details = json_decode($request->receiver_details);
        if($request['payment_method']=='wallet')
        {
            $order->confirmed = $timeNow;
        }
        $order->pending = $timeNow;
        $order->order_attachment = $request->has('order_attachment') ? Helpers::upload('order/', 'png', $request->file('order_attachment')) : null;
        $order->distance = $request->distance;
        $order->created_at = $timeNow;
        $order->updated_at = $timeNow;
        $order->charge_payer = $request->charge_payer;
        if($request->wallet_amount > 0) {
            $order->wallet_amount = $request->wallet_amount;
        } else {
            $order->wallet_amount = 0.00;
        }

        //Added DM TIPS
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        if ($dm_tips_manage_status == 1) {
            $order->dm_tips = $request->dm_tips ?? 0;
        } else {
            $order->dm_tips = 0;
        }

        if ($request->order_type !== 'parcel') 
        {
            foreach (json_decode($request['cart'], true) as $c) 
            {
                // return $c;
                if ($c['item_campaign_id'] != null) {
                    $product = ItemCampaign::with('module')->active()->find($c['item_campaign_id']);
                    if ($product) {
                        // if (count(json_decode($product['variations'], true)) > 0) {
                        //     $variant_data = Helpers::variation_price($product, json_encode($c['variation']));
                        //     $price = $variant_data['price'];
                        //     $stock = $variant_data['stock'];
                        // } else {
                        //     $price = $product['price'];
                        //     $stock = $product->stock;
                        // }
                        // if (config('module.' . $product->module->module_type)['stock']) {
                        //     if ($c['quantity'] > $stock) {
                        //         return response()->json([
                        //             'errors' => [
                        //                 ['code' => 'campaign', 'message' => translate('messages.product_out_of_stock_warning', ['item' => $product->title])]
                        //             ]
                        //         ], 406);
                        //     }

                        //     $product_data[] = [
                        //         'item' => clone $product,
                        //         'quantity' => $c['quantity'],
                        //         'variant' => count($c['variation']) > 0 ? $c['variation'][0]['type'] : null
                        //     ];
                        // }

                        $product->tax = $store->tax;
                        $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                        // $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);
                        $or_d = [
                            'item_id' => null,
                            'item_campaign_id' => $c['item_campaign_id'],
                            'item_details' => json_encode($product),
                            'quantity' => $c['quantity'],
                            'price' => $c['price'],
                            'tax_amount' => Helpers::tax_calculate($product, $c['price']),
                            'discount_on_item' => Helpers::product_discount_calculate($product, $c['price'], $store),
                            'discount_type' => 'discount_on_product',
                            'variant' => json_encode($c['variant']),
                            'variation' => json_encode($c['variation']),
                            // 'add_ons' => json_encode($addon_data['addons']),
                            'add_ons' => json_encode([]),
                            'total_add_on_price' => 0,//$addon_data['total_add_on_price'],
                            'created_at' => $timeNow,
                            'updated_at' => $timeNow
                        ];
                        $order_details[] = $or_d;
                        // $total_addon_price += $or_d['total_add_on_price'];
                        $product_price += $or_d['price'] * $or_d['quantity'];
                        $store_discount_amount += $or_d['discount_on_item'] * $or_d['quantity'];
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'campaign', 'message' => translate('messages.product_unavailable_warning')]
                            ]
                        ], 404);
                    }
                } else {
                    $product = Item::with('module')->active()->find($c['item_id']);
                    // $temp=json_decode($product['choice_options'], true);
                    // $temp=json_decode($product['choice_options'],true);
                    // return $temp[0];
                    if ($product) {

                        // if (count(json_decode($product['variations'], true)) > 0) {
                        //     $variant_data = Helpers::variation_price($product, json_encode($c['variation']));
                        //     if($variant_data['price']===0)
                        //     {
                        //         $price = $product['price'];
                        //     }
                        //     else
                        //     {

                        //         $price = $variant_data['price']+$product['price'];
                        //     }
                        //     $stock = $variant_data['stock'];

                        //     // return $variant_data;
                        //     // return $price;
                        // } else {
                        //     $price = $product['price'];
                        //     $stock = $product->stock;

                        //     // return 'price: '.$price.' stock: '.$stock;
                        // }

                        // if (config('module.' . $product->module->module_type)['stock']) {
                        //     if ($c['quantity'] > $stock) {
                        //         return response()->json([
                        //             'errors' => [
                        //                 ['code' => 'campaign', 'message' => translate('messages.product_out_of_stock_warning', ['item' => $product->name])]
                        //             ]
                        //         ], 406);
                        //     }

                        //     $product_data[] = [
                        //         'item' => clone $product,
                        //         'quantity' => $c['quantity'],
                        //         'variant' => count($c['variation']) > 0 ? $c['variation'][0]['type'] : null
                        //     ];
                        // }

                        $product->tax = $store->tax;
                        $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                        // $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);
                        $or_d = [
                            'order_id' => 0,
                            'item_id' => $c['item_id'],
                            'item_campaign_id' => null,
                            'item_details' => json_encode($product),
                            'quantity' => $c['quantity'],
                            // 'price' => round($price, config('round_up_to_digit')),
                            'price' => $c['price'],
                            'tax_amount' => round(Helpers::tax_calculate($product, $c['price']), config('round_up_to_digit')),
                            'discount_on_item' => Helpers::product_discount_calculate($product, $c['price'], $store),
                            'discount_type' => 'discount_on_product',
                            'variant' => json_encode($c['variant']),
                            'variation' => json_encode($c['variation']),
                            // 'add_ons' => json_encode($addon_data['addons']),
                            'add_ons' => json_encode([]),
                            // 'total_add_on_price' => round($addon_data['total_add_on_price'], config('round_up_to_digit')),
                            'total_add_on_price' => 0,
                            'created_at' => $timeNow,
                            'updated_at' => $timeNow
                        ];
                        $total_addon_price += $or_d['total_add_on_price'];
                        // $product_price += $request['sub_total'] ;//* $or_d['quantity'];
                        $product_price += $or_d['price'] * $or_d['quantity'];
                        
                        // return $or_d;
                        // return $request['sub_total'];
                        $store_discount_amount += $or_d['discount_on_item'] * $or_d['quantity'];
                        $order_details[] = $or_d;
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'item', 'message' => translate('messages.product_unavailable_warning')]
                            ]
                        ], 404);
                    }
                }
            }
            // return $order_details;
            // return 'product price: '.$product_price;
            $store_discount = Helpers::get_store_discount($store);
            if (isset($store_discount)) {
                if ($product_price + $total_addon_price < $store_discount['min_purchase']) {
                    $store_discount_amount = 0;
                }

                if ($store_discount['max_discount'] != 0 && $store_discount_amount > $store_discount['max_discount']) {
                    $store_discount_amount = $store_discount['max_discount'];
                }
            }
            $coupon_discount_amount = $coupon ? CouponLogic::get_discount($coupon, $product_price + $total_addon_price - $store_discount_amount) : 0;
            $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount;

            $tax = $store->tax;
            $total_tax_amount = ($tax > 0) ? (($total_price * $tax) / 100) : 0;

            if ($store->minimum_order > $product_price + $total_addon_price && $request->order_type !== 'take_away') {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.you_need_to_order_at_least', ['amount' => $store->minimum_order . ' ' . Helpers::currency_code()])]
                    ]
                ], 406);
            }

            $free_delivery_over = BusinessSetting::where('key', 'free_delivery_over')->first()->value;
            if (isset($free_delivery_over)) {
                if ($free_delivery_over <= $product_price + $total_addon_price - $coupon_discount_amount - $store_discount_amount) {
                    $order->delivery_charge = 0;
                    $free_delivery_by = 'admin';
                }
            }

            if($store->free_delivery){
                $order->delivery_charge = 0;
                $free_delivery_by = 'vendor';
            }

            if ($coupon) {
                if ($coupon->coupon_type == 'free_delivery') {
                    if($coupon->min_purchase <= $product_price + $total_addon_price - $store_discount_amount) {
                        $order->delivery_charge = 0;
                        $free_delivery_by = 'admin';                        
                    }
                }
                $coupon->increment('total_uses');
            }

            $order->coupon_discount_amount = round($coupon_discount_amount, config('round_up_to_digit'));
            $order->coupon_discount_title = $coupon ? $coupon->title : '';

            $order->store_discount_amount = round($store_discount_amount, config('round_up_to_digit'));
            $order->total_tax_amount = 0.00;//round($total_tax_amount, config('round_up_to_digit'));
            // $order->order_amount = round($total_price + $total_tax_amount + $order->delivery_charge, config('round_up_to_digit'));
            $order->free_delivery_by = $free_delivery_by;
        } 
        else 
        {
            $point = new Point(json_decode($request->receiver_details, true)['latitude'], json_decode($request->receiver_details, true)['longitude']);
            $zone_id =  json_decode($request->header('zoneId'), true);
            $zone = Zone::whereIn('id', $zone_id)->contains('coordinates', $point)->latest()->first();
            if (!$zone) {
                $errors = [];
                array_push($errors, ['code' => 'receiver_details', 'message' => translate('messages.out_of_coverage')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            
            $order->delivery_charge = round($delivery_charge, config('round_up_to_digit')) ?? 0;
            $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
            $order->order_amount = round($order->delivery_charge, config('round_up_to_digit'));
        }

        //DM TIPS
        // $order->order_amount = $order->order_amount + $order->dm_tips;
        if($request->payment_method == 'wallet' && $request->user()->wallet_balance < $request->wallet_amount)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'order_amount', 'message' => translate('messages.insufficient_balance')]
                ]
            ], 203);
        }

        // return $order;
        try {
            
            DB::beginTransaction();
            $order->save();
            // return $order;
            // return Order::orderBy('id', 'desc')->first()->id;
            $order->id = Order::orderBy('id', 'desc')->first()->id;
            if ($request->order_type !== 'parcel') {
                foreach ($order_details as $key => $item) {
                    // $order_details[$key]['order_id'] = $order->id;
                    $order_details[$key]['order_id'] = $order->id;
                }
                // return $order_details;
                OrderDetail::insert($order_details);
                // if (count($product_data) > 0) {
                //     foreach ($product_data as $item) {
                //         ProductLogic::update_stock($item['item'], $item['quantity'], $item['variant'])->save();
                //     }
                // }
                $store->increment('total_order');
                
            }


            // return $order->id;

            $customer = $request->user();
            $customer->zone_id = $order->zone_id;
            $customer->save();
            if($request->wallet_amount > 0 && ($request->payment_method == 'wallet' || $request->payment_method == 'cash_on_delivery')) {
                CustomerLogic::create_wallet_transaction($order->user_id, $request->wallet_amount, 'order_place', $order->id);
            }
            // if($request->payment_method == 'wallet') CustomerLogic::create_wallet_transaction($order->user_id, $order->order_amount, 'order_place', $order_id);
            DB::commit();
            // Helpers::send_order_notification($order);
            //PlaceOrderMail
            try{
                if($order->order_status == 'pending' && config('mail.status'))
                {
                    Mail::to($request->user()->email)->send(new PlaceOrder($order->id));
                }
            }catch (\Exception $ex) {
                info($ex);
            }
            //PlaceOrderMail end

            $coinbase_url = '';
            if($request->payment_method==='crypto')
            {
                $tran = Str::random(6) . '-' . rand(1, 1000);
                // session()->put('transaction_ref', $tran);
                $customer = $request->user();

                
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
                        // 'redirect_url' =>  env('APP_ENV').'/pay-coinbase/success',//?order_id='.$request->order_id.'&transaction_ref='.$tran,
                        'redirect_url' =>  env('APP_ENV').'/pay-coinbase/success/'.$order->id.'/'.$tran.'/'.$request['platform'].'/'.$customer->id,
                        // 'cancel_url' => env('APP_ENV').'/pay-coinbase/fail',//?order_id='.$request->order_id,
                        'cancel_url' => env('APP_ENV').'/pay-coinbase/fail/'.$order->id.'/'.$request['platform'].'/'.$customer->id,
                ]);

                // return $response->getBody();
        
                $resp = json_decode($response->getBody(),true);
                $resp = $resp['data'];
                $coinbase_url = $resp['hosted_url'];
                // return $resp;
                // return redirect($resp['hosted_url']);
            }
            $gomt_value = 1;
            if($request->payment_method==='gomt')
            {
                $tran = Str::random(6) . '-' . rand(1, 1000);
                // session()->put('transaction_ref', $tran);
                $customer = $request->user();

                
                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->get('https://telegrambot.gomeat.io/api/gomtValue');

                // return $response->getBody();
        
                $resp = json_decode($response->getBody(),true);
                $gomt_value = $resp['GOMT'];
                // $coinbase_url = $resp['hosted_url'];
                // return $resp;
                // return redirect($resp['hosted_url']);
            }

            return response()->json([
                'message' => translate('messages.order_placed_successfully'),
                'order_id' => $order->id,
                'total_ammount' => $order->order_amount,
                'coinbase_url' => $coinbase_url,
                'order_amount_gomt' => $order->order_amount/$gomt_value,
                'gomt_discount_percentage' => $request['gomt_discount_percentage']
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e], 403);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order_time', 'message' => translate('messages.failed_to_place_order')]
            ]
        ], 403);
    }

    public function get_order_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $paginator = Order::
        with(['store', 'delivery_man.rating', 'parcel_category'])
        ->withCount('details')
        ->where(['user_id' => $request->user()->id])
        ->whereIn('order_status', ['delivered', 'canceled', 'refund_requested', 'refunded', 'failed'])
        // ->where('payment_status', 'paid')
        ->Notpos()
        ->latest()
        ->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address'] ? json_decode($data['delivery_address']) : $data['delivery_address'];
            $data['store'] = $data['store'] ? Helpers::store_data_formatting($data['store']) : $data['store'];
            $data['delivery_man'] = $data['delivery_man'] ? Helpers::deliverymen_data_formatting([$data['delivery_man']]) : $data['delivery_man'];
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }


    public function get_running_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $paginator = Order::
        with(['store', 'delivery_man.rating', 'parcel_category'])
        ->where('payment_status', 'paid')
        ->withCount('details')
        ->where(['user_id' => $request->user()->id])
        ->whereNotIn('order_status', ['delivered', 'canceled', 'refund_requested', 'refunded', 'failed'])
        ->Notpos()->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address'] ? json_decode($data['delivery_address']) : $data['delivery_address'];
            $data['store'] = $data['store'] ? Helpers::store_data_formatting($data['store']) : $data['store'];
            $data['delivery_man'] = $data['delivery_man'] ? Helpers::deliverymen_data_formatting([$data['delivery_man']]) : $data['delivery_man'];
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::with('details', 'parcel_category')->where('user_id', $request->user()->id)->find($request->order_id);

        $details = $order->details;
        if ($details->count() > 0) {
            // $details = $details = Helpers::order_details_data_formatting($details);
            $details = Helpers::order_details_data_formatting($details);
            return response()->json($details, 200);
        } else if ($order->order_type == 'parcel') {
            $order->delivery_address = json_decode($order->delivery_address, true);
            return response()->json(($order), 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')]
            ]
        ], 404);
    }

    public function cancel_order(Request $request)
    {
        $order = Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->Notpos()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 401);
        } else if ($order->order_status == 'pending') {
            $timeNow = Helpers::setTImeZone($order->store_id);
            if (config('module.' . $order->module->module_type)['stock']) {
                foreach($order->details as $detail) {
                    $variant = json_decode($detail['variation'], true);
                    $item = $detail->item;
                    if($detail->campaign){
                        $item = $detail->campaign;
                    }
                    ProductLogic::update_stock($item, -$detail->quantity, count($variant) ? $variant[0]['type'] : null)->save();
                }
            }
            $order->order_status = 'canceled';
            $order->canceled = $timeNow;
            $order->save();
            Helpers::send_order_notification($order);
            return response()->json(['message' => translate('messages.order_canceled_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.you_can_not_cancel_after_confirm')]
            ]
        ], 401);
    }

    public function refund_request(Request $request)
    {
        $order = Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->Notpos()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 401);
        } else if ($order->order_status == 'delivered') {
            $timeNow = Helpers::setTImeZone($order->store_id);
            $order->order_status = 'refund_requested';
            $order->refund_requested = $timeNow;
            $order->save();
            return response()->json(['message' => translate('messages.refund_request_placed_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.you_can_not_request_for_refund_after_delivery')]
            ]
        ], 401);
    }

    public function update_payment_method(Request $request)
    {
        $config = Helpers::get_business_settings('cash_on_delivery');
        if ($config['status'] == 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'cod', 'message' => translate('messages.Cash on delivery order not available at this time')]
                ]
            ], 403);
        }
        $order = Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->Notpos()->first();
        if ($order) {
            $timeNow = Helpers::setTImeZone($order->store_id);
            Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->update([
                'payment_method' => 'cash_on_delivery', 'order_status' => 'pending', 'pending' => $timeNow
            ]);

            $fcm_token = $request->user()->cm_firebase_token;
            $value = Helpers::order_status_update_message('pending');
            try {
                if ($value) {
                    $data = [
                        'title' => translate('messages.order_placed_successfully'),
                        'description' => $value,
                        'order_id' => $order->id,
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    DB::table('user_notifications')->insert([
                        'data' => json_encode($data),
                        'user_id' => $request->user()->id,
                        'created_at' => $timeNow,
                        'updated_at' => $timeNow
                    ]);
                }
                if ($order->order_type == 'delivery' && !$order->scheduled) {
                    $data = [
                        'title' => translate('messages.order_placed_successfully'),
                        'description' => translate('messages.new_order_push_description'),
                        'order_id' => $order->id,
                        'image' => '',
                    ];
                    Helpers::send_push_notif_to_topic($data, $order->store->zone->deliveryman_wise_topic, 'order_request');
                }
            } catch (\Exception $e) {
                info($e);
            }
            return response()->json(['message' => translate('messages.payment') . ' ' . translate('messages.method') . ' ' . translate('messages.updated_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')]
            ]
        ], 404);
    }
}
