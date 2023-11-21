<?php

namespace App\Http\Controllers\Admin;

use App\Models\Zone;
use App\Models\AddOn;
use App\Models\Store;
use App\Models\Module;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\StoreCatMap;
use App\Scopes\StoreScope;
use App\Models\StoreWallet;
use Illuminate\Http\Request;
use App\Models\StoreSchedule;
use App\CentralLogics\Helpers;
use App\Models\WithdrawRequest;
use App\CentralLogics\StoreLogic;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryHasState;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Grimzy\LaravelMysqlSpatial\Types\Point;


class VendorController extends Controller
{
    public function index($country = null)
    {
        if (!empty($country)) {
            return view('admin-views.vendor.index_pk');
        } else {
            return view('admin-views.vendor.index');
        }
    }
    public function indexClone()
    {
        return view('admin-views.vendor.clone');
    }

    public function store(Request $request, $country = null)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name' => 'required|max:191',
            'address' => 'required|max:1000',
            'latitude' => 'required',
            'longitude' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            // 'minimum_delivery_time' => 'required',
            // 'maximum_delivery_time' => 'required',
            // 'delivery_time_type'=>'required',
            'password' => 'required|min:6',
            'zone_id' => 'required',
            'module_id' => 'required',
            'logo' => 'required',
            'gm_commission' => 'required'
            // 'minimum_order' => 'required'
        ], [
            'f_name.required' => translate('messages.first_name_is_required')
        ]);

        if($request->zone_id)
        {
            $point = new Point($request->latitude, $request->longitude);
            $zone = Zone::contains('coordinates', $point)->where('id', $request->zone_id)->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->save();

        $store = new Store;
        $store->name = $request->name;
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        $store->address = $request->address;
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->radius = $request->radius;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        // $store->minimum_order = $request->minimum_order;
        $store->minimum_order = $request->module_id==1?'35.00':'15.00';
        // $store->tax = $request->tax;
        $store->gm_commission = $request->gm_commission;
        // $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->delivery_time = $request->module_id==1?'02-03 hours':'60-90 minutes';
        $store->module_id = $request->module_id;
        
        //////////////////////////////////////////////////////////////////////
        // For Pakistani Zone
        //////////////////////////////////////////////////////////////////////
        if (!empty($country)) {
            if ($country == 'pk') {
                $store->legal_business_name = !empty($request->legal_business_name) ? $request->legal_business_name : '' ;
                $store->fbr_registration_status = !empty($request->fbr_registration_status) ? 'active' : 'in_active' ;
                $store->ntn_number = !empty($request->ntn_number) ? $request->ntn_number : '' ;
                $store->strn_number = !empty($request->strn_number) ? $request->strn_number : '' ;

                // Country and State Data
                $store->country_id = $request->country_id;
                $store->state_id = $request->state_id;
                $store->store_online_payment = $request->store_online_payment;
                $store->store_cash_payment = $request->store_cash_payment;
                $store->filer_status = !empty($request->filer_status) ? 'active' : 'in_active';
                $store->restaurant_online_payment = $request->restaurant_online_payment;
                $store->restaurant_cash_payment = $request->restaurant_cash_payment;
                $country = Country::find($request->country_id);
                $state = CountryHasState::find($request->state_id);
                $store->country_info = json_encode($country);
                $store->state_info = json_encode($state);
            }
        }
        $store->bank_name = !empty($request->bank_name)? $request->bank_name : '';
        $store->bank_iban = !empty($request->bank_iban)? $request->bank_iban : '';

        $store->save();
        $store->module->increment('stores_count');
        if(config('module.'.$store->module->module_type)['always_open'])
        {
            StoreLogic::insert_schedule($store->id);
        }
        // $store->zones()->attach($request->zone_ids);
        Toastr::success(translate('messages.store').translate('messages.added_successfully'));
        return redirect('admin/vendor/list');
    }
    public function storeClone(Request $request)
    {
        // dd($request->store_tobe_clone);
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name' => 'required|max:191',
            'address' => 'required|max:1000',
            'latitude' => 'required',
            'longitude' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            // 'minimum_delivery_time' => 'required',
            // 'maximum_delivery_time' => 'required',
            // 'delivery_time_type'=>'required',
            'password' => 'required|min:6',
            'zone_id' => 'required',
            // 'number_of_clones' => 'required',
            'store_tobe_clone' => 'required',
            'logo' => 'required',
            'gm_commission' => 'required'
            // 'minimum_order' => 'required'
        ], [
            'f_name.required' => translate('messages.first_name_is_required')
        ]);

        // if($request->number_of_clones<=1)
        // {

            if($request->zone_id)
            {
                $point = new Point($request->latitude, $request->longitude);
                $zone = Zone::contains('coordinates', $point)->where('id', $request->zone_id)->first();
                if(!$zone){
                    $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                    return back()->withErrors($validator)
                            ->withInput();
                }
            }
            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $vendor = new Vendor();
            $vendor->f_name = $request->f_name;
            $vendor->l_name = $request->l_name;
            $vendor->email = $request->email;
            $vendor->phone = $request->phone;
            $vendor->password = bcrypt($request->password);
            $vendor->save();
    
            $store = new Store;
            $store->name = $request->name;
            $store->phone = $request->phone;
            $store->email = $request->email;
            $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
            $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
            $store->address = $request->address;
            $store->latitude = $request->latitude;
            $store->longitude = $request->longitude;
            $store->radius = $request->radius;
            $store->vendor_id = $vendor->id;
            $store->zone_id = $request->zone_id;
            // $store->tax = $request->tax;
            $store->gm_commission = $request->gm_commission;
            // $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
            $store_old = Store::where('id',$request->store_tobe_clone)->first();
            $store->delivery_time = $store_old->module_id==1?'02-03 hours':'60-90 minutes';
            $store->minimum_order = $store_old->module_id==1?'35.00':'15.00';
            // dd($store_old->module_id);
            $store->module_id = $store_old->module_id;
            $store->save();
            $store->module->increment('stores_count');
    
            // items
            $items = Item::where('store_id',$request->store_tobe_clone)->get();
            if($items)
            {
    
                foreach($items as $item)
                {
                    $new_item = new Item;
                
                    $new_item->old_id = null;
        
                    $new_item->name = $item->name;
                    $new_item->description = $item->description;
                    $new_item->long_description = $item->long_description;
                    $new_item->image = $item->image;
                    $new_item->category_id = $item->category_id;
                    $new_item->category_ids = $item->category_ids;
                    $new_item->variations = $item->variations;
                    $new_item->multi_select = $item->multi_select;
                    $new_item->add_ons = $item->add_ons;
                    $new_item->attributes = $item->attributes;
                    $new_item->choice_options = $item->choice_options;
                    $new_item->price = $item->price;
                    $new_item->tax = $item->tax;
                    $new_item->tax_type = $item->tax_type;
                    $new_item->discount = $item->discount;
                    $new_item->discount_type = $item->discount_type;
                    $new_item->sales_tax = $item->sales_tax;
                    $new_item->gm_commission = $item->gm_commission;
                    $new_item->available_time_starts = $item->available_time_starts;
                    $new_item->available_time_ends = $item->available_time_ends;
                    $new_item->veg = $item->veg;
                    $new_item->status = $item->status;
                    $new_item->store_id = $store->id;
                    // $new_item->created_at = $item->created_at;
                    // $new_item->updated_at = $item->updated_at;
                    $new_item->order_count = 0;
                    $new_item->avg_rating = 0;
                    $new_item->rating_count = 0;
                    $new_item->rating = null;
                    $new_item->module_id = $item->module_id;
                    $new_item->stock = 100000;
                    $new_item->unit_id = $item->unit_id;
                    $new_item->images = $item->images;
        
                    
                    $new_item->save();
        
                }
        
        
                // storeCatMap
                $storeCatMaps = StoreCatMap::where('store_id',$request->store_tobe_clone)->get();
                foreach($storeCatMaps as $storeCatMap)
                {
                    $new_storeCatMap = new StoreCatMap;
        
                    $new_storeCatMap->store_id = $store->id;
                    $new_storeCatMap->category_id = $storeCatMap->category_id;
                    $new_storeCatMap->parent_id = $storeCatMap->parent_id;
                    $new_storeCatMap->status = $storeCatMap->status;
                    $new_storeCatMap->module_id = $storeCatMap->module_id;
                    $new_storeCatMap->zone_id = $storeCatMap->zone_id;
        
                    // dump($new_storeCatMap);
        
                    $new_storeCatMap->save();
                }
                // store schedule
        
                for($j=0; $j<=6; $j++)
                {
                    $storeSchedule = new StoreSchedule();
                    $storeSchedule->store_id = $store->id;
                    $storeSchedule->day = $j;
                    $storeSchedule->opening_time = '00:00:00';
                    $storeSchedule->closing_time = '23:59:50';
                    $storeSchedule->save();
                    // dd($storeSchedule);
                }
    
                Toastr::success('Store Cloned Successfully');
                return redirect('admin/vendor/list');
            }
            
    
            // if(config('module.'.$store->module->module_type)['always_open'])
            // {
            //     StoreLogic::insert_schedule($store->id);
            // }
            // $store->zones()->attach($request->zone_ids);
            Toastr::success('Store Cloned Successfully without Items');
            return redirect('admin/vendor/list');
        // }
        // else
        // {
        //     for($index = $number_of_clones; $index<=$number_of_clones; $index++)
        //     {
        //         if($request->zone_id)
        //         {
        //             $point = new Point($request->latitude, $request->longitude);
        //             $zone = Zone::contains('coordinates', $point)->where('id', $request->zone_id)->first();
        //             if(!$zone){
        //                 $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
        //                 return back()->withErrors($validator)
        //                         ->withInput();
        //             }
        //         }
        //         if ($validator->fails()) {
        //             return back()
        //                 ->withErrors($validator)
        //                 ->withInput();
        //         }
        //         $vendor = new Vendor();
        //         $vendor->f_name = $request->f_name;
        //         $vendor->l_name = $request->l_name;
        //         $vendor_email=explode('@', $request->email);
        //         $vendor->email = $vendor_email[0].$index.'@'.$vendor_email[1];
        //         $vendor->phone = $request->phone.$index;
        //         $vendor->password = bcrypt($request->password);
        //         $vendor->save();
        
        //         $store = new Store;
        //         $store->name = $request->name;
        //         $store->phone = $request->phone.$index;
        //         $store_email=explode('@', $request->email);
        //         $store->email = $store_email[0].$index.'@'.$store_email[1];
        //         $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        //         $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        //         $store->address = $request->address;
        //         $store->latitude = $request->latitude;
        //         $store->longitude = $request->longitude;
        //         $store->radius = $request->radius;
        //         $store->vendor_id = $vendor->id;
        //         $store->zone_id = $request->zone_id;
        //         // $store->tax = $request->tax;
        //         $store->gm_commission = $request->gm_commission;
        //         $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        //         $store_old = Store::where('id',$request->store_tobe_clone)->first();
        //         // dd($store_old->module_id);
        //         $store->module_id = $store_old->module_id;
        //         $store->save();
        //         $store->module->increment('stores_count');
        
        //         // items
        //         $items = Item::where('store_id',$request->store_tobe_clone)->get();
        //         if($items)
        //         {
        
        //             foreach($items as $item)
        //             {
        //                 $new_item = new Item;
                    
        //                 $new_item->old_id = null;
            
        //                 $new_item->name = $item->name;
        //                 $new_item->description = $item->description;
        //                 $new_item->long_description = $item->long_description;
        //                 $new_item->image = $item->image;
        //                 $new_item->category_id = $item->category_id;
        //                 $new_item->category_ids = $item->category_ids;
        //                 $new_item->variations = $item->variations;
        //                 $new_item->multi_select = $item->multi_select;
        //                 $new_item->add_ons = $item->add_ons;
        //                 $new_item->attributes = $item->attributes;
        //                 $new_item->choice_options = $item->choice_options;
        //                 $new_item->price = $item->price;
        //                 $new_item->tax = $item->tax;
        //                 $new_item->tax_type = $item->tax_type;
        //                 $new_item->discount = $item->discount;
        //                 $new_item->discount_type = $item->discount_type;
        //                 $new_item->sales_tax = $item->sales_tax;
        //                 $new_item->gm_commission = $item->gm_commission;
        //                 $new_item->available_time_starts = $item->available_time_starts;
        //                 $new_item->available_time_ends = $item->available_time_ends;
        //                 $new_item->veg = $item->veg;
        //                 $new_item->status = $item->status;
        //                 $new_item->store_id = $store->id;
        //                 // $new_item->created_at = $item->created_at;
        //                 // $new_item->updated_at = $item->updated_at;
        //                 $new_item->order_count = 0;
        //                 $new_item->avg_rating = 0;
        //                 $new_item->rating_count = 0;
        //                 $new_item->rating = null;
        //                 $new_item->module_id = $item->module_id;
        //                 $new_item->stock = 100000;
        //                 $new_item->unit_id = $item->unit_id;
        //                 $new_item->images = $item->images;
            
                        
        //                 $new_item->save();
            
        //             }
            
            
        //             // storeCatMap
        //             $storeCatMaps = StoreCatMap::where('store_id',$request->store_tobe_clone)->get();
        //             foreach($storeCatMaps as $storeCatMap)
        //             {
        //                 $new_storeCatMap = new StoreCatMap;
            
        //                 $new_storeCatMap->store_id = $store->id;
        //                 $new_storeCatMap->category_id = $storeCatMap->category_id;
        //                 $new_storeCatMap->parent_id = $storeCatMap->parent_id;
        //                 $new_storeCatMap->status = $storeCatMap->status;
        //                 $new_storeCatMap->module_id = $storeCatMap->module_id;
        //                 $new_storeCatMap->zone_id = $storeCatMap->zone_id;
            
        //                 // dump($new_storeCatMap);
            
        //                 $new_storeCatMap->save();
        //             }
        //             // store schedule
            
        //             for($j=0; $j<=6; $j++)
        //             {
        //                 $storeSchedule = new StoreSchedule();
        //                 $storeSchedule->store_id = $store->id;
        //                 $storeSchedule->day = $j;
        //                 $storeSchedule->opening_time = '00:00:00';
        //                 $storeSchedule->closing_time = '23:59:50';
        //                 $storeSchedule->save();
        //                 // dd($storeSchedule);
        //             }
        
        //             Toastr::success('Store Cloned Successfully');
        //             return redirect('admin/vendor/list');
        //         }
                
        
        //         // if(config('module.'.$store->module->module_type)['always_open'])
        //         // {
        //         //     StoreLogic::insert_schedule($store->id);
        //         // }
        //         // $store->zones()->attach($request->zone_ids);
        //         Toastr::success('Store Cloned Successfully without Items');
        //         return redirect('admin/vendor/list');
        //     }

        // }

        // Toastr::error('Store not Cloned');
        // return redirect('admin/vendor/list');

    }

    public function edit($id)
    {
        if(env('APP_MODE')=='demo' && $id == 2)
        {
            Toastr::warning(translate('messages.you_can_not_edit_this_store_please_add_a_new_store_to_edit'));
            return back();
        }
        $store = Store::findOrFail($id);
        return view('admin-views.vendor.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            'name' => 'required|max:191',
            'email' => 'required|unique:vendors,email,'.$store->vendor->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors,phone,'.$store->vendor->id,
            'zone_id'=>'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required',
            'gm_commission' => 'required',
            'password' => 'nullable|min:6',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type'=>'required',
            'minimum_order'=>'required'
        ], [
            'f_name.required' => translate('messages.first_name_is_required')
        ]);

        if($request->zone_id)
        {
            $point = new Point($request->latitude, $request->longitude);
            $zone = Zone::contains('coordinates', $point)->where('id', $request->zone_id)->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput();
        }
        $vendor = Vendor::findOrFail($store->vendor->id);
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = strlen($request->password)>1?bcrypt($request->password):$store->vendor->password;
        $vendor->save();

        $store->email = $request->email;
        $store->phone = $request->phone;
        $store->logo = $request->has('logo') ? Helpers::update('store/', $store->logo, 'png', $request->file('logo')) : $store->logo;
        $store->cover_photo = $request->has('cover_photo') ? Helpers::update('store/cover/', $store->cover_photo, 'png', $request->file('cover_photo')) : $store->cover_photo;
        $store->name = $request->name;
        $store->address = $request->address;
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->radius = $request->radius;
        $store->zone_id = $request->zone_id;
        $store->minimum_order = $request->minimum_order;
        // $store->tax = $request->tax;
        $store->gm_commission = $request->gm_commission;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->save();
        Toastr::success(translate('messages.store').translate('messages.updated_successfully'));
        return redirect('admin/vendor/list');
    }

    public function destroy(Request $request, Store $store)
    {
        if(env('APP_MODE')=='demo' && $store->id == 2)
        {
            Toastr::warning(translate('messages.you_can_not_delete_this_store_please_add_a_new_store_to_delete'));
            return back();
        }
        if (Storage::disk('public')->exists('store/' . $store['logo'])) {
            Storage::disk('public')->delete('store/' . $store['logo']);
        }
        $store->delete();

        $vendor = Vendor::findOrFail($store->vendor->id);
        $vendor->delete();
        Toastr::success(translate('messages.store').' '.translate('messages.removed'));
        return back();
    }

    public function view(Store $store, $tab=null, $sub_tab='cash')
    {
        $wallet = $store->vendor->wallet;
        if(!$wallet)
        {
            $wallet= new StoreWallet();
            $wallet->vendor_id = $store->vendor->id;
            $wallet->total_earning= 0.0;
            $wallet->total_withdrawn=0.0;
            $wallet->pending_withdraw=0.0;
            $wallet->created_at=now();
            $wallet->updated_at=now();
            $wallet->save();
        }
        if($tab == 'settings')
        {
            return view('admin-views.vendor.view.settings', compact('store'));
        }
        else if($tab == 'order')
        {
            return view('admin-views.vendor.view.order', compact('store'));
        }
        else if($tab == 'item')
        {
            return view('admin-views.vendor.view.product', compact('store'));
        }
        else if($tab == 'discount')
        {
            return view('admin-views.vendor.view.discount', compact('store'));
        }
        else if($tab == 'transaction')
        {
            return view('admin-views.vendor.view.transaction', compact('store', 'sub_tab'));
        }

        else if($tab == 'reviews')
        {
            return view('admin-views.vendor.view.review', compact('store', 'sub_tab'));
        }
        return view('admin-views.vendor.view.index', compact('store', 'wallet'));
    }

    public function view_tab(Store $store)
    {

        Toastr::error(translate('messages.unknown_tab'));
        return back();
    }

    public function list(Request $request)
    {
        $zone_id = $request->query('zone_id', 'all');
        $type = $request->query('type', 'all');
        $stores=null;

        if((isset($request->module_id) && $request->module_id =='all') &&
         (isset($request->zone_id) && $request->zone_id=='all'))
        {
            $stores = Store::
            with('vendor','module')->type($type)->latest()->paginate(config('default_pagination'));
        }
        else if((isset($request->module_id) && $request->module_id =='all') &&
         (isset($request->zone_id) && $request->zone_id!='all'))
        {
            $stores = Store::
            when(is_numeric($zone_id), function($query)use($zone_id){
                return $query->where('zone_id', $zone_id);
            })
            ->with('vendor','module')->type($type)->latest()->paginate(config('default_pagination'));
        }
        else
        {
            $stores = Store::when(is_numeric($zone_id), function($query)use($zone_id){
                    return $query->where('zone_id', $zone_id);
            })
            ->when($request->query('module_id', null), function($query)use($request){
                return $query->module($request->query('module_id'));
            })
            ->with('vendor','module')->type($type)->latest()->paginate(config('default_pagination'));

        }
        
        $zone = is_numeric($zone_id)?Zone::findOrFail($zone_id):null;
        return view('admin-views.vendor.list', compact('stores', 'zone','type'));
    }

    public function search(Request $request){
        // $key = explode(' ', $request['search']);
        // $stores=Store::orWhereHas('vendor',function ($q) use ($key) {
        // // $stores=Store::Where('vendor',function ($q) use ($key) {
        //     foreach ($key as $value) {
        //         $q->orWhere('f_name', 'like', "%{$value}%")
        //             ->orWhere('l_name', 'like', "%{$value}%")
        //             ->orWhere('email', 'like', "%{$value}%")
        //             ->orWhere('phone', 'like', "%{$value}%");
        //     }
        // })
        // // ->where(function ($q) use ($key) {
        // //     foreach ($key as $value) {
        // //         $q->orWhere('name', 'like', "%{$value}%")
        // //             ->orWhere('email', 'like', "%{$value}%")
        // //             ->orWhere('phone', 'like', "%{$value}%");
        // //     }
        // // })
        // ->get();

        // dd($request['search']);


        // $zone_id=$request->zone_id;
        // $module_id=$request->module_id;
        // $zone_id = $request->query('zone_id', 'all');

        $stores=null;
        if(isset($request->zone_id) && $request->zone_id!='all')
        {
            if(isset($request->module_id) && $request->module_id!='all')
            {
                // dump('1zone: '.$requets->zone_id);
                // dump('1module: '.$requets->module_id);
                // dump('1zone and module set');
                $stores = Store::
                where('zone_id',$request->zone_id)
                ->where('module_id',$request->module_id)
                ->where('name','like','%'.$request['search'].'%')
                ->get();
            }
            else
            {
                // dump('2zone set');
                // dump('2zone: '.$requets->zone_id);
                $stores = Store::
                where('zone_id',$request->zone_id)
                ->where('name','like','%'.$request['search'].'%')
                ->get();
            }

        }
        else if(isset($request->module_id) && $request->module_id!='all')
        {
            // dump('3module set');
            // dump('3module: '.$requets->module_id);

            $stores = Store::
            where('module_id',$request->module_id)
            ->where('name','like','%'.$request['search'].'%')
            ->get();
        }
        else
        {
            // dump('4module and zone not set');
            // dump('4module: '.$requets->module_id);
            // dump('4zone: '.$requets->zone_id);

            $stores = Store::
            where('name','like','%'.$request['search'].'%')
            ->get();
        }
        
        
        
        
        // $stores = Store::
        // when(is_numeric($zone_id), function($query)use($zone_id){
        //     return $query->where('zone_id', $zone_id);
        // })
        // ->where($request->query('module_id', null), function($query)use($request){
        //     return $query->module($request->query('module_id'));
        // })
        // ->Where('name','like','%'.$request['search'].'%')
        // // ->orWhere('email','like','%'.$request['search'].'@gomeat.io%')
        // // ->orWhere('phone','like','%'.$request['search'].'%')
        // ->get();

        // dd($stores);
        $total=$stores->count();
        return response()->json([
            'view'=>view('admin-views.vendor.partials._table',compact('stores'))->render(), 'total'=>$total
        ]);
    }

    public function get_modules(Request $request){
        $modules = Module::where('status',1)->get();
        // $data[] = null;
        foreach($modules as $module)
        {
            $temp['id'] = $module->id;
            $temp['name'] = $module->module_name;
            $data[] = $temp;
        }
        if(isset($request->all))
        {
            $data[]=(object)['id'=>'all', 'text'=>'All'];
        }

        // dd($request);
        return response()->json($data);
    }
    public function get_stores(Request $request){
        $zone_ids = isset($request->zone_ids)?(count($request->zone_ids)>0?$request->zone_ids:[]):0;

        // $data=null;
        // if(!isset($request->module_id))
        // {
        //     $data = Store::withOutGlobalScopes()
        //     ->where('stores.name', 'like', '%'.$request->q.'%')->limit(8)->get([DB::raw('stores.id as id, CONCAT(stores.name, " (", zones.name,")") as text')]);
        // }
        // else
        // {

            $data = Store::withOutGlobalScopes()
            ->join('zones', 'zones.id', '=', 'stores.zone_id')
            ->when($zone_ids, function($query) use($zone_ids){
                $query->whereIn('stores.zone_id', $zone_ids);
            })
            ->when($request->module_id, function($query)use($request){
                $query->where('module_id', $request->module_id);
            })
            ->when($request->module_type, function($query)use($request){
                $query->whereHas('module', function($q)use($request){
                    $q->where('module_type', $request->module_type);
                });
            })
            ->where('stores.name', 'like', '%'.$request->q.'%')->limit(8)->get([DB::raw('stores.id as id, CONCAT(stores.name, " (", zones.name,")") as text')]);
        // }
        if(isset($request->all))
        {
            $data[]=(object)['id'=>'all', 'text'=>'All'];
        }

        // dd($request);
        return response()->json($data);
    }

    

    public function status(Store $store, Request $request)
    {
        $store->status = $request->status;
        $store->save();
        $vendor = $store->vendor;

        try
        {
            if($request->status == 0)
            {   $vendor->auth_token = null;
                if(isset($vendor->fcm_token))
                {
                    $data = [
                        'title' => translate('messages.suspended'),
                        'description' => translate('messages.your_account_has_been_suspended'),
                        'order_id' => '',
                        'image' => '',
                        'type'=> 'block'
                    ];
                    Helpers::send_push_notif_to_device($vendor->fcm_token, $data);
                    DB::table('user_notifications')->insert([
                        'data'=> json_encode($data),
                        'vendor_id'=>$vendor->id,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }

            }

        }
        catch (\Exception $e) {
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        Toastr::success(translate('messages.store').translate('messages.status_updated'));
        return back();
    }

    public function store_status(Store $store, Request $request)
    {
        if($request->menu == "schedule_order" && !Helpers::schedule_order())
        {
            Toastr::warning(translate('messages.schedule_order_disabled_warning'));
            return back();
        }

        if((($request->menu == "delivery" && $store->take_away==0) || ($request->menu == "take_away" && $store->delivery==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.can_not_disable_both_take_away_and_delivery'));
            return back();
        }

        if((($request->menu == "veg" && $store->non_veg==0) || ($request->menu == "non_veg" && $store->veg==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.veg_non_veg_disable_warning'));
            return back();
        }
        if($request->menu == "self_delivery_system" && $request->status == '0') {
            $store['free_delivery'] = 0;
        }

        $store[$request->menu] = $request->status;
        $store->save();
        Toastr::success(translate('messages.store').translate('messages.settings_updated'));
        return back();
    }

    public function discountSetup(Store $store, Request $request)
    {
        $message=translate('messages.discount');
        $message .= $store->discount?translate('messages.updated_successfully'):translate('messages.added_successfully');
        $store->discount()->updateOrinsert(
        [
            'store_id' => $store->id
        ],
        [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => 'percent'
        ]
        );
        return response()->json(['message'=>$message], 200);
    }

    public function updateStoreSettings(Store $store, Request $request)
    {
        $request->validate([
            'minimum_order'=>'required',
            // 'comission'=>'required',
            // 'tax'=>'required',
            'minimum_delivery_time' => 'required|regex:/^([0-9]{2})$/|min:2|max:2',
            'maximum_delivery_time' => 'required|regex:/^([0-9]{2})$/|min:2|max:2',
        ]);

        // if($request->comission_status)
        // {
        //     $store->comission = $request->comission;
        // }
        // else{
        //     $store->comission = null;
        // }

        $store->minimum_order = $request->minimum_order;
        // $store->tax = $request->tax;
        $store->order_place_to_schedule_interval = $request->order_place_to_schedule_interval;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;

        $store->save();
        Toastr::success(translate('messages.store').translate('messages.settings_updated'));
        return back();
    }

    public function update_application(Request $request)
    {
        $store = Store::findOrFail($request->id);
        $store->vendor->status = $request->status;
        $store->vendor->save();
        if($request->status) $store->status = 1;
        $store->save();
        try{
            if ( config('mail.status') ) {
                Mail::to($request['email'])->send(new \App\Mail\SelfRegistration($request->status==1?'approved':'denied', $store->vendor->f_name.' '.$store->vendor->l_name));
            }
        }catch(\Exception $ex){
            info($ex);
        }
        Toastr::success(translate('messages.application_status_updated_successfully'));
        return back();
    }

    public function cleardiscount(Store $store)
    {
        $store->discount->delete();
        Toastr::success(translate('messages.store').translate('messages.discount_cleared'));
        return back();
    }

    public function withdraw()
    {
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req =WithdrawRequest::with(['vendor'])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.wallet.withdraw', compact('withdraw_req'));
    }

    public function withdraw_view($withdraw_id, $seller_id)
    {
        $wr = WithdrawRequest::with(['vendor'])->where(['id' => $withdraw_id])->first();
        return view('admin-views.wallet.withdraw-view', compact('wr'));
    }

    public function status_filter(Request $request){
        session()->put('withdraw_status_filter',$request['withdraw_status_filter']);
        return response()->json(session('withdraw_status_filter'));
    }

    public function withdrawStatus(Request $request, $id)
    {
        $withdraw = WithdrawRequest::findOrFail($id);
        $withdraw->approved = $request->approved;
        $withdraw->transaction_note = $request['note'];
        if ($request->approved == 1) {
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->increment('total_withdrawn', $withdraw->amount);
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            Toastr::success(translate('messages.seller_payment_approved'));
            return redirect()->route('admin.vendor.withdraw_list');
        } else if ($request->approved == 2) {
            StoreWallet::where('vendor_id', $withdraw->vendor_id)->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            Toastr::info(translate('messages.seller_payment_denied'));
            return redirect()->route('admin.vendor.withdraw_list');
        } else {
            Toastr::error(translate('messages.not_found'));
            return back();
        }
    }

    public function get_addons(Request $request)
    {
        $cat = AddOn::withoutGlobalScope(StoreScope::class)->withoutGlobalScope('translate')->where(['store_id' => $request->store_id])->active()->get();
        $res = '';
        foreach ($cat as $row) {
            $res .= '<option value="' . $row->id.'"';
            if(count($request->data))
            {
                $res .= in_array($row->id, $request->data)?'selected':'';
            }
            $res .=  '>' . $row->name . '</option>';
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function get_store_data(Store $store)
    {
        return response()->json($store);
    }

    public function store_filter($id)
    {
        if ($id == 'all') {
            if (session()->has('store_filter')) {
                session()->forget('store_filter');
            }
        } else {
            session()->put('store_filter', Store::where('id', $id)->first(['id', 'name']));
        }
        return back();
    }

    public function get_account_data(Store $store)
    {
        $wallet = $store->vendor->wallet;
        $cash_in_hand = 0;
        $balance = 0;

        if($wallet)
        {
            $cash_in_hand = $wallet->collected_cash;
            $balance = $wallet->total_earning - $wallet->total_withdrawn - $wallet->pending_withdraw - $wallet->collected_cash;
        }
        return response()->json(['cash_in_hand'=>$cash_in_hand, 'earning_balance'=>$balance], 200);

    }

    public function bulk_import_index()
    {
        return view('admin-views.vendor.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        $request->validate([
            'module_id'=>'required_if:stackfood,1',
            'products_file'=>'required|file'
        ]);
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }
        // $duplicate_phones = $collections->duplicates('phone');
        // $duplicate_emails = $collections->duplicates('email');

        // dd(['Phone'=>$duplicate_phones, 'Email'=>$duplicate_emails]);
        // if($duplicate_emails->isNotEmpty())
        // {
        //     Toastr::error(translate('messages.duplicate_data_on_column',['field'=>translate('messages.email')]));
        //     return back();
        // }

        // if($duplicate_phones->isNotEmpty())
        // {
        //     Toastr::error(translate('messages.duplicate_data_on_column',['field'=>translate('messages.phone')]));
        //     return back();
        // }

        $vendors = [];
        $stores = [];
        $vendor = Vendor::orderBy('id', 'desc')->first('id');
        $vendor_id = $vendor?$vendor->id:0;
        $store = Store::orderBy('id', 'desc')->first('id');
        $store_id = $store?$store->id:0;
        $store_ids = [];
        foreach ($collections as $key=>$collection) {
                if ($collection['ownerFirstName'] === "" || $collection['storeName'] === "" || $collection['phone'] === "" || $collection['email'] === "" || $collection['latitude'] === "" || $collection['longitude'] === "" || $collection['zone_id'] === "" || $collection['module_id'] === "") {
                    Toastr::error(translate('messages.please_fill_all_required_fields'));
                    return back();
                }


            array_push($vendors, [
                'id'=>$vendor_id+$key+1,
                'f_name' => $collection['ownerFirstName'],
                'l_name' => $collection['ownerLastName'],
                'password' => bcrypt(12345678),
                'phone' => $collection['phone'],
                'email' => $collection['email'],
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            array_push($stores, [
                'id'=>$store_id+$key+1,
                'name' => $request->stackfood?$collection['restaurantName']:$collection['storeName'],
                'logo' => $collection['logo'],
                'phone' => $collection['phone'],
                'email' => $collection['email'],
                'latitude' => $collection['latitude'],
                'longitude' => $collection['longitude'],
                'address' => $collection['address'],
                'radius' => $collection['radius'],
                'gm_commission' => $collection['gm_commission'],
                'status' => $collection['status'],
                'vendor_id' => $vendor_id+$key+1,
                'zone_id' => $collection['zone_id'],
                'delivery_time' => (isset($collection['delivery_time']) && preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $collection['delivery_time'])) ? $collection['delivery_time'] :'30-40 min',
                'module_id' => $request->stackfood?$request->module_id:$collection['module_id'],
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            if($module = Module::select('module_type')->where('id', $collection['module_id'])->first())
            {
                if(config('module.'.$module->module_type))
                {
                    $store_ids[] = $store_id+$key+1;
                }
            }

        }

        $data = array_map(function($id){
            return array_map(function($item)use($id){
                return     ['store_id'=>$id,'day'=>$item,'opening_time'=>'00:00:00','closing_time'=>'23:59:59'];
            },[0,1,2,3,4,5,6]);
        },$store_ids);

        try{
            DB::beginTransaction();
            DB::table('vendors')->insert($vendors);
            DB::table('stores')->insert($stores);
            DB::table('store_schedule')->insert(array_merge(...$data));
            DB::commit();
        }catch(\Exception $e)
        {
            // dd('stores :'.$stores.'\nvendors'.$vendors);
            DB::rollBack();
            info($e);
            Toastr::error(translate('messages.failed_to_import_data'));
            // Toastr::error($e);
            return back();
        }

        Toastr::success(translate('messages.store_imported_successfully',['count'=>count($stores)]));
        return back();
    }

    public function bulk_export_index()
    {
        return view('admin-views.vendor.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        $request->validate([
            'type'=>'required',
            'start_id'=>'required_if:type,id_wise',
            'end_id'=>'required_if:type,id_wise',
            'from_date'=>'required_if:type,date_wise',
            'to_date'=>'required_if:type,date_wise'
        ]);
        $vendors = Vendor::with('stores')
        ->when($request['type']=='date_wise', function($query)use($request){
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
        ->when($request['type']=='id_wise', function($query)use($request){
            $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
        })
        ->get();
        return (new FastExcel(StoreLogic::format_export_stores($vendors)))->download('Stores.xlsx');
    }

    public function add_schedule(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time',
            'store_id'=>'required',
        ],[
            'end_time.after'=>translate('messages.End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $temp = StoreSchedule::where('day', $request->day)->where('store_id',$request->store_id)
        ->where(function($q)use($request){
            return $q->where(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->start_time)->where('closing_time', '>=', $request->start_time);
            })->orWhere(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->end_time)->where('closing_time', '>=', $request->end_time);
            });
        })
        ->first();

        if(isset($temp))
        {
            return response()->json(['errors' => [
                ['code'=>'time', 'message'=>translate('messages.schedule_overlapping_warning')]
            ]]);
        }

        $store = Store::find($request->store_id);
        $store_schedule = StoreLogic::insert_schedule($request->store_id, [$request->day], $request->start_time, $request->end_time.':59');

        return response()->json([
            'view' => view('admin-views.vendor.view.partials._schedule', compact('store'))->render(),
        ]);
    }

    public function remove_schedule($store_schedule)
    {
        $schedule = StoreSchedule::find($store_schedule);
        if(!$schedule)
        {
            return response()->json([],404);
        }
        $store = $schedule->store;
        $schedule->delete();
        return response()->json([
            'view' => view('admin-views.vendor.view.partials._schedule', compact('store'))->render(),
        ]);
    }

    public function featured(Request $request)
    {
        $store = Store::findOrFail($request->store);
        $store->featured = $request->status;
        $store->save();
        Toastr::success(translate('messages.store_featured_status_updated'));
        return back();
    }
}
