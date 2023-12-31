<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\StoreLogic;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\StoreCatMap;
use App\Models\Category;
use App\Models\Order;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\Campaign;
use App\Models\WithdrawRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    // category end point

    public function get_categories(Request $request)
    {
        $parent_categories = StoreCatMap::where('store_id',$request['vendor']->stores[0]->id)
        ->select('parent_id')->groupBy('parent_id')->get()->toArray();
        // return $parent_categories;
        if(count($parent_categories)>0)
        {
            for($i =0 ; $i<sizeof($parent_categories); $i++)
            {
                $sub_categories = StoreCatMap::where('store_id',$request['vendor']->stores[0]->id)
                ->where('parent_id',$parent_categories[$i]['parent_id'])->get();
                unset($temp);
                foreach($sub_categories as $sub_category)
                {
                    $checkItem = Item::withoutGlobalScope('translate')->type('all')
                    ->where('category_id',$sub_category->category_id)
                    ->where('store_id', $request['vendor']->stores[0]->id);
                    if ($checkItem->exists()) {
                        $temp[]= Category::where('id',$sub_category->category_id)->first();
                    }
                }
                $final['category']=Category::where('id',$parent_categories[$i]['parent_id'])->first();
                $final['subcategory']=$temp;

                // dump($final);
                $cat_subcat[]=$final; 
                
            }
            return response()->json($cat_subcat, 200);
        }
        
        return response()->json(['errors' => 'no items or category found'], 404);
        
    }
    public function get_profile(Request $request)
    {
        $vendor = $request['vendor'];
        $store = Helpers::store_data_formatting($vendor->stores[0], false);
        $discount=Helpers::get_store_discount($vendor->stores[0]);
        unset($store['discount']);
        $store['discount']=$discount;
        $store['schedules']=$store->schedules()->get();
        $store['module']=$store->module;

        $vendor['order_count'] =$vendor->orders->where('order_type','!=','pos')->whereNotIn('order_status',['canceled','failed'])->count();
        $vendor['todays_order_count'] =$vendor->todaysorders->where('order_type','!=','pos')->whereNotIn('order_status',['canceled','failed'])->count();
        $vendor['this_week_order_count'] =$vendor->this_week_orders->where('order_type','!=','pos')->whereNotIn('order_status',['canceled','failed'])->count();
        $vendor['this_month_order_count'] =$vendor->this_month_orders->where('order_type','!=','pos')->whereNotIn('order_status',['canceled','failed'])->count();
        $vendor['member_since_days'] =$vendor->created_at->diffInDays();
        $vendor['cash_in_hands'] =$vendor->wallet?(float)$vendor->wallet->collected_cash:0;
        $vendor['balance'] =$vendor->wallet?(float)$vendor->wallet->balance:0;
        $vendor['total_earning'] =$vendor->wallet?(float)$vendor->wallet->total_earning:0;
        $vendor['todays_earning'] =(float)$vendor->todays_earning()->sum('store_amount');
        $vendor['this_week_earning'] =(float)$vendor->this_week_earning()->sum('store_amount');
        $vendor['this_month_earning'] =(float)$vendor->this_month_earning()->sum('store_amount');
        $vendor["stores"] = $store;
        $vendor["country"] = isset($store->country) ? $store->country->short_name : '';
        unset($vendor['orders']);
        unset($vendor['rating']);
        unset($vendor['todaysorders']);
        unset($vendor['this_week_orders']);
        unset($vendor['wallet']);
        unset($vendor['todaysorders']);
        unset($vendor['this_week_orders']);
        unset($vendor['this_month_orders']);

        return response()->json($vendor, 200);
    }

    public function active_status(Request $request)
    {
        $store = $request->vendor->stores[0];
        $store->active = $store->active?0:1;
        $store->save();
        return response()->json(['message' => $store->active?translate('messages.store_opened'):translate('messages.store_temporarily_closed')], 200);
    }

    public function get_earning_data(Request $request)
    {
        $vendor = $request['vendor'];
        $data= StoreLogic::get_earning_data($vendor->id);
        return response()->json($data, 200);
    }

    public function update_profile(Request $request)
    {
        $vendor = $request['vendor'];
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required|unique:vendors,phone,'.$vendor->id,
            'password'=>'nullable|min:6',
        ], [
            'f_name.required' => translate('messages.first_name_is_required'),
            'l_name.required' => translate('messages.Last name is required!'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image = $request->file('image');

        if ($request->has('image')) {
            $imageName = Helpers::update('vendor/', $vendor->image, 'png', $request->file('image'));
        } else {
            $imageName = $vendor->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $vendor->password;
        }
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->phone = $request->phone;
        $vendor->image = $imageName;
        $vendor->password = $pass;
        $vendor->updated_at = now();
        $vendor->save();

        return response()->json(['message' => translate('messages.profile_updated_successfully')], 200);
    }

    public function get_current_orders(Request $request)
    {
        // return Auth::user();
        $vendor = $request['vendor']; 

        $orders = Order::whereHas('store.vendor', function($query) use($vendor){
            $query->where('id', $vendor->id);
        })
        ->with('customer')
        // ->where(function($query)use($vendor){
        //     if(config('order_confirmation_model') == 'store' || $vendor->stores[0]->self_delivery_system)
        //     {
        //         $query->whereIn('order_status', ['accepted','pending','confirmed', 'processing', 'handover','picked_up']);
        //     }
        //     else
        //     {
        //         $query->whereIn('order_status', ['confirmed', 'processing', 'handover','picked_up'])
        //         ->orWhere(function($query){
        //             $query->where('payment_status','paid')->where('order_status', 'accepted');
        //         })
        //         ->orWhere(function($query){
        //             $query->where('order_status','pending')->where('order_type', 'take_away');
        //         });
        //     }
        // })
        // ->Notpos()
        ->orderBy('schedule_at', 'desc')
        ->get();
        $orders= Helpers::order_data_formatting($orders, true);
        // $orders= 'api response';
        return response()->json($orders, 200);
    }
    
    public function get_completed_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'status' => 'required|in:all,refunded,delivered',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];

        $paginator = Order::whereHas('store.vendor', function($query) use($vendor){
            $query->where('id', $vendor->id);
        })
        ->with('customer')
        ->when($request->status == 'all', function($query){
            return $query->whereIn('order_status', ['refunded', 'delivered']);
        })
        ->when($request->status != 'all', function($query)use($request){
            return $query->where('order_status', $request->status);
        })
        ->Notpos()
        ->latest()
        ->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders= Helpers::order_data_formatting($paginator->items(), true);
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }

    public function update_order_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'status' => 'required|in:confirmed,processing,handover,delivered,canceled'
        ]);

        $validator->sometimes('otp', 'required', function ($request) {
            return (Config::get('order_delivery_verification')==1 && $request['status']=='delivered');
        });

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];

        $order = Order::whereHas('store.vendor', function($query) use($vendor){
            $query->where('id', $vendor->id);
        })
        ->where('id', $request['order_id'])
        ->Notpos()
        ->first();

        if($request['order_status']=='canceled')
        {
            if(!config('canceled_by_store'))
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'status', 'message' => translate('messages.you_can_not_cancel_a_order')]
                    ]
                ], 403);
            }
            else if($order->confirmed)
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'status', 'message' => translate('messages.you_can_not_cancel_after_confirm')]
                    ]
                ], 403);
            }
        }

        if($request['status'] =="confirmed" && !$vendor->stores[0]->self_delivery_system && config('order_confirmation_model') == 'deliveryman' && $order->order_type != 'take_away')
        {
            return response()->json([
                'errors' => [
                    ['code' => 'order-confirmation-model', 'message' => translate('messages.order_confirmation_warning')]
                ]
            ], 403);
        }

        if($order->picked_up != null)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'status', 'message' => translate('messages.You_can_not_change_status_after_picked_up_by_delivery_man')]
                ]
            ], 403);
        }

        if($request['status']=='delivered' && $order->order_type != 'take_away' && !$vendor->stores[0]->self_delivery_system)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'status', 'message' => translate('messages.you_can_not_delivered_delivery_order')]
                ]
            ], 403);
        }
        if(Config::get('order_delivery_verification')==1 && $request['status']=='delivered' && $order->otp != $request['otp'])
        {
            return response()->json([
                'errors' => [
                    ['code' => 'otp', 'message' => 'Not matched']
                ]
            ], 401);
        }

        if ($request->status == 'delivered' && $order->transaction == null) {
            if($order->payment_method == 'cash_on_delivery')
            {
                $ol = OrderLogic::create_transaction($order,'store', null);
            }
            else
            {
                $ol = OrderLogic::create_transaction($order,'admin', null);
            }
            
            $order->payment_status = 'paid';
        } 

        if($request->status == 'delivered')
        {
            $order->details->each(function($item, $key){
                if($item->item)
                {
                    $item->item->increment('order_count');
                }
            });
            $order->customer->increment('order_count');
            $order->store->increment('order_count');
        }
        if($request->status == 'canceled' || $request->status == 'delivered')
        {
            if($order->delivery_man)
            {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders>1?$dm->current_orders-1:0;
                $dm->save();
            }                   
        }

        $order->order_status = $request['status'];
        $order[$request['status']] = now();
        $order->save();
        Helpers::send_order_notification($order);

        return response()->json(['message' => 'Status updated'], 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $vendor = $request['vendor'];

        $order = Order::whereHas('store.vendor', function($query) use($vendor){
            $query->where('id', $vendor->id);
        })
        ->with(['customer','details'])
        ->where('id', $request['order_id'])
        ->Notpos()
        ->first();
        // $order_data[]={$details,$order};
        // $details = $order;//->details;
        
        $order->details = Helpers::order_details_data_formatting($order->details);
        // $order_data=new array();
        // array_push($order_data,);

        return response()->json($order, 200);
    }

    public function get_all_orders(Request $request)
    {
        $vendor = $request['vendor'];

        $orders = Order::whereHas('store.vendor', function($query) use($vendor){
            $query->where('id', $vendor->id);
        })
        ->with('customer')
        ->Notpos()
        ->orderBy('schedule_at', 'desc')
        ->get();
        $orders= Helpers::order_data_formatting($orders, true);
        return response()->json($orders, 200);
    }

    public function update_fcm_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $vendor = $request['vendor'];

        Vendor::where(['id' => $vendor['id']])->update([
            'firebase_token' => $request['fcm_token']
        ]);

        return response()->json(['message'=>'successfully updated!'], 200);
    }

    public function get_notifications(Request $request){
        $vendor = $request['vendor'];

        $notifications = Notification::active()->where(function($q) use($vendor){
            $q->whereNull('zone_id')->orWhere('zone_id', $vendor->stores[0]->zone_id);
        })->where('tergat', 'store')->where('created_at', '>=', \Carbon\Carbon::today()->subDays(7))->get();

        $notifications->append('data');

        $user_notifications = UserNotification::where('vendor_id', $vendor->id)->where('created_at', '>=', \Carbon\Carbon::today()->subDays(7))->get();
        
        $notifications =  $notifications->merge($user_notifications);

        try {
            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_basic_campaigns(Request $request)
    {
        $vendor = $request['vendor'];
        $store_id = $vendor->stores[0]->id;
        $module_id = $vendor->stores[0]->module_id;      

        $campaigns=Campaign::with('stores')->module($module_id)->Running()->latest()->get();
        $data = [];

        foreach ($campaigns as $item) {
            $store_ids = count($item->stores)?$item->stores->pluck('id')->toArray():[];
            if($item->start_date)
            {
                $item['available_date_starts']=$item->start_date->format('Y-m-d');
                unset($item['start_date']);
            }
            if($item->end_date)
            {
                $item['available_date_ends']=$item->end_date->format('Y-m-d');
                unset($item['end_date']);
            }

            if (count($item['translations'])>0 ) {
                $translate = array_column($item['translations']->toArray(), 'value', 'key');
                $item['title'] = $translate['title'];
                $item['description'] = $translate['description'];
            }

            $item['is_joined'] = in_array($store_id, $store_ids)?true:false;
            unset($item['stores']);
            array_push($data, $item);
        }
        // $data = CampaignLogic::get_basic_campaigns($vendor->stores[0]->id, $request['limite'], $request['offset']);
        return response()->json($data, 200);
    }

    public function remove_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $campaign = Campaign::where('status', 1)->find($request->campaign_id);
        if(!$campaign)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'campaign', 'message'=>'Campaign not found or upavailable!']
                ]
            ]);
        }
        $store = $request['vendor']->stores[0];
        $campaign->stores()->detach($store);
        $campaign->save();
        return response()->json(['message'=>translate('messages.you_are_successfully_removed_from_the_campaign')], 200);
    }
    public function addstore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $campaign = Campaign::where('status', 1)->find($request->campaign_id);
        if(!$campaign)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'campaign', 'message'=>'Campaign not found or upavailable!']
                ]
            ]);
        }
        $store = $request['vendor']->stores[0];
        $campaign->stores()->attach($store);
        $campaign->save();
        return response()->json(['message'=>translate('messages.you_are_successfully_joined_to_the_campaign')], 200);
    }

    public function get_items(Request $request)
    {
        $limit=$request->limit?$request->limit:25;
        $offset=$request->offset?$request->offset:1;

        $type = $request->query('type', 'all');

        $paginator = Item::withoutGlobalScope('translate')->type($type)
        ->where('category_id',$request->category_id)
        ->where('store_id', $request['vendor']->stores[0]->id)->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'items' => Helpers::product_data_formatting($paginator->items(), true, true, app()->getLocale())
        ];   

        return response()->json($data, 200);
    }

    public function update_bank_info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|max:191',
            'branch' => 'required|max:191',
            'holder_name' => 'required|max:191',
            'account_no' => 'required|max:191'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $bank = $request['vendor'];
        $bank->bank_name = $request->bank_name;
        $bank->branch = $request->branch;
        $bank->holder_name = $request->holder_name;
        $bank->account_no = $request->account_no;
        $bank->save();

        return response()->json(['message'=>translate('messages.bank_info_updated_successfully'),200]);
    }

    public function withdraw_list(Request $request)
    {
        $withdraw_req = WithdrawRequest::where('vendor_id', $request['vendor']->id)->latest()->get();

        $temp = [];
        $status = [
            0=>'Pending',
            1=>'Approved',
            2=>'Denied'
        ];
        foreach($withdraw_req as $item)
        {
            $item['status'] = $status[$item->approved];
            $item['requested_at'] = $item->created_at->format('Y-m-d H:i:s');
            $item['bank_name'] = $request['vendor']->bank_name;
            unset($item['created_at']);
            unset($item['approved']);
            $temp[] = $item;
        }

        return response()->json($temp, 200);
    }

    public function request_withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $w = $request['vendor']->wallet;
        if ($w->balance >= $request['amount']) {
            $data = [
                'vendor_id' => $w->vendor_id,
                'amount' => $request['amount'],
                'transaction_note' => null,
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
            try
            {
                DB::table('withdraw_requests')->insert($data);
                $w->increment('pending_withdraw', $request['amount']);
                return response()->json(['message'=>translate('messages.withdraw_request_placed_successfully')],200);
            }
            catch(\Exception $e)
            {
                return response()->json($e);
            }
        }
        return response()->json([
            'errors'=>[
                ['code'=>'amount', 'message'=>translate('messages.insufficient_balance')]
            ]
        ],403);
    }

    public function remove_account(Request $request)
    {
        $vendor = $request['vendor'];
        
        if(Order::where('store_id', $vendor->stores[0]->id)->whereIn('order_status', ['pending','accepted','confirmed','processing','handover','picked_up'])->count())
        {
            return response()->json(['errors'=>[['code'=>'on-going', 'message'=>translate('messages.user_account_delete_warning')]]],203);
        }

        if($vendor->wallet && $vendor->wallet->collected_cash > 0)
        {
            return response()->json(['errors'=>[['code'=>'on-going', 'message'=>translate('messages.user_account_wallet_delete_warning')]]],203);
        }

        if (Storage::disk('public')->exists('vendor/' . $vendor['image'])) {
            Storage::disk('public')->delete('vendor/' . $vendor['image']);
        }
        if (Storage::disk('public')->exists('store/' . $vendor->stores[0]->logo)) {
            Storage::disk('public')->delete('store/' . $vendor->stores[0]->logo);
        }

        if (Storage::disk('public')->exists('store/cover/' . $vendor->stores[0]->cover_photo)) {
            Storage::disk('public')->delete('store/cover/' . $vendor->stores[0]->cover_photo);
        }
        foreach($vendor->stores[0]->deliverymen as $dm) {
            if (Storage::disk('public')->exists('delivery-man/' . $dm['image'])) {
                Storage::disk('public')->delete('delivery-man/' . $dm['image']);
            }
    
            foreach (json_decode($dm['identity_image'], true) as $img) {
                if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                    Storage::disk('public')->delete('delivery-man/' . $img);
                }
            }
        }
        $vendor->stores[0]->deliverymen()->delete();
        $vendor->stores()->delete();
        $vendor->delete();
        return response()->json([]);
    }
}
