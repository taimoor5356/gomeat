<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\StoreLogic;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Category;
use App\Models\StoreCatMap;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessSetting;
use Illuminate\Support\Arr;

class StoreController extends Controller
{
    public function get_stores(Request $request, $filter_data="all")
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $zone_id = $request->header('zoneId');
        $module_id =  $request->header('moduleId');
        $temp = json_decode($zone_id); 
        $lat = $request->header('lat');
        $long = $request->header('long');
        // $stores = StoreLogic::get_stores( $zone_id, $filter_data, $type, $request['limit'], $request['offset'], $request->query('featured'));
        $takeaway = 0;
        $delivery = 0;
        $featured = 0;


        if($filter_data=='take_away')
        {
            $store_data = Store::
            withOpen()
            ->Active()
            ->where('zone_id',$temp)
            ->where('take_away',1)
            // ->where('status',1)
            ->where('module_id',$module_id)->get();
        }
        else if($filter_data=='delivery')
        {
            $store_data = Store::
            withOpen()
            ->Active()
            ->where('zone_id',$temp)
            ->where('delivery',1)
            // ->where('status',1)
            ->where('module_id',$module_id)->get();
        }
        else if($request['featured'])
        {
            $store_data = Store::
            withOpen()
            ->Active()
            ->where('zone_id',$temp)
            ->where('featured',1)
            // ->where('status',1)
            ->get();
        }
        else
        {
            // $takeaway = 1;
            // $delivery = 1;   

            $store_data = Store::
            withOpen()
            ->Active()
            ->where('zone_id',$temp)
            // ->where('status',1)
            ->where('module_id',$module_id)->get();
        }

        $stores = [
            'total_size' => 0,
            // 'limit' => $request['limit'],
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'stores' => $store_data//->items()
        ];

        // return $stores;


        $data=[];
        $i=0;
        if($stores['stores'])
        {
            foreach($stores['stores'] as $str)
            {
                $theta = $long - $str->longitude;
                $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
                $miles = acos($miles);
                $miles = rad2deg($miles);
                $radius = $miles * 60 * 1.1515;
                // $radius = $radius * 0.8684;

                // return $radius;
                
                if($radius <= $str->radius)
                {
                    if($i<=$request['limit'])
                    {
                        $data[] = $str;
                        $i++;
                    }
                }

            }
        }

        // return $i;
        $stores['total_size']=count($data);
        $stores['stores'] = Helpers::store_data_formatting($data, true);
        // $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        return response()->json($stores, 200);
    }

    // public function calculateDistance(Request $request)
    public function calculateDistance($lat1,$long1,$lat2,$long2)
    {
        
        // dd($request->lat1);
        
        $theta = $long1 - $long2;
        // dd($theta);
        $miles = (sin(deg2rad($lat1))) * sin(deg2rad($lat2)) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        
        $miles = acos($miles);
        $miles = rad2deg($miles);
        
        $result['miles'] = $miles * 60 * 1.1515;
        // dd($result['miles']);
        $result['feet'] = $result['miles']*5280;
        $result['yards'] = $result['feet']/3;
        $result['kilometers'] = $result['miles']*1.609344;
        $result['meters'] = $result['kilometers']*1000;
        // return $result;
        return $miles * 60 * 1.1515;
    }

    public function get_latest_stores(Request $request, $filter_data="all")
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');

        $zone_id= $request->header('zoneId');
        $lat = $request->header('lat');
        $long = $request->header('long');
        // $stores = StoreLogic::get_latest_stores($zone_id, $request['limit'], $request['offset'], $type);
        $data=[];
        // if($stores['stores'])
        // {
        //     foreach($stores['stores'] as $str)
        //     {
        //         $theta = $long - $str->longitude;
        //         $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
        //         $miles = acos($miles);
        //         $miles = rad2deg($miles);
        //         $radius = $miles * 60 * 1.1515;
                
        //         if($radius <= $str->radius)
        //         {
        //             $data[] = $str;
        //         }
        //     }
        // }

        // $stores['total_size']=count($data);
        // $stores['stores'] = Helpers::store_data_formatting($data, true);
        $stores['total_size']=0;
        $stores['stores'] = [];
        // $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        // return response()->json($stores['stores'], 200);
        return response()->json($stores, 200);
    }

    public function get_popular_stores(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $module_id =  $request->header('moduleId');
        $zone = $request->header('zoneId');
        $zone_id = json_decode($zone); 
        $lat = $request->header('lat');
        $long = $request->header('long');

        
        
        // $stores = StoreLogic::get_popular_stores($zone_id, $request['limit'], $request['offset'], $type);

        $store_data = Store::
            withOpen()
            ->Active()
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->where('zone_id',$zone_id)
            // ->where('status',1)
            // ->where('total_order','>',0)
            ->where('module_id',$module_id)
            ->get();

        
        // return $store_data;

        $stores = [
            'total_size' => 0,
            'limit' => 10,
            'offset' => $request['offset'],
            'stores' => $store_data
        ];

        $data=[];

        $i=0;
        if($stores['stores'])
        {
            foreach($stores['stores'] as $str)
            {
                $theta = $long - $str->longitude;
                $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
                $miles = acos($miles);
                $miles = rad2deg($miles);
                $radius = $miles * 60 * 1.1515;
                
                if($radius <= $str->radius)
                {
                    if($i<=10)
                    {
                        $data[] = $str;
                        $i++;
                    }
                }
            }
        }

        $stores['total_size']=count($data);
        $stores['stores'] = Helpers::store_data_formatting($data, true);
        return response()->json($stores, 200);

        // $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        // return response()->json($stores['stores'], 200);
    }

    //TBD make separate table for categories and store mapping
    //TBD make separate table for sub-categories and store mapping
    public function get_details(Request $request, $id)
    {
        $store = 
        // Store::
        // withOpen()
        // ->Active()
        // ->where('id',$id)->first();
        StoreLogic::get_store_details($id);

        if($store)
        {
            $category_ids = Category::where('position',0)
            ->where('status',1)->get();
            // StoreCatMap::where('store_id',$id)->get();
            // DB::table('items')
            // ->join('categories', 'items.category_id', '=', 'categories.id')
            // ->selectRaw('IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
            // ->where('items.store_id', $id)
            // ->where('categories.status',1)
            // ->groupBy('categories')
            // ->get();
            // dd($category_ids->pluck('categories'));
            // $store = Helpers::store_data_formatting($store);
            $store['category_ids'] = array_map('intval', $category_ids->pluck('id')->toArray());
            $store['categories_list'] = $category_ids->toArray();
            $taxAmount = BusinessSetting::where('key', 'service_fee')->first()->value;
            if ($request->header('country') == 'PK') {
                $taxAmount = 5;
            }
            $store['tax'] = $taxAmount;
            $final='';
            
            // return $store['categories_list'];
            // return $store;
                
            for($count = 0; $count<sizeof($store['category_ids']); $count++)
            {
                
                // $subcat = Category::where(['parent_id' => $store['category_ids'][$count],'status'=>1])->orderBy('priority','desc')->get();
                $subcats = StoreCatMap::
                where('parent_id' , $store['category_ids'][$count])
                ->where('status',1)
                ->where('store_id',$store['id'])
                // ->orderBy('name', 'DESC')
                ->get();
                
                // return $subcats;

                // $subcatexist = Item::where('category_id', 27)
                //     ->where('store_id', $id)->where('status',1)->first();
                //     dd(!$subcatexist);

                if($subcats)
                {

                    $finalsubcat=array();
                    foreach($subcats as $subcat)
                    {
                        // $subcatexist = StoreCatMap::where('category_id', $subcat[$i]->id)
                        // ->where('store_id', $id)->first();
    
                        // if($subcatexist && !is_null($subcatexist))
                        // {
                            $subcat_data = Category::where('id',$subcat->category_id)->first();
                            array_push($finalsubcat,$subcat_data);
                         
                        // }
                        
                    }
                    // $finalsubcat = Arr::sort($finalsubcat);
                    $data[$store['category_ids'][$count]] = Helpers::category_data_formatting($finalsubcat, true);
                    
                    $final=$data;
                }
                // var_dump($subcat);
                // $subcat=array_values($subcat);
                // dd($finalsubcat);
            }

            
                
          
            
            $store['subcategories']=$final;
        }
        return response()->json($store, 200);
    }

    public function get_searched_stores(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        
        $type = $request->query('type', 'all');

        $zone_id= $request->header('zoneId');
        $lat = $request->header('lat');
        $long = $request->header('long');
        $stores = StoreLogic::search_stores($request['name'], $zone_id, $request->category_id,$request['limit'], $request['offset'], $type);


        $data=[];
        if($stores['stores'])
        {
            foreach($stores['stores'] as $str)
            {
                $theta = $long - $str->longitude;
                $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
                $miles = acos($miles);
                $miles = rad2deg($miles);
                $radius = $miles * 60 * 1.1515;
                
                if($radius <= $str->radius)
                {
                    $data[] = $str;
                }
            }
        }
        $stores['total_size']=count($data);
            $stores['stores'] = Helpers::store_data_formatting($data, true);
            return response()->json($stores, 200);

        // $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);
        // return response()->json($stores, 200);
    }

    public function reviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $id = $request['store_id'];


        $reviews = Review::with(['customer', 'item'])
        ->whereHas('item', function($query)use($id){
            return $query->where('store_id', $id);
        })
        ->active()->latest()->get();

        $storage = [];
        foreach ($reviews as $temp) {
            $temp['attachment'] = json_decode($temp['attachment']);
            $temp['item_name'] = null;
            $temp['item_image'] = null;
            $temp['customer_name'] = null;
            if($temp->item)
            {
                $temp['item_name'] = $temp->item->name;
                $temp['item_image'] = $temp->item->image;
                if(count($temp->item->translations)>0)
                {
                    $translate = array_column($temp->item->translations->toArray(), 'value', 'key');
                    $temp['item_name'] = $translate['name'];
                }
            }
            if($temp->customer)
            {
                $temp['customer_name'] = $temp->customer->f_name.' '.$temp->customer->l_name;
            }
            
            unset($temp['item']);
            unset($temp['customer']);
            array_push($storage, $temp);
        }

        return response()->json($storage, 200);
    }
}
