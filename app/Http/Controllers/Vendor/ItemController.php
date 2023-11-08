<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\StoreCatMap;
use App\Models\Item;
use App\Models\Attribute;
use App\Models\Review;
use App\Models\OrderDetail;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;
use App\Models\Translation;

class ItemController extends Controller
{
    public function index()
    {
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $categories = Category::where(['position' => 0])->module(Helpers::get_store_data()->module_id)->get();
        $module_data = config('module.'. Helpers::get_store_data()->module->module_type);
        return view('vendor-views.product.index', compact('categories','module_data'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if(!Helpers::get_store_data()->item_section)
        {
            return response()->json([
                    'errors'=>[
                        ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                    ]
                ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'image' => 'required',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'description.*' => 'max:1000',
            
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'description.*.max' => translate('messages.description_length_warning'),   
        ]);

        // if ($request['discount_type'] == 'percent') {
        //     $dis = ($request['price'] / 100) * $request['discount'];
        // } else {
        //     $dis = $request['discount'];
        // }

        // if ($request['price'] <= $dis) {
        //     $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        // }

        // if ($request['price'] <= $dis || $validator->fails()) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)]);
        // }

        $food = new Item;
        $food->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $food->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $food->category_ids = json_encode($category);
        $food->description = $request->description[array_search('en', $request->lang)];

        $attributes = [];
        $choice_options = [];


        if($request->has('attribute_id'))
        {

            foreach($request['attribute_id'] as $attribute_id)
            {
                array_push($attributes, $attribute_id);
            }
            // dd($attributes);
    
            $attr_count = 0;
    
    
            foreach($request['options'] as $choice_option)
            {
                // dd($choice_option);
                $options=[];
                $variant['name'] = 'choice_'.$attributes[$attr_count];
                $attr = Attribute::where('id',$attributes[$attr_count])->first();
                $variant['title'] = $attr->name;
                $variant['multiselect'] = $choice_option['type']=='single'?0:1;
    
                // dd($variant);
                // $variant['options'] = [];
                foreach($choice_option['values'] as $opt)
                {
                    // $options['stock'] = intval($options['stock']);
                    $option['type']=$opt['type'];
                    $option['price']=$opt['price'];
                    $option['stock']=100000;
                    array_push($options, $option);
                }
                $variant['options']=$options;
                array_push($choice_options, $variant);
                // $choice_options = $variant;
                // dd($choice_options);
                $attr_count++;
            }
            
        }

        // dd($choice_options);
        $food->choice_options = $request->has('attribute_id') ? json_encode($choice_options) : json_encode([]);


        // dd($food);
        // $choice_options = [];
        // if ($request->has('choice')) {
        //     foreach ($request->choice_no as $key => $no) {
        //         $str = 'choice_options_' . $no;
        //         if ($request[$str][0] == null) {
        //             $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
        //             return response()->json(['errors' => Helpers::error_processor($validator)]);
        //         }
        //         $item['name'] = 'choice_' . $no;
        //         $item['title'] = $request->choice[$key];
        //         $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
        //         array_push($choice_options, $item);
        //     }
        // }
        // $food->choice_options = json_encode($choice_options);
        // $variations = [];
        // $options = [];
        // if ($request->has('choice_no')) {
        //     foreach ($request->choice_no as $key => $no) {
        //         $name = 'choice_options_' . $no;
        //         $my_str = implode('|', $request[$name]);
        //         array_push($options, explode(',', $my_str));
        //     }
        // }
        // //Generates the combinations of customer choice options
        // $combinations = Helpers::combinations($options);
        // if (count($combinations[0]) > 0) {
        //     foreach ($combinations as $key => $combination) {
        //         $str = '';
        //         foreach ($combination as $k => $item) {
        //             if ($k > 0) {
        //                 $str .= '-' . str_replace(' ', '', $item);
        //             } else {
        //                 $str .= str_replace(' ', '', $item);
        //             }
        //         }
        //         $item = [];
        //         $item['type'] = $str;
        //         $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
        //         $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
        //         array_push($variations, $item);
        //     }
        // }
        //combinations end

        $img_names = [];
        $images = [];
        if (!empty($request->file('item_images'))) {
            foreach ($request->item_images as $img) {
                $image_name = Helpers::upload('product/', 'png', $img);
                array_push($img_names, $image_name);
            }
            $images = $img_names;
        }

        $food->variations = json_encode([]);
        $food->price = $request->price;
        $food->veg = $request->veg??0;
        $food->image = Helpers::upload('product/', 'png', $request->file('image'));
        $food->available_time_starts = $request->available_time_starts??'00:00:00';
        $food->available_time_ends = $request->available_time_ends??'23:59:59';
        // $food->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        // $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? json_encode($attributes) : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $food->store_id = Helpers::get_store_id();
        $food->module_id = Helpers::get_store_data()->module_id;
        $food->images = $images;
        $food->stock = $request->current_stock??100000;
        // $food->save();

        // dd($food);

        try
        {
            $food->save();
            $storeCatMap = StoreCatMap::where('store_id',Helpers::get_store_id())
            ->where('category_id',$request->sub_category_id)
            ->where('parent_id',$request->category_id)->first();
            if(!isset($storeCatMap->category_id))
            {

                $storeCatMap = new StoreCatMap;
                $storeCatMap->store_id = $request->store_id;
                $storeCatMap->category_id = $request->sub_category_id;
                $storeCatMap->parent_id = $request->category_id;
                $storeCatMap->status = 1;
                $storeCatMap->module_id = $request->module_id;
                $store = Store::where('id',Helpers::get_store_id())->first();
                $storeCatMap->zone_id = $store->zone_id;
                $storeCatMap->save();
            }
            // dd("item saved");
        }
        catch(\Exception $e)
        {
            dd($e);
        }

        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Models\Item',
                    'translationable_id' => $food->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                ));
            }
            if ($request->description[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Models\Item',
                    'translationable_id' => $food->id,
                    'locale' => $key,
                    'key' => 'description',
                    'value' => $request->description[$index],
                ));
            }
        }


        Translation::insert($data);

        return response()->json([], 200);
    }

    public function view($id)
    {
        $product = Item::findOrFail($id);
        $reviews=Review::where(['item_id'=>$id])->latest()->paginate(config('default_pagination'));
        return view('vendor-views.product.view', compact('product','reviews'));
    }

    public function edit($id)
    {
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }

        $product = Item::withoutGlobalScope('translate')->findOrFail($id);
        $product_category = json_decode($product->category_ids);
        $categories = Category::where(['parent_id' => 0])->module(Helpers::get_store_data()->module_id)->get();
        $module_data = config('module.'. Helpers::get_store_data()->module->module_type);
        // dd($module_data);
        return view('vendor-views.product.edit', compact('product', 'product_category', 'categories','module_data'));
    }

    public function status(Request $request)
    {
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $product = Item::find($request->id);
        $product->status = $request->status;
        $product->save();
        Toastr::success('Item status updated!');
        return back();
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        if(!Helpers::get_store_data()->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'price' => 'required|numeric|between:0.01,999999999999.99',
            'description.*' => 'max:1000',
            // 'discount' => 'required|numeric|min:0',
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'description.*.max' => translate('messages.description_length_warning'),   
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $p = Item::find($id);

        $p->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('en', $request->lang)];

        // dd($p);
        $attributes = [];
        $choice_options = [];

        if($request->has('attribute_id'))
        {

            foreach($request['attribute_id'] as $attribute_id)
            {
                array_push($attributes, $attribute_id);
            }
            // dd(json_encode($attributes));
    
            $attr_count = 0;
    
    
            foreach($request->choice_options as $choice_option)
            {
                $options=[];
                $choice_option['name'] = 'choice_'.$attributes[$attr_count];
                $attr = Attribute::where('id',$attributes[$attr_count])->first();
                $choice_option['title'] = $attr->name;
                $choice_option['multiselect'] = is_string($choice_option['multiselect'])?intval($choice_option['multiselect']):$choice_option['multiselect'];
                // dd($choice_option);

                foreach($choice_option['options'] as $opt)
                { 
                    $option['type']=$opt['type'];
                    $option['price']=$opt['price'];
                    $option['stock']=100000;
                    array_push($options, $option);
                }
                // dd($options);
                $choice_option['options'] = $options;
                array_push($choice_options, $choice_option);
                $attr_count++;
            }
            
        }
        // dd(json_encode($choice_options));
        
        $p->choice_options = $request->has('attribute_id') ? json_encode($choice_options) : json_encode([]);

        $images = $p['images'];
        if ($request->has('item_images')){
            foreach ($request->item_images as $img) {
                $image = Helpers::upload('product/', 'png', $img);
                array_push($images, $image);
            }
        } 
        $p->variations = json_encode([]);
        // dd($p);
        $p->price = $request->price;
        $p->veg = $request->veg??0;
        $p->image = $request->has('image') ? Helpers::update('product/', $p->image, 'png', $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts??'00:00:00';
        $p->available_time_ends = $request->available_time_ends??'23:59:59';
        $p->discount = $request->discount??0;
        $p->discount_type = $request->discount_type??'percent';
        $p->attributes = $request->has('attribute_id') ? json_encode($attributes) : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $p->images = $images;
        $p->unit_id = $request->unit;
        $p->module_id = Helpers::get_store_data()->module_id;
        $p->stock = $request->current_stock??100000;
        // dd($p);
        
        try
        {
            $p->save();
            $storeCatMap = StoreCatMap::where('store_id',Helpers::get_store_id())
            ->where('category_id',$request->sub_category_id)
            ->where('parent_id',$request->category_id)->first();
            if(!isset($storeCatMap->category_id))
            {
                $storeCatMap = new StoreCatMap;
                $storeCatMap->store_id = $request->store_id;
                $storeCatMap->category_id = $request->sub_category_id;
                $storeCatMap->parent_id = $request->category_id;
                $storeCatMap->status = 1;
                $storeCatMap->module_id = Helpers::get_store_data()->module_id;
                $store = Store::where('id',Helpers::get_store_id())->first();
                $storeCatMap->zone_id = $store->zone_id;
                $storeCatMap->save();
            }
            // dd("item saved");
        }
        catch(\Exception $e)
        {
            dd($e);
        }


        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Models\Item',
                        'translationable_id' => $p->id,
                        'locale' => $key,
                        'key' => 'name'],
                    ['value' => $request->name[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Models\Item',
                        'translationable_id' => $p->id,
                        'locale' => $key,
                        'key' => 'description'],
                    ['value' => $request->description[$index]]
                );
            }
        }
        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $product = Item::find($request->id);

        if($product->image)
        {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }

        $product->delete();
        Toastr::success('Item removed!');
        return back();
    }

    public function variant_combination(Request $request)
    {
        $options = [];
        $price = $request->price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $result = [[]];
        foreach ($options as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        $combinations = $result;
        $stock = (boolean)$request->stock;
        return response()->json([
            'view' => view('vendor-views.product.partials._variant-combinations', compact('combinations', 'price', 'product_name','stock'))->render(),
            'length'=>count($combinations),
        ]);
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        $category_id = $request->query('category_id', 'all');
        // dd($category_id);
        $type = $request->query('type', 'all');
        $key = explode(' ', $request['search']);

        if($key)
        {
            $items = Item::
            when(is_numeric($category_id), function($query)use($category_id){
                return $query->whereHas('category',function($q)use($category_id){
                    return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->where('store_id',Helpers::get_store_id())
            // ->type($type)->latest()->paginate(config('default_pagination'));
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            })->type($type)->latest()->paginate(30);
        }
        else
        {
            $items = Item::
            when(is_numeric($category_id), function($query)use($category_id){
                return $query->whereHas('category',function($q)use($category_id){
                    return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->where('store_id',Helpers::get_store_id())
            // ->type($type)->latest()->paginate(config('default_pagination'));
            ->type($type)->latest()->paginate(30);
        }
        
        
        // ->type($type)->latest()->paginate(30);
        $category =$category_id !='all'? Category::findOrFail($category_id):null;   
        // dd($category);
        return view('vendor-views.product.list', compact('items', 'category', 'type'));
    }

    public function search(Request $request)
    {
        // dd($request->all());
        // $category_id = $request->query('category', 'all');
        $category_id = $request->category;
        // $type = $request->query('type', 'all');
        $type = $request->type;
        $key = explode(' ', $request['search']);
        // dd($category_id);
        $items=Item::
        when(is_numeric($category_id), function($query)use($category_id){
            return $query->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "%{$value}%");
            }
        })
        ->type($type)->get();
        $category =$category_id !='all'? Category::findOrFail($category_id):null; 
        // dd($items);  
        return response()->json([
            'view'=>view('vendor-views.product.partials._table', compact('items', 'category', 'type'))->render()
        ]);

        // $category_id = $request->query('category', 'all');
        // // dd($category_id);
        // $type = $request->query('type', 'all');
        // $key = explode(' ', $request['search']);

        // if($key)
        // {
        //     $items = Item::
        //     when(is_numeric($category_id), function($query)use($category_id){
        //         return $query->whereHas('category',function($q)use($category_id){
        //             return $q->whereId($category_id)->orWhere('parent_id', $category_id);
        //         });
        //     })
        //     // ->type($type)->latest()->paginate(config('default_pagination'));
        //     ->where(function ($q) use ($key) {
        //         foreach ($key as $value) {
        //             $q->where('name', 'like', "%{$value}%");
        //         }
        //     })->type($type)->latest()->paginate(30);
        // }
        // else
        // {
        //     $items = Item::
        //     when(is_numeric($category_id), function($query)use($category_id){
        //         return $query->whereHas('category',function($q)use($category_id){
        //             return $q->whereId($category_id)->orWhere('parent_id', $category_id);
        //         });
        //     })
        //     // ->type($type)->latest()->paginate(config('default_pagination'));
        //     ->type($type)->latest()->paginate(30);
        // }
        
        
        // ->type($type)->latest()->paginate(30);
        // $category =$category_id !='all'? Category::findOrFail($category_id):null;   
        // // dd($category);
        // return view('vendor-views.product.list', compact('items', 'category', 'type'));
    }

    public function remove_image(Request $request)
    {
        if (Storage::disk('public')->exists('product/' . $request['name'])) {
            Storage::disk('public')->delete('product/' . $request['name']);
        }
        $item = Item::find($request['id']);
        $array = [];
        if (count($item['images']) < 2) {
            Toastr::warning('You cannot delete all images!');
            return back();
        }
        foreach ($item['images'] as $image) {
            if ($image != $request['name']) {
                array_push($array, $image);
            }
        }
        Item::where('id', $request['id'])->update([
            'images' => json_encode($array),
        ]);
        Toastr::success('Item image removed successfully!');
        return back();
    }

    public function bulk_import_index()
    {
        return view('vendor-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $data = [];
        $skip = ['youtube_video_url'];
        foreach ($collections as $collection) {
            if ($collection['name'] === "" || $collection['category_id'] === "" || $collection['sub_category_id'] === "" || $collection['price'] === "" || empty($collection['available_time_starts']) === "" || empty($collection['available_time_ends']) || empty($collection['veg']) === "") {
                Toastr::error(translate('messages.please_fill_all_required_fields'));
                return back();
            }
            array_push($data, [
                'name' => $collection['name'],
                'category_id' => $collection['sub_category_id']?$collection['sub_category_id']:$collection['category_id'],
                'category_ids' => json_encode([['id' => $collection['category_id'], 'position' => 0], ['id' => $collection['sub_category_id'], 'position' => 1]]),
                'veg' => $collection['veg']??0,  //$request->item_type;
                'price' => $collection['price'],
                'discount' => $collection['discount'],
                'discount_type' => $collection['discount_type'],
                'description' => $collection['description'],
                'available_time_starts' => $collection['available_time_starts']??'00:00:00',
                'available_time_ends' => $collection['available_time_ends']??'23:59:59',
                'image' => $collection['image'],
                'images' => json_encode([]),
                'store_id' => Helpers::get_store_id(),
                'module_id' => Helpers::get_store_data()->module_id,
                'add_ons' => json_encode([]),
                'attributes' => json_encode([]),
                'choice_options' => json_encode([]),
                'variations' => json_encode([]),
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
        }

        try
        {
            DB::beginTransaction();
            DB::table('items')->insert($data);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.product_imported_successfully', ['count'=>count($data)]));
        return back();
    }

    public function bulk_export_index()
    {
        return view('vendor-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        // dd($request->all());
        if(!Helpers::get_store_data()->item_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }

        $request->validate([
            'type'=>'required',
            'start_id'=>'required_if:type,id_wise',
            'end_id'=>'required_if:type,id_wise',
            'from_date'=>'required_if:type,date_wise',
            'to_date'=>'required_if:type,date_wise'
        ]);
        // $products = null;
        if($request['type']=='date_wise')
        {
            $items=Item::where('store_id',Helpers::get_store_id())->orderBy('order_count', 'DESC')->get();
            // $items=Item::where('store_id',Helpers::get_store_id())->get();

            // $sum=0;
            
            $products=[];
            foreach($items as $item)
            {
                $order_details = OrderDetail::
                where('item_id',$item->id)
                ->whereBetween('created_at',[$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59'])->get();

                // dd($order_details);
                // $item->order_count=$order_details->count();

                
                // dd($item->order_ids);


                $sum_quantity=0;
                if($item->order_count>0 && $order_details->count()>0)
                {
                    // $products=$item;
                    $item->order_count=$order_details->count();
                    $order_ids='';
                    foreach($order_details as $order_detail)
                    {
                        $order_ids = $order_detail->order_id.','.$order_ids;
                        // array_push($order_ids,$order_detail->order_id);
                        $sum_quantity = $order_detail->quantity+$sum_quantity;
                    }
    
                    $item->order_ids = $order_ids;
                    $item->quantity_sold = $sum_quantity;
                    array_push($products,$item);
                }
                // $sum = $order_details_count+$sum;


                // dump(isset($order_details)?$order_details->count():'no order '.$item->id);
            }
            // $products=$items;
            // dd($products);
            // return $products; 
            // $products = $products->sortby('order_count');
            // return ProductLogic::format_export_items_vendor($products);

            $file_name = 'Items_from_'.$request['from_date'].'_to_'.$request['to_date'].'.xlsx';
            // $file_name = 'Items.xlsx';
            return (new FastExcel(ProductLogic::format_export_items_vendor($products)))->download($file_name);

        }
        else
        {

            $items = Item::
            // when($request['type']=='date_wise', function($query)use($request){
            //     $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
            // })
            // ->
            when($request['type']=='id_wise', function($query)use($request){
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            })
            ->where('store_id', Helpers::get_store_id())
            // ->groupBy('order_count')
            ->orderBy('order_count', 'DESC')
            ->get();

            // return $items;

            $products=[];
            foreach($items as $item)
            {
                $order_details = OrderDetail::
                where('item_id',$item->id)->get();

                // dd($order_details);
                // $item->order_count=$order_details->count();

                
                // dd($item->order_ids);

                $sum_quantity = 0;
                if($item->order_count>0 && $order_details->count()>0)
                {
                    $item->order_count = $order_details->count();
                    // $products=$item;
                    $order_ids='';

                    foreach($order_details as $order_detail)
                    {
                        $order_ids = $order_detail->order_id.','.$order_ids;
                        // array_push($order_ids,$order_detail->order_id);
                        $sum_quantity = $order_detail->quantity+$sum_quantity;
                    }
    
                    $item->order_ids = $order_ids;
                    $item->quantity_sold = $sum_quantity;
                }
                else
                {

                    $item->order_ids = 'no order';
                    $item->quantity_sold = 0;

                }
                array_push($products,$item);
                // $sum = $order_details_count+$sum;


                // dump(isset($order_details)?$order_details->count():'no order '.$item->id);
            }
            // $products = $products->sortby('order_count');
            // return $products;
            // return ProductLogic::format_export_items_vendor($products);
            $file_name = 
            $request['type']=='id_wise'?'Items_from_'.$request['start_id'].'_to_'.$request['end_id'].'.xlsx'
            :'Items.xlsx';

            return (new FastExcel(ProductLogic::format_export_items_vendor($products)))->download($file_name);
            
        }
    }

    public function item_wise_report_index()
    {
        // dd('index');

        return view('vendor-views.report.item-wise-report');
    }

    public function item_wise_report(Request $request)
    {
        $orders = Items::get();

        $data = [];
        foreach($orders as $order)
        {
            $store = \App\Models\Store::where('id',$order['store_id'])->first();
            $user = \App\Models\User::where('id',$order['user_id'])->first();
            $zone = \App\Models\Zone::where('id',$order['zone_id'])->first();
            $module = \App\Models\Module::where('id',$order['module_id'])->first();

            $adr = json_decode($order['delivery_address']);

            $temp=[
                'Order ID'=>$order['id'],
                'Order Status'=>$order['order_status'],
                'Order Time'=>$order['created_at'],
                'Store ID'=>$store->id,
                'Store Name'=>$store->name,
                'Sub Total'=>$order['sub_total'],
                'Service fee %'=>$order['service_fee_percent'].'%',
                'Store Commission %'=>$order['gm_commission_percent'].'%',
                'Promo Discount'=>$order['coupon_discount_amount'],
                'Sales tax'=>$order['sales_tax'],
                'Service fee'=>$order['service_fee_amount'],
                'GoMeat Commission'=>$order['gm_commission'],
                'Delivery Charge'=>$order['delivery_charges'],
                'Tip'=>$order['dm_tips'],
                'Net to Store'=>$order['net_to_store'],
                'Order total/Cash'=>$order['order_amount'],
                'GoMeat Revenue'=>$order['gomeat_revenue'],
                'Promo Code'=>$order['coupon_code'],
                'Payment Method'=>$order['payment_method'],
                'Delivery Mode'=>$order['order_type'],
                'Transaction Status'=>$order['payment_status'],
                'Transaction ID'=>$order['transaction_reference'],
                'Customer ID'=>isset($user->id)?$user->id:'user was deleted',
                'Customer Name'=>isset($user->id)?$user->f_name.' '.$user->l_name:'user was deleted',
                'Customer Email'=>isset($user->id)?$user->email:'user was deleted',
                'Customer Phone'=>isset($user->id)?$user->phone:'user was deleted',
                'Delivery Address'=>$adr->address,
                'Pickup Address'=>$store->address,
                'Description'=>$order['order_note'],
                'Distance (in miles)'=>$order['distance'],
                'Pickup Time'=>$order['picked_up'],
                'Delivery Time'=>$order['delivered'],
                'Zones ID'=>$zone->name,
                'Module'=>$module->module_name
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

    public function stock_limit_list(Request $request)
    {
        $category_id = $request->query('category_id', 'all');
        $type = $request->query('type', 'all');
        $items = Item::
        when(is_numeric($category_id), function($query)use($category_id){
            return $query->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        ->type($type)->latest()->paginate(config('default_pagination'));
        $category =$category_id !='all'? Category::findOrFail($category_id):null;  
        return view('vendor-views.product.stock_limit_list', compact('items', 'category', 'type'));

    }

    public function get_variations(Request $request)
    {
        $product = Item::find($request['id']);

        return response()->json([
            'view' => view('vendor-views.product.partials._update_stock', compact('product'))->render()
        ]);
    }

    public function stock_update(Request $request)
    {
        $variations = [];
        $stock_count = $request['current_stock'];
        if ($request->has('type')) {
            foreach ($request['type'] as $key => $str) {
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
            }
        }


        $product = Item::find($request['product_id']);

        $product->stock = $stock_count ?? 0;
        $product->variations = json_encode($variations);
        $product->save();
        Toastr::success(translate("messages.product_updated_successfully"));
        return back();
        
        
    }
}
