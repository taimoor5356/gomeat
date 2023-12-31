<?php

namespace App\CentralLogics;

use App\Models\Item;
use App\Models\Review;

class ProductLogic
{
    public static function get_product($id)
    {
        return Item::active()
        ->when(config('module.current_module_data'), function($query){
            $query->module(config('module.current_module_data')['id']);
        })
        ->where('id', $id)->first();
    }

    public static function get_latest_products($limit, $offset, $store_id, $category_id, $type, $request = null)
    {
        $paginator = Item::with('store.state')->active()->type($type)
        ->when($category_id != 0, function($q)use($category_id){
            $q->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        // ->when(config('module.current_module_data'), function($query){
        //     $query->module(config('module.current_module_data')['id']);
        // })
        ->where('store_id', $store_id)->latest()->paginate($limit, ['*'], 'page', $offset);
        $items = $paginator->items();
        if (($request->header('country') == 'PK') && ($request->header('moduleId') == 2)) {
            foreach ($items as $key => $item) {
                if ($item->store) {
                    if ($item->store->filer_status == 'active') {
                        if (isset($item->store->state)) {
                            $items[$key]['sales_tax'] = $item->store->state->restaurant_online_payment;
                            $items[$key]['cod_tax'] = $item->store->state->restaurant_cash_payment;
                        } else {
                            $items[$key]['cod_tax'] = 0.00;
                        }
                    } else {
                        $items[$key]['cod_tax'] = 0.00;
                    }
                } else {
                    $items[$key]['cod_tax'] = 0.00;
                }
            }
        }
        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $items
        ];
    }

    public static function get_related_products($product_id)
    {
        $product = Item::find($product_id);
        return Item::active()
        ->when(config('module.current_module_data'), function($query){
            $query->module(config('module.current_module_data')['id']);
        })
        ->whereHas('store', function($query){
            $query->Weekday();
        })
        ->where('category_ids', $product->category_ids)
        ->where('id', '!=', $product->id)
        ->limit(10)
        ->get();
    }
    
    public static function popular_products($zone_id, $limit = null, $offset = null, $type = 'all')
    {
        if($limit != null && $offset != null)
        {
            $paginator = Item::
            when(config('module.current_module_data'), function($query)use($zone_id){
                $query->module(config('module.current_module_data')['id']);
                if(!config('module.current_module_data')['all_zone_service']) {
                    $query->whereHas('store', function($q)use($zone_id){
                        $q->whereIn('zone_id', $zone_id)->Weekday();
                    });
                }
            })
            ->active()->type($type)->popular()->paginate($limit, ['*'], 'page', $offset);

            return [
                'total_size' => $paginator->total(),
                'limit' => $limit,
                'offset' => $offset,
                'products' => $paginator->items()
            ];
        }
        $paginator = Item::active()
        ->when(config('module.current_module_data'), function($query)use($zone_id){
            $query->module(config('module.current_module_data')['id']);
            if(!config('module.current_module_data')['all_zone_service']) {
                $query->whereHas('store', function($q)use($zone_id){
                    $q->whereIn('zone_id', json_decode($zone_id, true))->Weekday();
                });
            }
        })
        ->type($type)->popular()->limit(50)->get();

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator
        ];
        
    }

    public static function most_reviewed_products($zone_id, $limit = null, $offset = null, $type = 'all', $request = null)
    {
        // if($limit != null && $offset != null)
        // {
            $paginator = Item::
            whereHas('store', function($q)use($zone_id){
                $q->when(config('module.current_module_data'), function($query)use($zone_id){
                    $query->module(config('module.current_module_data')['id']);
                    if(!config('module.current_module_data')['all_zone_service']) {
                        $query->whereIn('zone_id', json_decode($zone_id, true));
                    }
                })
                ->Weekday();
            })
            ->withCount('reviews')->active()->type($type)
            ->orderBy('reviews_count','desc')
            ->paginate($limit, ['*'], 'page', $offset);
            $items = $paginator->items();
            if (($request->header('country') == 'PK') && ($request->header('moduleId') == 2)) {
                foreach ($items as $key => $item) {
                    if ($item->store) {
                        if ($item->store->filer_status == 'active') {
                            if (isset($item->store->state)) {
                                $items[$key]['sales_tax'] = $item->store->state->restaurant_online_payment;
                                $items[$key]['cod_tax'] = $item->store->state->restaurant_cash_payment;
                            } else {
                                $items[$key]['cod_tax'] = 0.00;
                            }
                        } else {
                            $items[$key]['cod_tax'] = 0.00;
                        }
                    } else {
                        $items[$key]['cod_tax'] = 0.00;
                    }
                }
            }
            return [
                'total_size' => $paginator->total(),
                'limit' => $limit,
                'offset' => $offset,
                'products' => $items
            ];
        // }
        $paginator = Item::active()->type($type)
        ->whereHas('store', function($q)use($zone_id){
            $q->when(config('module.current_module_data'), function($query)use($zone_id){
                $query->module(config('module.current_module_data')['id']);
                if(!config('module.current_module_data')['all_zone_service']) {
                    $query->whereIn('zone_id', json_decode($zone_id, true));
                }
            })
            ->Weekday();
        })
        ->withCount('reviews')
        ->orderBy('reviews_count','desc')
        ->limit(50)->get();

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator
        ];
        
    }

    public static function get_product_review($id)
    {
        $reviews = Review::where('product_id', $id)->get();
        return $reviews;
    }

    public static function get_rating($reviews)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_avg_rating($rating)
    {
        $total_rating = 0;
        $total_rating += $rating[1];
        $total_rating += $rating[2]*2;
        $total_rating += $rating[3]*3;
        $total_rating += $rating[4]*4;
        $total_rating += $rating[5]*5;

        return $total_rating/array_sum($rating);
    }

    public static function get_overall_rating($reviews)
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }

    public static function format_export_items_vendor($foods)
    {
        $storage = [];
        // $sum = 0;

        foreach($foods as $item)
        {
            $category_id = 0;
            $sub_category_id = 0;
            foreach(json_decode($item->category_ids, true) as $category)
            {
                if($category['position']==1)
                {
                    $category_id = $category['id'];
                }
                else if($category['position']==2)
                {
                    $sub_category_id = $category['id'];
                }
            }
            
            $storage[] = [
                'id'=>$item->id,
                'name'=>$item->name,
                'description'=>$item->description,
                // 'image'=>$item->image,
                // 'veg'=>$item->veg,
                // 'category_id'=>$category_id,
                // 'sub_category_id'=>$sub_category_id,
                // 'unit_id'=>$item->unit_id,
                // 'stock'=>$item->stock,
                // 'price'=>$item->price,
                // 'discount'=>$item->discount,
                // 'discount_type'=>$item->discount_type,
                // 'available_time_starts'=>$item->available_time_starts,
                // 'available_time_ends'=>$item->available_time_ends,
                // 'variations'=>str_replace(['{','}','[',']'],['(',')','',''],$item->variations),
                // 'add_ons'=>str_replace(['"','[',']'],'',$item->add_ons),
                // 'attributes'=>str_replace(['"','[',']'],'',$item->attributes),
                // 'choice_options'=>str_replace(['{','}'],['(',')'],substr($item->choice_options, 1, -1)),
                // 'store_id'=>$item->store_id,
                // 'module_id'=>$item->module_id,
                'order_count'=>$item->order_count,
                'order_ids'=>isset($item->order_ids)?$item->order_ids:'no order',
                'total_quantity_sold'=>isset($item->quantity_sold)?$item->quantity_sold:0,

            ];
            
            
            
            // $sum = $order_details_count+$sum;

        }
        // $storage['sum'] = $sum;

        return $storage;
    }
    
    public static function format_export_items($foods)
    {
        $storage = [];
        foreach($foods as $item)
        {
            $category_id = 0;
            $sub_category_id = 0;
            foreach(json_decode($item->category_ids, true) as $category)
            {
                if($category['position']==1)
                {
                    $category_id = $category['id'];
                }
                else if($category['position']==2)
                {
                    $sub_category_id = $category['id'];
                }
            }
            $storage[] = [
                'id'=>$item->id,
                'name'=>$item->name,
                'description'=>$item->description,
                'image'=>$item->image,
                'veg'=>$item->veg,
                'category_id'=>$category_id,
                'sub_category_id'=>$sub_category_id,
                'unit_id'=>$item->unit_id,
                'stock'=>$item->stock,
                'price'=>$item->price,
                'discount'=>$item->discount,
                'discount_type'=>$item->discount_type,
                'available_time_starts'=>$item->available_time_starts,
                'available_time_ends'=>$item->available_time_ends,
                'variations'=>str_replace(['{','}','[',']'],['(',')','',''],$item->variations),
                'add_ons'=>str_replace(['"','[',']'],'',$item->add_ons),
                'attributes'=>str_replace(['"','[',']'],'',$item->attributes),
                'choice_options'=>str_replace(['{','}'],['(',')'],substr($item->choice_options, 1, -1)),
                'store_id'=>$item->store_id,
                'module_id'=>$item->module_id,
                'order_count'=>$item->order_count,
            ];
        }

        return $storage;
    }

    public static function update_food_ratings()
    {
        try{
            $foods = Item::withOutGlobalScopes()->whereHas('reviews')->with('reviews')->get();
            foreach($foods as $key=>$food)
            {
                $foods[$key]->avg_rating = $food->reviews->avg('rating');
                $foods[$key]->rating_count = $food->reviews->count();
                foreach($food->reviews as $review)
                {
                    $foods[$key]->rating = self::update_rating($foods[$key]->rating, $review->rating);
                }
                $foods[$key]->save();
            }
        }catch(\Exception $e){
            info($e);
            return false;
        }
        return true;
    }

    public static function update_rating($ratings, $product_rating)
    {

        $store_ratings = [1=>0 , 2=>0, 3=>0, 4=>0, 5=>0];
        if(isset($ratings))
        {
            $store_ratings = json_decode($ratings, true);
            $store_ratings[$product_rating] = $store_ratings[$product_rating] + 1; 
        }
        else
        {
            $store_ratings[$product_rating] = 1;
        }
        return json_encode($store_ratings);
    }

    public static function update_stock($item, $quantity, $variant=null)
    { 
        if(isset($variant))
        {
            $variations = is_array($item['variations'])?$item['variations']: json_decode($item['variations'], true);
            
            foreach ($variations as $key => $value) {
                if ($value['type'] == $variant) {
                    $variations[$key]['stock'] -= $quantity;
                }
            }
            $item['variations']= json_encode($variations);
        }
        $item->stock -= $quantity;
        return $item;
    }
}
