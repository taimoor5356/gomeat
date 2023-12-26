<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\CustomerLogic;
use App\Models\User;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Mail\EmailVerification;
use App\Models\BusinessSetting;
use App\Models\RefUsers;
use App\Models\WalletTransaction;
use App\CentralLogics\SMS_module;
use App\Models\EmailVerifications;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

// inserted auth, hash
use Illuminate\Support\Facades\Hash;
use Auth;

class CustomerAuthController extends Controller
{
    // public function verify_phone(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|min:11|max:14',
    //         'otp' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    //     $user = User::where('phone', $request->phone)->first();
    //     if ($user) {
    //         if ($user->is_phone_verified) {
    //             return response()->json([
    //                 'message' => translate('messages.phone_number_is_already_varified')
    //             ], 200);
    //         }

    //         if (env('APP_MODE') == 'demo') {
    //             if ($request['otp'] == "1234") {
    //                 $user->is_phone_verified = 1;
    //                 $user->save();

    //                 return response()->json([
    //                     'message' => translate('messages.phone_number_varified_successfully'),
    //                     'otp' => 'inactive'
    //                 ], 200);
    //             }
    //             return response()->json([
    //                 'message' => translate('messages.phone_number_and_otp_not_matched')
    //             ], 404);
    //         }

    //         $data = DB::table('phone_verifications')->where([
    //             'phone' => $request['phone'],
    //             'token' => $request['otp'],
    //         ])->first();

    //         if ($data) {
    //             DB::table('phone_verifications')->where([
    //                 'phone' => $request['phone'],
    //                 'token' => $request['otp'],
    //             ])->delete();

    //             $user->is_phone_verified = 1;
    //             $user->save();

    //             return response()->json([
    //                 'message' => translate('messages.phone_number_varified_successfully'),
    //                 'otp' => 'inactive'
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'message' => translate('messages.phone_number_and_otp_not_matched')
    //             ], 404);
    //         }
    //     }
    //     return response()->json([
    //         'message' => translate('messages.not_found')
    //     ], 404);
    // }

    public function verify_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:14',
            'otp'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $data = DB::table('phone_verifications')->where([
            'phone' => $request['phone'],
            'token' => $request['otp'],
        ])->first();

        if($data)
        {
            DB::table('phone_verifications')->where([
                'phone' => $request['phone'],
                'token' => $request['otp'],
            ])->delete();
            return response()->json([
                'message' => trans('messages.phone_number_varified_successfully'),
                'otp' => 'inactive'
            ], 200);
        }
        else
        {
            return response()->json([
                'message' => trans('messages.phone_number_and_otp_not_matched')
            ], 404);
        }
    }

    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        if (BusinessSetting::where(['key' => 'email_verification'])->first()->value) {
            $token = rand(1000, 9999);
            DB::table('email_verifications')->insert([
                'email' => $request['email'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (config('mail.status')) {
                Mail::to($request['email'])->send(new EmailVerification($token));
            }
            return response()->json([
                'message' => 'Email is ready to register',
                'token' => 'active'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Email is ready to register',
                'token' => 'inactive'
            ], 200);
        }
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = EmailVerifications::where(['email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => translate('messages.token_varified'),
            ], 200);
        }

        $errors = [];
        array_push($errors, ['code' => 'token', 'message' => translate('messages.token_not_found')]);
        return response()->json(
            ['errors' => $errors],
            404
        );
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            // 'email' => 'required|unique:users',
            'phone' => 'required|unique:users'
        ], [
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // $customer_verification = BusinessSetting::where('key', 'customer_verification')->first()->value;

        //Check Exists Ref Code
        $check_duplicate_ref = RefUsers::where('reference_number', $request->phone)->first();

        //Check Exists Ref Code Condition
        if ($check_duplicate_ref) {
            return response()->json(['errors'=>['code'=>'ref_code','message'=>'Referral code already used']]);
        } else {

            //User Creation
            $user = User::create([
                'f_name' => $request->first_name,
                'l_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'os' => $request->os,
                'platform' => $request->platform,
                'login_medium' => $request->medium,
                'social_access_token' => $request->token,
                'social_id' => $request->uniqueId,
                'is_phone_verified' => 1,
                'password' => bcrypt($request->password),
            ]);
            $user->one_signal_id = $request->oneSignalId;
            $user->last_login = now();
            $user->ref_code = Helpers::generate_referer_code($user);
            $user->save();

            //Save point to refeer
            if ($request->ref_code) {
                $checkRefCode = $request->ref_code;
                $referar_user = User::where('ref_code', '=', $checkRefCode)->first();
                $ref_status = BusinessSetting::where('key', 'ref_earning_status')->first()->value;
                if ($ref_status != '1') {
                    $errors = [];
                    array_push($errors, ['code' => 'ref_code', 'message' => 'Referer Disable']);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }

                if (!$referar_user) {
                    $errors = [];
                    array_push($errors, ['code' => 'ref_code', 'message' => 'Referer Code not Found']);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }

                $check_duplicate_ref = RefUsers::where('reference_number', $request->phone)->first();

                if ($check_duplicate_ref) {
                    $errors = [];
                        array_push($errors, ['code'=>'ref_code','message'=>'Referral code already used']);
                        return response()->json([
                            'errors' => $errors
                        ], 405);
                    // return response()->json(['errors'=>['code'=>'ref_code','message'=>'Referral code already used']]);
                } else if (strtoupper($request->ref_code) == 'GOMT' || strtoupper($request->ref_code) == 'DGMT') {
                    // $ref_code_exchange_amt = BusinessSetting::where('key', 'ref_earning_exchange_rate')->first()->value;
                    $ref_code_exchange_amt = 100;
    
                    $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($user->id, $ref_code_exchange_amt, 'referrer', $user->phone);

                    return response()->json($refer_wallet_transaction, 'inside the function');
                }
                //dd($refer_wallet_transaction);

                $ref_data_insert = new RefUsers;
                $ref_data_insert->user_id = $referar_user->id;
                $ref_data_insert->reference_number = $user->phone;

                $ref_data_insert->save();


                try {
                    if (config('mail.status')) {
                        Mail::to($referar_user->email)->send(new \App\Mail\AddFundToWallet($refer_wallet_transaction));
                    }
                } catch (\Exception $ex) {
                    info($ex);
                }
            }
        }



        $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

        // if ($customer_verification && env('APP_MODE') != 'demo') {
        //     $otp = rand(1000, 9999);
        //     DB::table('phone_verifications')->updateOrInsert(
        //         ['phone' => $request['phone']],
        //         [
        //             'token' => $otp,
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]
        //     );
        //     try {
        //         if (config('mail.status')) {
        //             Mail::to($request['email'])->send(new EmailVerification($otp));
        //         }
        //     } catch (\Exception $ex) {
        //         info($ex);
        //     }

        //     $response = SMS_module::send($request['phone'], $otp);
        //     if ($response != 'success') {
        //         $errors = [];
        //         array_push($errors, [
        //             'code' => 'otp',
        //             'message' => translate('messages.faield_to_send_sms')
        //         ]);
        //         return response()->json([
        //             'errors' => $errors
        //         ], 405);
        //     }
        // }
        // try {
        //     if (config('mail.status')) {
        //         Mail::to($request->email)->send(new \App\Mail\CustomerRegistration($request->f_name . ' ' . $request->l_name));
        //     }
        // } catch (\Exception $ex) {
        //     info($ex);
        // }
        return response()->json(['token' => $token,'user_data' => $user], 200);
        // return response()->json(['token' => $token, 'is_phone_verified' => 1, 'phone_verify_end_url' => "api/v1/auth/verify-phone"], 200);
    }

    // public function register(Request $request)
    // {
        
        
    //     // $validator = Validator::make($request->all(), [
    //     //     'first_name' => 'required',
    //     //     'last_name' => 'required',
    //     //     'email' => 'required|unique:users',
    //     //     'phone' => 'required|unique:users',
    //     // ]);

    //     // if ($validator->fails()) {
    //     //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     // }
        
    //     $user = new User();
        

    //     if(empty($request->first_name) || is_null($request->first_name))
    //     {
    //         $user->f_name = '';
    //     }
    //     else
    //     {
    //         $user->f_name = $request->first_name;
    //     }

    //     if(empty($request->last_name) || is_null($request->last_name))
    //     {
    //         $user->l_name = '';
    //     }
    //     else
    //     {
    //         $user->l_name = $request->last_name;
    //     }

        

    //     // if(empty($request->email) || is_null($request->email))
    //     // {
    //     //     $user->email = '';
    //     // }
    //     // else
    //     // {
    //     // }
    //     $user->email = $request->email;

    //     if(empty($request->password) || is_null($request->password))
    //     {
    //         $user->password = null;
    //     }
    //     else
    //     {
    //         $user->password = bcrypt($request->password);
    //     }

    //     $user->phone = $request->phone;
    //     $user->login_medium = $request->medium;
    //     $user->social_access_token = $request->token;
    //     $user->social_id = $request->uniqueId;
    //     $user->is_phone_verified = 1;
        
        

    //     $user->save();
        
    //     $token = $user->createToken('RestaurantCustomerAuth')->accessToken;
    //     // return response()->json(['user_data' => $user], 200);
    //     return response()->json(['token' => $token,'user_data' => $user], 200);
    // }

    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required',
    //         'password' => 'required|min:6'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }

    //     $data = [
    //         'phone' => $request->phone,
    //         'password' => $request->password
    //     ];
    //     $customer_verification = BusinessSetting::where('key', 'customer_verification')->first()->value;
    //     if (auth()->attempt($data)) {
    //         $token = auth()->user()->createToken('RestaurantCustomerAuth')->accessToken;
    //         if (!auth()->user()->status) {
    //             $errors = [];
    //             array_push($errors, ['code' => 'auth-003', 'message' => translate('messages.your_account_is_blocked')]);
    //             return response()->json([
    //                 'errors' => $errors
    //             ], 403);
    //         }
    //         if ($customer_verification && !auth()->user()->is_phone_verified && env('APP_MODE') != 'demo') {
    //             $otp = rand(1000, 9999);
    //             DB::table('phone_verifications')->updateOrInsert(
    //                 ['phone' => $request['phone']],
    //                 [
    //                     'token' => $otp,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]
    //             );
    //             $response = SMS_module::send($request['phone'], $otp);
    //             if ($response != 'success') {

    //                 $errors = [];
    //                 array_push($errors, ['code' => 'otp', 'message' => translate('messages.faield_to_send_sms')]);
    //                 return response()->json([
    //                     'errors' => $errors
    //                 ], 405);
    //             }
    //         }
    //         $user = auth()->user();
    //         if($user->ref_code == null && isset($user->id)){
    //             $ref_code = Helpers::generate_referer_code($user);
    //             DB::table('users')->where('phone', $user->phone)->update(['ref_code' => $ref_code]);
    //         }
    //         return response()->json(['token' => $token, 'is_phone_verified' => auth()->user()->is_phone_verified], 200);
    //     } else {
    //         $errors = [];
    //         array_push($errors, ['code' => 'auth-001', 'message' => translate('messages.Unauthorized')]);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 401);
    //     }
    // }

    public function login(Request $request)
    {
        // return 'onesignalid: '.$request->medium;
        $auth = false;
        
        if($request->medium == 'apple')
        {
            $auth = User::where('social_id',$request->uniqueId)->where('login_medium',$request->medium)->first();

            // $user->social_id=$request->uniqueId;

            // dd();

            // return $auth;

            if($auth && $auth->status == 0)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => trans('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            else if($auth && $auth->status == 1)
            {
                $auth = true;
            }
            else
            {
                $user_check = User::where('social_id',$request->uniqueId)->where('login_medium',NULL)->first();
                if($user_check)
                {
                    $auth= true;
                }
                else
                {
                    $auth= false;
                }
                // $auth = false;
            }
            
            // return $auth;
        }
        else if($request->medium == 'facebook')
        {

            $auth = User::where('social_id',$request->uniqueId)->where('login_medium',$request->medium)->first();

            // $auth = User::where('social_id',$request->uniqid)->where('login_medium',$request->medium)->first();

            
            if($auth && $auth->status == 0)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => trans('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            else if($auth && $auth->status == 1)
            {
                $auth = true;
            }
            else
            {
                
                $user_check = User::where('social_id',$request->uniqueId)->where('login_medium',NULL)->first();
                if($user_check)
                {
                    $auth= true;
                }
                else
                {
                    $auth= false;
                }
                
                // $auth = false;
            }
        }
        else if($request->medium == 'google')
        {

            $auth = User::where('email',$request->email)->where('login_medium',$request->medium)->first();

            
            if($auth && $auth->status == 0)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => trans('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            else if($auth && $auth->status == 1)
            {
                $auth = true;
            }
            else
            {
                
                $user_check = User::where('email',$request->email)->where('login_medium',NULL)->first();
                if($user_check)
                {
                    $auth= true;
                }
                else
                {
                    $auth= false;
                }
                
                // $auth = false;
            }
        }
        else if($request->medium == 'custom_email')
        {
            $auth = User::where('phone',$request->phone)->where('login_medium',$request->medium)->first();

            if($auth && $auth->status == 0)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => trans('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            else if($auth && $auth->status == 1 )
            {
                if (Hash::check($request->password, $auth->password))
                {
                    $auth = true;
                }
                else
                {
                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => 'password not matched']);
                    return response()->json([
                        'errors' => $errors
                    ], 408);
                }
            }
            else
            {
                $phone = substr($request->phone, 3);
                // $user = User::where('phone',$request->phone)->first();
                // $user = User::where('phone','LIKE',"%{$phone}%")->first();
                $user_check = User::where('phone','LIKE',"%{$phone}%")->where('login_medium',NULL)->first();
                if($user_check)
                {
                    $auth = true;
                }
                else
                {
                    $auth= false;
                }
                // $auth = false;
            }
        }
        
        
        if ($auth) {
            
            if($request->medium == 'custom_email')
            {
                // ->where('name', 'LIKE', "%{$searchTerm}%") 
                $phone = substr($request->phone, 3);
                // $user = User::where('phone',$request->phone)->first();
                $user = User::where('phone','LIKE',"%{$phone}%")->first();
                if($user->last_login===NULL)
                {
                    $user->phone = $request->phone;
                    $user->password = bcrypt($request->password);
                }
                if($user->ref_code === NULL)
                {
                    Helpers::generate_referer_code($user);
                } 
                $user->os=$request->os;
                $user->platform=$request->platform;
                $user->login_medium = $request->medium;
                $user->last_login = now();
                $user->one_signal_id = $request->oneSignalId;
                $user->update();
            }
            else
            {
                if($request->medium == 'apple')
                {
                    // $user = User::where('email',$request->email)->first();
                    $user = User::where('social_id',$request->uniqueId)->first();

                    $user->social_id=$request->uniqueId;
                    $user->social_access_token=$request->token;
                    // }
                    if($user->ref_code === NULL)
                    {
                        Helpers::generate_referer_code($user);
                    } 
                    $user->os=$request->os;
                    $user->platform=$request->platform;
                    $user->login_medium = $request->medium;
                    $user->one_signal_id = $request->oneSignalId;
                    $user->last_login = now();
                    $user->update();

                }
                else if($request->medium == 'facebook')
                {

                    $user = User::where('social_id',$request->uniqueId)->first();
                    // if($user->social_id== null && $user->social_access_token==null)
                    // {
                    //     'social_access_token' => $request->token,
                    // 'social_id' => $request->uniqueId,
                        $user->social_id=$request->uniqueId;
                        $user->social_access_token=$request->token;
                    // }
                    if($user->ref_code === NULL)
                    {
                        Helpers::generate_referer_code($user);
                    } 
                    $user->os=$request->os;
                    $user->platform=$request->platform;
                    $user->login_medium = $request->medium;
                    $user->one_signal_id = $request->oneSignalId;
                    $user->last_login = now();
                    $user->update();
                }
                else 
                {

                    $user = User::where('email',$request->email)->first();
                    // if($user->social_id== null && $user->social_access_token==null)
                    // {
                    //     'social_access_token' => $request->token,
                    // 'social_id' => $request->uniqueId,
                        $user->social_id=$request->uniqueId;
                        $user->social_access_token=$request->token;
                    // }
                    if($user->ref_code === NULL)
                    {
                        Helpers::generate_referer_code($user);
                    } 
                    $user->os=$request->os;
                    $user->platform=$request->platform;
                    $user->login_medium = $request->medium;
                    $user->one_signal_id = $request->oneSignalId;
                    $user->last_login = now();
                    $user->update();
                }
            }
            
            $token = $user->createToken('RestaurantCustomerAuth')->accessToken;
            return response()->json(['token' => $token, 'user_data'=>$user], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => trans('messages.Unauthorized')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }


    public function get_otp(Request $request)
    {
        $check_duplicate_ref = RefUsers::where('reference_number', $request['phone'])->first();

        if ($check_duplicate_ref) {
            $errors = [];
                array_push($errors, ['code'=>'ref_code','message'=>'Referral code already used']);
                return response()->json([
                    'errors' => $errors
                ], 405);
            // return response()->json(['errors'=>['code'=>'ref_code','message'=>'Referral code already used']]);
        } 
        if ($request->ref_code) {
            $checkRefCode = $request->ref_code;
            $referar_user = User::where('ref_code', '=', $checkRefCode)->first();
            $ref_status = BusinessSetting::where('key', 'ref_earning_status')->first()->value;
            if ($ref_status != '1') {
                $errors = [];
                array_push($errors, ['code' => 'ref_code', 'message' => 'Referer Disable']);
                return response()->json([
                    'errors' => $errors
                ], 405);
            }

            if (!$referar_user) {
                $errors = [];
                array_push($errors, ['code' => 'ref_code', 'message' => 'Referer Code not Found']);
                return response()->json([
                    'errors' => $errors
                ], 405);
            }

            // $ref_code_exchange_amt = BusinessSetting::where('key', 'ref_earning_exchange_rate')->first()->value;

            // $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($referar_user->id, $ref_code_exchange_amt, 'referrer', $user->phone);
            //dd($refer_wallet_transaction);
            // try {
            //     if (config('mail.status')) {
            //         Mail::to($referar_user->email)->send(new \App\Mail\AddFundToWallet($refer_wallet_transaction));
            //     }
            // } catch (\Exception $ex) {
            //     info($ex);
            // }
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users'
        ]);
        

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        if(env('APP_MODE') !='demo')
        {
            $otp = rand(1000, 9999);
            
            $response = SMS_module::send($request['phone'],$otp);
            if($response != 'success')
            {
                // DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                // [
                // 'token' => '1111',
                // 'created_at' => now(),
                // 'updated_at' => now(),
                // ]);
                $errors = [];
                array_push($errors, ['code' => 'otp', 'message' => trans('messages.faield_to_send_sms')]);
                return response()->json([
                    'errors' => $errors
                ], 405);
                // return response()->json(['otp' => '1111','twillio-api-response'=>'success'], 200);
            }
            else
            {
                DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                [
                'token' => $otp,
                'created_at' => now(),
                'updated_at' => now(),
                ]);
                return response()->json(['otp' => $otp,'twillio-api-response'=>$response], 200);
            }
        }
    }
}
