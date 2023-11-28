<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\StoreCatMap;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function get_categories()
    {
        try {
            $categories = Category::where(['position'=>0,'status'=>1])
            ->when(config('module.current_module_data'), function($query){
                $query->module(config('module.current_module_data')['id']);
            })
            ->orderBy('priority','desc')->get();
            return response()->json([], 200);
            // return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_childes($id, Request $request)
    {
        // $zone_id= $request->header('zoneId');
        // $temp = json_decode($zone_id,true);

        // return $temp[0];
        // $module_id= $request->header('moduleId');

        // return $module_id;

        // $type = $request->query('type', 'all');

        // $lat = $request->header('lat');
        // $long = $request->header('long');

        try {

            $categories = Category::where(['parent_id' => $id,'status'=>1])->orderBy('priority','desc')->get();
            // $categories = Category::where(['parent_id' => $id,'status'=>1])->orderBy('desc')->get();
            
            // $cat = array();
            // $category_check = [];
            // foreach($categories as $category)
            // {
                // $store_cat_maps = StoreCatMap::where(['parent_id' => $id, 'category_id'=>$category->id,'module_id'=>$module_id])->first();

                // return $category;
                // if($store_cat_maps)
                // {

                // }
                // $item=Item::
                //     where('category_id',$category->id)
                //     ->where('module_id',$module_id)
                //     ->first();

                // return $item;

                // $store = Store::where('id',$item->store_id)
                // ->where('zone_id',$temp[0])
                // ->where('module_id',$module_id)
                // ->first();

                // $item = Item::
                // where('store_id',$store_cat_map->store_id)
                // ->where('category_id',$store_cat_map->category_id)
                // ->where('module_id',$module_id)
                // ->first();

                // if($item)
                // {

                    
                    

                    // $theta = $long - $store->longitude;
                    // $miles = (sin(deg2rad($lat))) * sin(deg2rad($store->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($store->latitude)) * cos(deg2rad($theta)));
                    // $miles = acos($miles);
                    // $miles = rad2deg($miles);
                    // $radius = $miles * 60 * 1.1515;
                    
                    // return $radius;
                    // if($radius <= $store->radius)
                    // {
                        // if (!isset($category_check[$store_cat_map->category_id]))
                        // {

                            // $data[] = $str;
                            // $category_check[$store_cat_map->category_id] = 'yes';
                            // $data[]=$store;

                            // $category_data = Category::where('id',$store_cat_map->category_id)->first();
                            // array_push($cat,$category_data);
                            // array_push($cat,$category);
                        // }

                    // }
                // }
                
                // return $item;
                // if($item)
                // {
                //     array_push($cat,$category);
                // }

                return response()->json(Helpers::category_data_formatting($categories, true), 200);
            // }


        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_products($id, Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');
        $temp = json_decode($zone_id,true);

        // return $temp[0];
        $module_id= $request->header('moduleId');

        $type = $request->query('type', 'all');

        $lat = $request->header('lat');
        $long = $request->header('long');

        // $data = CategoryLogic::products($id, $zone_id, $request['limit'], $request['offset'], $type);

        $category = Category::where('id',$id)->first();
        $prdoucts_data= array();

        $i=0;
        if($category->position==0)
        {
            // return
            $child_categories = Category::
            where('parent_id',$id)->get();

            foreach($child_categories as $child_cat)
            {
                $products = Item::
                where('module_id',$module_id)
                ->where('category_id',$child_cat->id)
                ->get();

                foreach($products as $product)
                {
                    $store = Store::
                    where('id',$product->store_id)
                    ->where('module_id',$module_id)
                    ->where('zone_id',$temp[0])
                    ->where('status',1)
                    ->first();

                    if($store)
                    {
                        // return $store;

                        $theta = $long - $store->longitude;
                        $miles = (sin(deg2rad($lat))) * sin(deg2rad($store->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($store->latitude)) * cos(deg2rad($theta)));
                        $miles = acos($miles);
                        $miles = rad2deg($miles);
                        $radius = $miles * 60 * 1.1515;

                        // return $product;
                        
                        if($store->radius && $radius <= $store->radius && $i<=$request['limit'])
                        {
                            array_push($prdoucts_data,$product);
                            $i++;
            
                        }
                    }
                }
                if($i>$request['limit'])
                {
                    break;
                }
            }
            // return $prdoucts_data;
        }
        else
        {
            $products = Item::
            where('module_id',$module_id)
            ->where('category_id',$id)
            ->get();

            foreach($products as $product)
            {
                $store = Store::
                where('id',$product->store_id)
                ->where('module_id',$module_id)
                ->where('zone_id',$temp[0])
                ->where('status',1)
                ->first();

                if($store)
                {
                    // return $store;

                    $theta = $long - $store->longitude;
                    $miles = (sin(deg2rad($lat))) * sin(deg2rad($store->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($store->latitude)) * cos(deg2rad($theta)));
                    $miles = acos($miles);
                    $miles = rad2deg($miles);
                    $radius = $miles * 60 * 1.1515;

                    // return $product;
                    
                    if($store->radius && $radius <= $store->radius)
                    {
                        // return $product;
                        // $data[] = $str;
                        // $store_check[$storeCatMap->store_id] = 'yes';
                        array_push($prdoucts_data,$product);
                        // $product_data[]=$product;
        
                    }
                }
            }
        }

        // return 'w';

        // return $prdoucts_data;

        $products = Helpers::product_data_formatting($prdoucts_data , true, false, app()->getLocale());

        $data = [
            'total_size' => sizeof($products),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $products
        ];

        
        return response()->json($data, 200);
    }


    public function get_stores($id, Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');

        $type = $request->query('type', 'all');

        $module_id = $request->header('moduleId');

        $lat = $request->header('lat');
        $long = $request->header('long');

        // $categories = Category::where('parent_id',$id)->get();

        $storeCatMaps = StoreCatMap::
        select('store_id')
        ->where('parent_id',$id)
        ->where('module_id',$module_id)
        // ->groupBy('store_id')
        ->distinct()
        ->get();
        // return $storeCatMaps;
        // // return 'working';
        $temp = json_decode($zone_id,true); 
        $store_check = [];

        $data = array();
        foreach($storeCatMaps as $storeCatMap)
        {
            // return $temp[0];

            if (!isset($store_check[$storeCatMap->store_id])) 
            {
                $store = Store::
                withOpen()
                ->where('id',$storeCatMap->store_id)
                ->where('zone_id',$temp[0])
                ->where('module_id',$module_id)
                // ->where('status',1)
                ->active()
                ->type($type)
                ->latest()
                ->first();

                // return $store;
                if($store)
                {
                    $theta = $long - $store->longitude;
                    $miles = (sin(deg2rad($lat))) * sin(deg2rad($store->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($store->latitude)) * cos(deg2rad($theta)));
                    $miles = acos($miles);
                    $miles = rad2deg($miles);
                    $radius = $miles * 60 * 1.1515;
                    
                    // return $radius;
                    if($radius <= $store->radius)
                    {
                        // $data[] = $str;
                        $store_check[$storeCatMap->store_id] = 'yes';
                        // $data[]=$store;
                        array_push($data,$store);

                    }
                }
            }


            // return $data;
            // isset($array1[$object->id]);

            

            // $stores = Store::
            // where('id',$storeCatMap->store_id)
            // ->first();



            // return $category;
            // $store_cat_map = DB::table('store_cat_map')
            // ->where('category_id', $category->id)
            // ->get();

            // return $store_cat_map;
        }

        // return $data;

        // $data = CategoryLogic::stores($id,$module_id, $zone_id, $request['limit'], $request['offset'], $type);
        $final_data['stores'] = Helpers::store_data_formatting($data , true);
        return response()->json($final_data, 200);
    }
    



    public function get_all_products($id,Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');

        try {
            return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id, $zone_id), true, false, app()->getLocale()), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
