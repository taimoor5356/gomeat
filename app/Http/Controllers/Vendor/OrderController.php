<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Models\Order;
use App\Models\Vendor;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class OrderController extends Controller
{
    public function list($status)
    {
        Order::where(['checked' => 0])->where('store_id',Helpers::get_store_id())->update(['checked' => 1]);
        
        $orders = Order::with(['customer'])
        ->when($status == 'searching_for_deliverymen', function($query){
            return $query->SearchingForDeliveryman();
        })
        ->when($status == 'confirmed', function($query){
            return $query->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed');
        })
        ->when($status == 'pending', function($query){
            if(config('order_confirmation_model') == 'store' || Helpers::get_store_data()->self_delivery_system)
            {
                return $query->where('order_status','pending');
            }
            else
            {
                return $query->where('order_status','pending')->where('order_type', 'take_away');
            }
        })
        ->when($status == 'cooking', function($query){
            return $query->where('order_status','processing');
        })
        ->when($status == 'item_on_the_way', function($query){
            return $query->where('order_status','picked_up');
        })
        ->when($status == 'delivered', function($query){
            return $query->Delivered();
        })
        ->when($status == 'ready_for_delivery', function($query){
            return $query->where('order_status','handover');
        })
        ->when($status == 'refund_requested', function($query){
            return $query->RefundRequest();
        })
        ->when($status == 'refunded', function($query){
            return $query->Refunded();
        })
        ->when($status == 'scheduled', function($query){
            return $query->Scheduled()->where(function($q){
                if(config('order_confirmation_model') == 'store' || Helpers::get_store_data()->self_delivery_system)
                {
                    $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
                }
                else
                {
                    $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                        $query->where('order_status','pending')->where('order_type', 'take_away');
                    });
                }

            });
        })
        ->when($status == 'all', function($query){
            return $query->where(function($query){
                $query->whereNotIn('order_status',(config('order_confirmation_model') == 'store'|| Helpers::get_store_data()->self_delivery_system)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
                ->orWhere(function($query){
                    return $query->where('order_status','pending')->where('order_type', 'take_away');
                });
            });
        })
        ->when(in_array($status, ['pending','confirmed']), function($query){
            return $query->OrderScheduledIn(30);
        })
        ->StoreOrder()
        ->where('store_id',\App\CentralLogics\Helpers::get_store_id())
        ->orderBy('id', 'desc')
        ->paginate(config('default_pagination'));

        $status = translate('messages.'.$status);
        dd($orders);
        return view('store_owner_views.orders.index', compact('orders', 'status'));
        return view('vendor-views.order.list', compact('orders', 'status'));
    }

    public function getVendorOrders(Request $request)
    {
        // if ($request->ajax()) {
            $vendor = auth('vendor');
            if (isset($vendor)) {
                $orders = Order::whereHas('store.vendor', function($query) use($vendor){
                    $query->where('id', 2936);
                })
                ->with('customer')
                ->Notpos()
                ->orderBy('schedule_at', 'desc')
                ->get();
                $orders= Helpers::order_data_formatting($orders, true);
                dd($orders);
            }
        // }
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $orders=Order::where(['store_id'=>Helpers::get_store_id()])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
            }
        })->StoreOrder()->limit(100)->get();
        return response()->json([
            'view'=>view('vendor-views.order.partials._table',compact('orders'))->render()
        ]);
    }

    public function details(Request $request,$id)
    {
        $order = Order::with(['details', 'customer'=>function($query){
            return $query->withCount('orders');
        },'delivery_man'=>function($query){
            return $query->withCount('orders');
        }])->where(['id' => $id, 'store_id' => Helpers::get_store_id()])->first();
        if (isset($order)) {
            return view('vendor-views.order.order-view', compact('order'));
        } else {
            Toastr::info('No more orders!');
            return back();
        }
    }
 
    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'order_status' => 'required|in:confirmed,processing,handover,delivered,canceled'
        ],[
            'id.required' => 'Order id is required!'
        ]);

        $order = Order::where(['id' => $request->id, 'store_id' => Helpers::get_store_id()])->first();

        if($order->delivered != null)
        {
            Toastr::warning(translate('messages.cannot_change_status_after_delivered'));
            return back();
        }

        if($request['order_status']=='canceled' && !config('canceled_by_store'))
        {
            Toastr::warning(translate('messages.you_can_not_cancel_a_order'));
            return back();
        }

        if($request['order_status']=='canceled' && $order->confirmed)
        {
            Toastr::warning(translate('messages.you_can_not_cancel_after_confirm'));
            return back();
        }



        if($request['order_status']=='delivered' && $order->order_type != 'take_away' && !Helpers::get_store_data()->self_delivery_system)
        {
            Toastr::warning(translate('messages.you_can_not_delivered_delivery_order'));
            return back();
        }

        if($request['order_status'] =="confirmed")
        {
            if(!Helpers::get_store_data()->self_delivery_system && config('order_confirmation_model') == 'deliveryman' && $order->order_type != 'take_away')
            {
                Toastr::warning(translate('messages.order_confirmation_warning'));
                return back();
            }
        }

        if ($request->order_status == 'delivered') {
            $order_delivery_verification = (boolean)\App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value;
            if($order_delivery_verification)
            {
                if($request->otp)
                {
                    if($request->otp != $order->otp)
                    {
                        Toastr::warning(translate('messages.order_varification_code_not_matched'));
                        return back();
                    }
                }
                else
                {
                    Toastr::warning(translate('messages.order_varification_code_is_required'));
                    return back();
                }
            }

            if($order->transaction  == null)
            {
                if($order->payment_method == 'cash_on_delivery')
                {
                    $ol = OrderLogic::create_transaction($order,'store', null);
                }
                else{
                    $ol = OrderLogic::create_transaction($order,'admin', null);
                }
                

                if(!$ol)
                {
                    Toastr::warning(translate('messages.faield_to_create_order_transaction'));
                    return back();
                }
            }

            $order->payment_status = 'paid';

            $order->details->each(function($item, $key){
                if($item->item)
                {
                    $item->item->increment('order_count');
                }
            });
            $order->customer->increment('order_count');
        } 
        if($request->order_status == 'canceled' || $request->order_status == 'delivered')
        {
            if($order->delivery_man)
            {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders>1?$dm->current_orders-1:0;
                $dm->save();
            }                 
        }  

        if($request->order_status == 'delivered')
        {
            $order->store->increment('order_count');
            if($order->delivery_man)
            {
                $order->delivery_man->increment('order_count');
            }
            
        }

        $order->order_status = $request->order_status;
        $order[$request['order_status']] = now();
        $order->save();
        if(!Helpers::send_order_notification($order))
        {
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        Toastr::success(translate('messages.order').' '.translate('messages.status_updated'));
        return back();
    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('customer_addresses')->where('id', $id)->update($address);
        Toastr::success('Delivery address updated!');
        return back();
    }

    public function generate_invoice($id)
    {
        $order = Order::where(['id' => $id, 'store_id' => Helpers::get_store_id()])->first();
        return view('vendor-views.order.invoice', compact('order'));
    }

    public function add_payment_ref_code(Request $request, $id)
    {
        Order::where(['id' => $id, 'store_id' => Helpers::get_store_id()])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success('Payment reference code is added!');
        return back();
    }
    public function export_orders(Request $request,$file_type)
    {
        // dd($file_type);
        $data = [];
        $orders = Order::where('store_id',Helpers::get_store_id())->get();
        foreach($orders as $order)
        {
            // $store = \App\Models\Store::where('id',$order['store_id'])->first();
            $user = \App\Models\User::where('id',$order['user_id'])->first();
            $zone = \App\Models\Zone::where('id',$order['zone_id'])->first();
            $module = \App\Models\Module::where('id',$order['module_id'])->first();
            // dd($zone);


            $adr = json_decode($order['delivery_address']);

            $temp=[
                'Order ID'=>$order['id'],
                'Order Status'=>$order['order_status'],
                'Order Time'=>$order['created_at'],
                // 'Store ID'=>$store->id,
                // 'Store Name'=>$store->name,
                'Sub Total'=>$order['sub_total'],
                // 'Service fee %'=>$order['service_fee_percent'].'%',
                // 'Store Commission %'=>$order['gm_commission_percent'].'%',
                // 'Promo Discount'=>$order['coupon_discount_amount'],
                // 'Sales tax'=>$order['sales_tax'],
                // 'Service fee'=>$order['service_fee_amount'],
                'GoMeat Commission'=>$order['gm_commission'],
                // 'Delivery Charge'=>$order['delivery_charges'],
                // 'Tip'=>$order['dm_tips'],
                'Net to Store'=>$order['net_to_store'],
                // 'Order total/Cash'=>$order['order_amount'],
                // 'GoMeat Revenue'=>$order['gomeat_revenue'],
                // 'Promo Code'=>$order['coupon_code'],
                // 'Payment Method'=>$order['payment_method'],
                'Delivery Mode'=>$order['order_type'],
                'Transaction Status'=>$order['payment_status'],
                // 'Transaction ID'=>$order['transaction_reference'],
                'Customer ID'=>isset($user->id)?$user->id:'user was deleted',
                'Customer Name'=>isset($user->id)?$user->f_name.' '.$user->l_name:'user was deleted',
                'Customer Email'=>isset($user->id)?$user->email:'user was deleted',
                'Customer Phone'=>isset($user->id)?$user->phone:'user was deleted',
                // 'Delivery Address'=>$adr->address,
                // 'Pickup Address'=>$store->address,
                'Description'=>$order['order_note'],
                // 'Distance (in miles)'=>$order['distance'],
                'Pickup Time'=>$order['picked_up'],
                'Delivery Time'=>$order['delivered'],
                // 'Zones ID'=>$zone->name,
                // 'Module'=>$module->module_name
            ];

            $data[]=$temp;
        }

        if ($file_type == 'excel') {
            // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.xlsx');
            return (new FastExcel($data))->download('Orders.xlsx');
        } else if ($file_type == 'csv') {
            // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.csv');
            return (new FastExcel($data))->download('Orders.csv');
        }
        // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.xlsx');
        return (new FastExcel($data))->download('Orders.xlsx');
    }
    
}
