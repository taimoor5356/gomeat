<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\Helpers;
use App\Models\Newsletter;
use App\Models\BusinessSetting;
// use Rap2hpoutre\FastExcel\FastExcel;

class CustomerController extends Controller
{

    public function export_customers($file_type, Request $request)
    {
        dd('working');
        // if (session()->has('zone_filter') == false) {
        //     session()->put('zone_filter', 0);
        // }

        // $module_id = $request->query('module_id', null);

        // dd($module_id);

        // if (session()->has('order_filter')) {
        //     $request = json_decode(session('order_filter'));
        // }

        // Order::where(['checked' => 0])->update(['checked' => 1]);

        // $orders = Order::with(['customer', 'store'])
        //     ->when(isset($module_id), function ($query) use ($module_id) {
        //         return $query->module($module_id);
        //     })
        //     ->when(isset($request->zone), function ($query) use ($request) {
        //         return $query->whereHas('store', function ($q) use ($request) {
        //             return $q->whereIn('zone_id', $request->zone);
        //         });
        //     })
        //     ->when($status == 'scheduled', function ($query) {
        //         return $query->whereRaw('created_at <> schedule_at');
        //     })
        //     ->when($status == 'searching_for_deliverymen', function ($query) {
        //         return $query->SearchingForDeliveryman();
        //     })
        //     ->when($status == 'pending', function ($query) {
        //         return $query->Pending();
        //     })
        //     ->when($status == 'accepted', function ($query) {
        //         return $query->AccepteByDeliveryman();
        //     })
        //     ->when($status == 'processing', function ($query) {
        //         return $query->Preparing();
        //     })
        //     ->when($status == 'food_on_the_way', function ($query) {
        //         return $query->ItemOnTheWay();
        //     })
        //     ->when($status == 'delivered', function ($query) {
        //         return $query->Delivered();
        //     })
        //     ->when($status == 'canceled', function ($query) {
        //         return $query->Canceled();
        //     })
        //     ->when($status == 'failed', function ($query) {
        //         return $query->failed();
        //     })
        //     ->when($status == 'refunded', function ($query) {
        //         return $query->Refunded();
        //     })
        //     ->when($status == 'scheduled', function ($query) {
        //         return $query->Scheduled();
        //     })
        //     ->when($status == 'on_going', function ($query) {
        //         return $query->Ongoing();
        //     })
        //     ->when(($status != 'all' && $status != 'scheduled' && $status != 'canceled' && $status != 'refund_requested' && $status != 'refunded' && $status != 'delivered' && $status != 'failed'), function ($query) {
        //         return $query->OrderScheduledIn(30);
        //     })
        //     ->when(isset($request->vendor), function ($query) use ($request) {
        //         return $query->whereHas('store', function ($query) use ($request) {
        //             return $query->whereIn('id', $request->vendor);
        //         });
        //     })
        //     ->when(isset($request->orderStatus) && $status == 'all', function ($query) use ($request) {
        //         return $query->whereIn('order_status', $request->orderStatus);
        //     })
        //     ->when(isset($request->scheduled) && $status == 'all', function ($query) {
        //         return $query->scheduled();
        //     })
        //     ->when(isset($request->order_type) && $type == 'order', function ($query) use ($request) {
        //         return $query->where('order_type', $request->order_type);
        //     })
        //     ->when(isset($request->from_date) && isset($request->to_date) && $request->from_date != null && $request->to_date != null, function ($query) use ($request) {
        //         return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
        //     })
        //     ->when($type == 'order', function ($query) {
        //         $query->StoreOrder();
        //     })
        //     ->when($type == 'parcel', function ($query) {
        //         $query->ParcelOrder();
        //     })
        // ->orderBy('schedule_at', 'desc')
        // ->get();

        
        // $orders = Order::get();

        // dd($orders->count());
        // $i=1;


        // $data = [];
        // foreach($orders as $order)
        // {
        //     $store = \App\Models\Store::where('id',$order['store_id'])->first();
        //     $user = \App\Models\User::where('id',$order['user_id'])->first();
        //     $zone = \App\Models\Zone::where('id',$order['zone_id'])->first();
        //     $module = \App\Models\Module::where('id',$order['module_id'])->first();

        //     // if($order['store_id']==null || $order['user_id']==null || $order['zone_id']==null ||
        //     // $order['module_id']==null)
        //     // {
        //     //     // dd($order['store_id'] , $order['user_id'] , $order['zone_id'] , $order['module_id']);
        //     //     dd('some id is empty');
        //     // }
        //     // dd($order);

        //     $adr = json_decode($order['delivery_address']);

        //     // if($adr == null)
        //     // {
        //     //     dd('adr is null');
        //     // }

        //     // if($order['id']==200456)
        //     // {
        //     //     dd($order);

                
        //     // }
            
        //     // dump($order['id']);
        //     // dump($order['user_id']);
        //     // dump($order['store_id']);


        //     $temp=[
        //         'Order ID'=>$order['id'],
        //         'Order Status'=>$order['order_status'],
        //         'Order Time'=>$order['created_at'],
        //         'Store ID'=>$store->id,
        //         'Store Name'=>$store->name,
        //         'Sub Total'=>$order['sub_total'],
        //         'Service fee %'=>$order['service_fee_percent'].'%',
        //         'Store Commission %'=>$order['gm_commission_percent'].'%',
        //         'Promo Discount'=>$order['coupon_discount_amount'],
        //         'Sales tax'=>$order['sales_tax'],
        //         'Service fee'=>$order['service_fee_amount'],
        //         'GoMeat Commission'=>$order['gm_commission'],
        //         'Delivery Charge'=>$order['delivery_charges'],
        //         'Tip'=>$order['dm_tips'],
        //         'Net to Store'=>$order['net_to_store'],
        //         'Order total/Cash'=>$order['order_amount'],
        //         'GoMeat Revenue'=>$order['gomeat_revenue'],
        //         'Promo Code'=>$order['coupon_code'],
        //         'Payment Method'=>$order['payment_method'],
        //         'Delivery Mode'=>$order['order_type'],
        //         'Transaction Status'=>$order['payment_status'],
        //         'Transaction ID'=>$order['transaction_reference'],
        //         'Customer ID'=>$user->id,
        //         'Customer Name'=>$user->f_name.' '.$user->l_name,
        //         'Customer Email'=>$user->email,
        //         'Customer Phone'=>$user->phone,
        //         'Delivery Address'=>$adr->address,
        //         'Pickup Address'=>$store->address,
        //         'Description'=>$order['order_note'],
        //         'Distance (in miles)'=>$order['distance'],
        //         'Pickup Time'=>$order['picked_up'],
        //         'Delivery Time'=>$order['delivered'],
        //         'Zones ID'=>$zone->name,
        //         'Module'=>$module->module_name
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // 'Order ID'=>$order['id'],
        //         // translate('messages.date')=>date('d M Y',strtotime($order['created_at'])),
        //         // translate('messages.customer')=>$order->customer?$order->customer['f_name'].' '.$order->customer['l_name']:translate('messages.invalid').' '.translate('messages.customer').' '.translate('messages.data'),
        //         // translate($type=='order'?'messages.store':'messages.parcel_category')=>\Str::limit($type=='order'?($order->store?$order->store->name:translate('messages.store deleted!')):($order->parcel_category?$order->parcel_category->name:translate('messages.not_found')),20,'...'),
        //         // translate('messages.payment').' '.translate('messages.status')=>$order->payment_status=='paid'?translate('messages.paid'):translate('messages.unpaid'),
        //         // translate('messages.total')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']),
        //         // translate('messages.order').' '.translate('messages.status')=>translate('messages.'. $order['order_status']),
        //         // translate('messages.order').' '.translate('messages.type')=>translate('messages.'.$order['order_type'])
        //     ];


        //     // dump($temp['Order ID']);
        //     // dump($order);

        //     // if($order['id']==200456)
        //     // {
        //     //     dd($temp);
        //     // }
            

        //     $data[]=$temp;
        //     // $i++;
        // }


        // dd($data);
        
        
        
        // if ($file_type == 'excel') {
        //     // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.xlsx');
        //     return (new FastExcel($data))->download('Orders.xlsx');
        // } else if ($file_type == 'csv') {
        //     // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.csv');
        //     return (new FastExcel($data))->download('Orders.csv');
        // }
        // // return (new FastExcel(OrderLogic::format_export_data($orders, $type)))->download('Orders.xlsx');
        // return (new FastExcel($data))->download('Orders.xlsx');
    }


    public function customer_list(Request $request)
    {
        $key = [];
        if ($request->search) {
            $key = explode(' ', $request['search']);
        }
        $customers = User::when(count($key) > 0, function ($query) use ($key) {
            foreach ($key as $value) {
                $query->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            };
        })
            ->orderBy('order_count', 'desc')->paginate(config('default_pagination'));
            // dd($customers);
            // return $customers;
        return view('admin-views.customer.list', compact('customers'));
    }

    public function status(User $customer, Request $request)
    {
        $customer->status = $request->status;
        $customer->save();

        try {
            if ($request->status == 0) {
                $customer->tokens->each(function ($token, $key) {
                    $token->delete();
                });
                if (isset($customer->cm_firebase_token)) {
                    $data = [
                        'title' => translate('messages.suspended'),
                        'description' => translate('messages.your_account_has_been_blocked'),
                        'order_id' => '',
                        'image' => '',
                        'type' => 'block'
                    ];
                    Helpers::send_push_notif_to_device($customer->cm_firebase_token, $data);

                    DB::table('user_notifications')->insert([
                        'data' => json_encode($data),
                        'user_id' => $customer->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        Toastr::success(translate('messages.customer') . translate('messages.status_updated'));
        return back();
    }

    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $customers = User::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            }
        })->orderBy('order_count', 'desc')->limit(50)->get();
        return response()->json([
            'view' => view('admin-views.customer.partials._table', compact('customers'))->render()
        ]);
    }

    public function view($id)
    {
        $customer = User::find($id);
        if (isset($customer)) {
            $orders = Order::latest()->where(['user_id' => $id])->Notpos()->paginate(config('default_pagination'));
            return view('admin-views.customer.customer-view', compact('customer', 'orders'));
        }
        Toastr::error(translate('messages.customer_not_found'));
        return back();
    }

    public function subscribedCustomers()
    {
        $data['subscribedCustomers'] = Newsletter::orderBy('id', 'desc')->get();
        return view('admin-views.customer.subscribed-emails', $data);
    }
    public function subscriberMailSearch(Request $request)
    {
        $key = explode(' ', $request['search']);
        $customers = Newsletter::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('email', 'like', "%". $value."%");
            }
        })->orderBy('id', 'desc')->get();
        return response()->json([
            'view' => view('admin-views.customer.partials._subscriber-email-table', compact('customers'))->render()
        ]);
    }

    public function get_customers(Request $request){
        $key = explode(' ', $request['q']);
        $data = User::
        where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                ->orWhere('l_name', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%");
            }
        })
        ->limit(8)
        ->get([DB::raw('id, CONCAT(f_name, " ", l_name, " (", phone ,")") as text')]);
        if($request->all) $data[]=(object)['id'=>false, 'text'=>translate('messages.all')];


        return response()->json($data);
    }

    public function settings()
    {
        $data = BusinessSetting::where('key','like','wallet_%')
            ->orWhere('key','like','loyalty_%')
            ->orWhere('key','like','ref_earning_%')
            ->orWhere('key','like','ref_earning_%')->get();
        $data = array_column($data->toArray(), 'value','key');
        //dd($data);
        return view('admin-views.customer.settings', compact('data'));
    }

    public function update_settings(Request $request)
    {

        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'add_fund_bonus'=>'nullable|numeric|max:100|min:0',
            'loyalty_point_exchange_rate'=>'nullable|numeric',
            'ref_earning_exchange_rate'=>'nullable|numeric',
        ]);
        BusinessSetting::updateOrInsert(['key' => 'wallet_status'], [
            'value' => $request['customer_wallet']??0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'loyalty_point_status'], [
            'value' => $request['customer_loyalty_point']??0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'ref_earning_status'], [
            'value' => $request['ref_earning_status'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'wallet_add_refund'], [
            'value' => $request['refund_to_wallet']??0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'loyalty_point_exchange_rate'], [
            'value' => $request['loyalty_point_exchange_rate'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'ref_earning_exchange_rate'], [
            'value' => $request['ref_earning_exchange_rate'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'loyalty_point_item_purchase_point'], [
            'value' => $request['item_purchase_point']??0
        ]);
        BusinessSetting::updateOrInsert(['key' => 'loyalty_point_minimum_point'], [
            'value' => $request['minimun_transfer_point']??0
        ]);

        Toastr::success(translate('messages.customer_settings_updated_successfully'));
        return back();
    }
}
