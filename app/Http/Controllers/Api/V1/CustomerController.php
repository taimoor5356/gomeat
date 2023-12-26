<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Item;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\RefUsers;
use App\Models\WalletTransaction;
use App\Models\BusinessSetting;
use App\CentralLogics\CustomerLogic;
use App\Models\PopUpUserMap;
use App\Models\UserFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Zone;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function address_list(Request $request)
    {
        $limit = $request['limit']??10;
        $offset = $request['offset']??1;

        $addresses = CustomerAddress::where('user_id', $request->user()->id)->latest()->paginate($limit, ['*'], 'page', $offset);
        
        $data =  [
            'total_size' => $addresses->total(),
            'limit' => $limit,
            'offset' => $offset,
            'addresses' => Helpers::address_data_formatting($addresses->items())
        ];
        return response()->json($data, 200);
    }

    public function add_new_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // $zone_id= $request->header('zoneId');
        // $temp = json_decode($zone_id,true);
        $point = new Point($request->latitude,$request->longitude);
        // $zone = Zone::contains('coordinates', $point)->get(['id']);
        $zone = Zone::contains('coordinates', $point)->get();

        // return $zone[0]->id;
        if(count($zone) == 0)
        {
            $errors = [];
            array_push($errors, ['code' => 'coordinates', 'message' => translate('messages.service_not_available_in_this_area')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        // $address = [
        //     'user_id' => $request->user()->id,
        //     'contact_person_name' => $request->contact_person_name,
        //     'contact_person_number' => $request->contact_person_number,
        //     'address_type' => $request->address_type,
        //     'address' => $request->address,
        //     'floor' => $request->floor,
        //     'road' => $request->road,
        //     'house' => $request->house,
        //     'longitude' => $request->longitude,
        //     'latitude' => $request->latitude,
        //     'zone_id' => $zone[0]->id,
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ];


        $address = new CustomerAddress();

        $address->user_id = $request->user()->id; //

        // $user = User::where('id',$request->user()->id)->first();
        $address->contact_person_name = $request->contact_person_name; //
        $address->contact_person_email = $request->contact_person_email; //
        $address->contact_person_number = $request->contact_person_number; //
        $address->address_type = $request->address_type; //
        $address->address = $request->address; //
        $address->floor = $request->floor; //
        $address->road = $request->road; //
        $address->house = $request->house; //
        $address->longitude = $request->longitude; //
        $address->latitude = $request->latitude; //
        $address->zone_id = $zone[0]->id; //
        $address->created_at = now(); //
        $address->updated_at = now(); //

        try{

            $address->save();
            return response()->json(['message' => translate('messages.successfully_added'),'zone_ids'=>array_column($zone->toArray(), 'id')], 200);
        }
        catch(\Exception $e){
            return response()->json($e, 200);
        }



        // return $address;
        // DB::table('customer_addresses')->insert($address);
    }

    public function update_address(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $point = new Point($request->latitude,$request->longitude);
        $zone = Zone::active()->contains('coordinates', $point)->first();
        if(!$zone)
        {
            $errors = [];
            array_push($errors, ['code' => 'coordinates', 'message' => translate('messages.service_not_available_in_this_area')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $address = [
            'user_id' => $request->user()->id,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'zone_id' => $zone->id,
            'created_at' => now(),
            'updated_at' => now()
        ];
        DB::table('customer_addresses')->where('id',$id)->update($address);
        return response()->json(['message' => translate('messages.updated_successfully'),'zone_id'=>$zone->id], 200);
    }

    public function delete_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (DB::table('customer_addresses')->where(['id' => $request['address_id'], 'user_id' => $request->user()->id])->first()) {
            DB::table('customer_addresses')->where(['id' => $request['address_id'], 'user_id' => $request->user()->id])->delete();
            return response()->json(['message' => translate('messages.successfully_removed')], 200);
        }
        return response()->json(['message' => translate('messages.not_found')], 404);
    }

    public function get_order_list(Request $request)
    {
        $orders = Order::where(['user_id' => $request->user()->id])->get();
        return response()->json($orders, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $details = OrderDetail::where(['order_id' => $request['order_id']])->get();
        foreach ($details as $det) {
            $det['product_details'] = json_decode($det['product_details'], true);
        }

        return response()->json($details, 200);
    }

    public function info(Request $request)
    {
        $data = $request->user();
        $data['order_count'] =(integer)$request->user()->orders->count();
        $data['member_since_days'] =(integer)$request->user()->created_at->diffInDays();
        unset($data['orders']);
        return response()->json($data, 200);
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            // 'email' => 'required|unique:users,email,'.$request->user()->id,
        ], [
            'f_name.required' => 'First name is required!',
            'l_name.required' => 'Last name is required!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image = $request->file('image');

        if ($request->has('image')) {
            $imageName = Helpers::update('profile/', $request->user()->image, 'png', $request->file('image'));
        } else {
            $imageName = $request->user()->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $request->user()->password;
        }

        $userDetails = [
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'image' => $imageName,
            'password' => $pass,
            'updated_at' => now()
        ];

        User::where(['id' => $request->user()->id])->update($userDetails);

        return response()->json(['message' => translate('messages.successfully_updated')], 200);
    }
    public function update_interest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'interest' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userDetails = [
            'interest' => json_encode($request->interest),
        ];

        User::where(['id' => $request->user()->id])->update($userDetails);

        return response()->json(['message' => translate('messages.interest_updated_successfully')], 200);
    }

    public function update_cm_firebase_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        DB::table('users')->where('id',$request->user()->id)->update([
            'cm_firebase_token'=>$request['cm_firebase_token']
        ]);

        return response()->json(['message' => translate('messages.updated_successfully')], 200);
    }

    public function get_suggested_item(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }


        $zone_id= $request->header('zoneId');
        $module_id= $request->header('moduleId');

        $interest = $request->user()->interest;
        $interest = isset($interest) ? json_decode($interest):null;
        // return response()->json($interest, 200);
        
        $products =  Item::active()->whereHas('store', function($q)use($zone_id){
            $q->whereIn('zone_id', json_decode($zone_id, true));
        })
        ->when(isset($interest), function($q)use($interest){
            return $q->whereIn('category_id',$interest);
        })
        ->where('module_id',$module_id)
        ->when($interest == null, function($q){
            return $q->popular();
        })->limit(5)->get();
        $products = Helpers::product_data_formatting($products, true, false, app()->getLocale());
        return response()->json($products, 200);
        // return response()->json([], 200);
    }

    public function update_zone(Request $request)
    {
        if (!$request->hasHeader('zoneId') && is_numeric($request->header('zoneId'))) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $customer = $request->user();
        $customer->zone_id = (integer)$request->header('zoneId');
        $customer->save();
        return response()->json([], 200);
    }

    public function remove_account(Request $request)
    {
        $user = $request->user();
        
        if(Order::where('user_id', $user->id)->whereIn('order_status', ['pending','accepted','confirmed','processing','handover','picked_up'])->count())
        {
            return response()->json(['errors'=>[['code'=>'on-going', 'message'=>translate('messages.user_account_delete_warning')]]],203);
        }
        $user_ref = RefUsers::where('reference_number', $user->phone)->delete();
        $request->user()->token()->revoke();
        $user->delete();

        return response()->json([]);
    }    

    public function deleteUserAccount(Request $request)
    {
        $phone = $request->phone;
        $password = $request->password;
        $user = User::where('phone', $phone)->first();
        if (isset($user)) {
            if (($user->phone == $phone) && (Hash::check($password, $user->password))) {
                if(Order::where('user_id', $user->id)->whereIn('order_status', ['pending','accepted','confirmed','processing','handover','picked_up'])->count())
                {
                    return redirect()->back()->with('error', 'Something went wrong');
                }
                $user_ref = RefUsers::where('reference_number', $user->phone)->delete();
                if (!is_null($user->token())) {
                    $user->token()->revoke();
                }
                $user->delete();
            
                return redirect()->back()->with('success', 'Account deleted');
            } else {
                return redirect()->back()->with('error', 'Credentials not match');
            }
        } else {
            return redirect()->back()->with('error', 'User not found');
        }
    }


    public function setPopUpUserMap(Request $request)
    {
        //user_id

        $user = $request->user();

        $popup = PopUpUserMap::where('user_id',$user->id)->first();

        if(isset($popup['is_seen']))
        {
            $popup->is_seen = 1;
            $popup->save();
        }
        else
        {
            $popup = new PopUpUserMap;
            $popup->user_id = $user->id;
            $popup->pop_up_id = 1;
            $popup->is_seen = 1;
            $popup->save();
        }

        $data =  [
            'message' => 'success'
        ];
        return response()->json($data, 200);
        // dd($popups);
    }

    public function getPopUpUserMap(Request $request)
    {
        $user = $request->user();

        // return $user;
        //user_id
        $popup = PopUpUserMap::where('user_id',$user->id)->first();

        // return $popup;

        if(isset($popup['is_seen']))
        {
            $data =  [
                'message' => 'success',
                'is_seen' => $popup['is_seen']
                // (isset($popup['is_seen']) ? $popup['is_seen'] : 0)
            ];
        }
        else
        {
            $data =  [
                'message' => 'success',
                'is_seen' => 0
                // (isset($popup['is_seen']) ? $popup['is_seen'] : 0)
            ];
        }
        return response()->json($data, 200);

    }

    public function user_feedback(Request $request)
    {
        $user = $request->user();

        $userFeedback  = new UserFeedback;

        $userFeedback->user_id = $user->id;
        $userFeedback->address = $request->address;
        $userFeedback->store_name = $request->store_name;
        $userFeedback->store_type = $request->store_type;

        // return $userFeedback;
        $userFeedback->save();

        $data =  [
            'message' => 'success'
        ];
        return response()->json($data, 200);

    }

    public function global_helper(Request $request) 
    {
        $order = Order::where('id',$request->order_id)->first();

        if($order->payment_status=='paid')
        {
            $user = $request->user();

            $check_duplicate_ref = RefUsers::where('reference_number', $user->phone)->first();
            
            if($check_duplicate_ref)
            {
                $check_duplicate_transaciton = WalletTransaction::where('reference', $user->phone)->first();
    
                if($check_duplicate_transaciton)
                {
                    $data =  [
                        'message' => 'wallet transaction existed'
                    ];
                    return response()->json($data, 200);
                }
                else
                {
    
                    // $referar_user = User::where('ref_code', '=', $checkRefCode)->first();
    
                    $ref_code_exchange_amt = BusinessSetting::where('key', 'ref_earning_exchange_rate')->first()->value;
    
                    $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($check_duplicate_ref->user_id, $ref_code_exchange_amt, 'referrer', $user->phone);
    
                    $data =  [
                        'message' => 'wallet transaction created'
                    ];
                    return response()->json($data, 200);
                }
            }
    
    
            $data =  [
                'message' => 'refferal not found'
            ];
            return response()->json($data, 200);
        }
        $data =  [
            'message' => 'order unpaid'
        ];
        return response()->json($data, 200);
    }
}
