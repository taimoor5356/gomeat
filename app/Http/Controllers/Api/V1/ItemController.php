<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\StoreLogic;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\Store;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function get_latest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'category_id' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request->query('type', 'all'); 

        $items = ProductLogic::get_latest_products($request['limit'], $request['offset'], $request['store_id'], $request['category_id'], $type, $request);
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_searched_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zone_id = $request->header('zoneId');

        $key = explode(' ', $request['name']);
        
        $limit = $request['limit']??10;
        $offset = $request['offset']??1;

        $type = $request->query('type', 'all'); 

        $items = Item::active()->type($type)
        
        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->store_id, function($query) use($request){
            return $query->where('store_id', $request->store_id);
        })
        ->when(config('module.current_module_data'), function($query)use($zone_id){
            $query->module(config('module.current_module_data')['id']);
            if(!config('module.current_module_data')['all_zone_service']) {
                $query->whereHas('store', function($q)use($zone_id){
                    $q->whereIn('zone_id', json_decode($zone_id, true));
                });
            }
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('translations',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('value', 'like', "%{$value}%");
                    };
                });
            });
        })

        ->paginate($limit, ['*'], 'page', $offset);
        $itm = $items->items();
        $lat = $request->header('lat');
        $long = $request->header('long');
        $itemData=[];
        foreach($items->items() as $itm)
        {
            $str=Store::where('id',$itm->store_id)->first();
            
            $theta = $long - $str->longitude;
            $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
            $miles = acos($miles);
            $miles = rad2deg($miles);
            $radius = $miles * 60 * 1.1515;
            
            if($radius <= $str->radius)
            {
                $itemData[] = $itm;
            }
        }

        $data =  [
            'total_size' => count($itemData),//$items->total(),
            'limit' => $limit,
            'offset' => $offset,
            // 'products' => $items->items()
            'products' => $itemData
        ];

        $data['products'] = Helpers::product_data_formatting($data['products'], true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_popular_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');

        $zone= $request->header('zoneId');
        $zone_id = json_decode($zone);
        $module_id = $request->header('moduleId');
        // $items = ProductLogic::popular_products($zone, $request['limit'], $request['offset'], $type);

        // $item_data = Item::
        // where('module_id',$module_id)
        // ->popular()

        // $str=Store::
        // where('id',8)
        // ->where('module_id',2)
        // ->first();

        // return $str;
        
        $lat = $request->header('lat');
        $long = $request->header('long');
        $itemData=[];
        $i=0;
        // foreach($items['products'] as $itm)
        // {
        //     $str=Store::
        //     where('id',$itm->store_id)
        //     ->where('module_id',$module_id)
        //     ->first();
            
        //     // return $str;
        //     if($str)
        //     {
        //         $theta = $long - $str->longitude;
        //         $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
        //         $miles = acos($miles);
        //         $miles = rad2deg($miles);
        //         $radius = $miles * 60 * 1.1515;
                
        //         if($radius <= $str->radius && $i<=20)
        //         {
        //             $itemData[] = $itm;
        //             $i++;
        //         }
        //     }
        // }
        
        
        // $items['products'] = $itemData;
        // $items['total_size'] = count($items['products']);
        $items['products'] = 0;
        $items['total_size'] = 0;
        // $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        $items['products'] = [];
        return response()->json($items, 200);
    }    
    
    public function get_most_reviewed_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');

        $module_id = $request->header('moduleId');
        $zone_id= $request->header('zoneId');
        $items = ProductLogic::most_reviewed_products($zone_id, $request['limit'], $request['offset'], $type, $request);
        $lat = $request->header('lat');
        $long = $request->header('long');
        $itemData=[];
        foreach($items['products'] as $itm)
        {
            $str=Store::
            where('id',$itm->store_id)
            ->where('module_id',$module_id)
            ->first();
            
            // return $str;
            if($str)
            {
                $theta = $long - $str->longitude;
                $miles = (sin(deg2rad($lat))) * sin(deg2rad($str->latitude)) + (cos(deg2rad($lat)) * cos(deg2rad($str->latitude)) * cos(deg2rad($theta)));
                $miles = acos($miles);
                $miles = rad2deg($miles);
                $radius = $miles * 60 * 1.1515;
                
                if($radius <= $str->radius)
                {
                    $itemData[] = $itm;
                }
            }
        }
        
        
        $items['products'] = $itemData;
        $items['total_size'] = count($items['products']);
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_product($id)
    {
        
        try {
            $item = ProductLogic::get_product($id);
            $item = Helpers::product_data_formatting($item, false, false, app()->getLocale());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
            ], 404);
        }
    }

    public function get_cart_items(Request $request)
    {

        // dd($request->item_ids);

        foreach($request->item_ids as $item_id)
        {
            $item = Item::where('id',$item_id)->first();
            if($item)
            {
                $data[]=$item;
            }
        }
        return response()->json($data, 200);
        // return $data;
        
        // try {
        //     $item = ProductLogic::get_product($id);
        //     $item = Helpers::product_data_formatting($item, false, false, app()->getLocale());
        //     return response()->json($item, 200);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        //     ], 404);
        // }
    }

    public function get_related_products($id)
    {
        if (Item::find($id)) {
            $items = ProductLogic::get_related_products($id);
            $items = Helpers::product_data_formatting($items, true, false, app()->getLocale());
            return response()->json($items, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }

    public function get_set_menus()
    {
        try {
            $items = Helpers::product_data_formatting(Item::active()->with(['rating'])->where(['set_menu' => 1, 'status' => 1])->get(), true, false, app()->getLocale());
            return response()->json($items, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Set menu not found!']
            ], 404);
        }
    }

    public function get_product_reviews($item_id)
    {
        $reviews = Review::with(['customer', 'item'])->where(['item_id' => $item_id])->active()->get();

        $storage = [];
        foreach ($reviews as $temp) {
            $temp['attachment'] = json_decode($temp['attachment']);
            $temp['item_name'] = null;
            if($temp->item)
            {
                $temp['item_name'] = $temp->item->name;
                if(count($temp->item->translations)>0)
                {
                    $translate = array_column($temp->item->translations->toArray(), 'value', 'key');
                    $temp['item_name'] = $translate['name'];
                }
            }
            
            unset($temp['item']);
            array_push($storage, $temp);
        }

        return response()->json($storage, 200);
    }

    public function get_product_rating($id)
    {
        try {
            $item = Item::find($id);
            $overallRating = ProductLogic::get_overall_rating($item->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required|numeric|max:5',
        ]);

        $order = Order::find($request->order_id);
        if (isset($order) == false) {
            $validator->errors()->add('order_id', translate('messages.order_data_not_found'));
        }

        $item = Item::find($request->item_id);
        if (isset($order) == false) {
            $validator->errors()->add('item_id', translate('messages.item_not_found'));
        }

        $multi_review = Review::where(['item_id' => $request->item_id, 'user_id' => $request->user()->id, 'order_id'=>$request->order_id])->first();
        if (isset($multi_review)) {
            return response()->json([
                'errors' => [ 
                    ['code'=>'review','message'=> translate('messages.already_submitted')]
                ]
            ], 403);
        } else {
            $review = new Review;
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image_array = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                if ($image != null) {
                    if (!Storage::disk('public')->exists('review')) {
                        Storage::disk('public')->makeDirectory('review');
                    }
                    array_push($image_array, Storage::disk('public')->put('review', $image));
                }
            }
        }

        $review->user_id = $request->user()->id;
        $review->item_id = $request->item_id;
        $review->order_id = $request->order_id;
        $review->module_id = $order->module_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();

        if($item->store)
        {
            $store_rating = StoreLogic::update_store_rating($item->store->rating, (int)$request->rating);
            $item->store->rating = $store_rating;
            $item->store->save();
        }

        $item->rating = ProductLogic::update_rating($item->rating, (int)$request->rating);
        $item->avg_rating = ProductLogic::get_avg_rating(json_decode($item->rating, true));
        $item->save();
        $item->increment('rating_count');

        return response()->json(['message' => translate('messages.review_submited_successfully')], 200);
    }
}
