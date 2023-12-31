<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\Store;
use App\Models\Module;
use App\Models\StoreCatMap;
use App\Models\Attribute;
use App\Models\Review;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\Models\ItemCampaign;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;
use App\Scopes\StoreScope;
use App\Models\Translation;

class ItemController extends Controller
{
    public function index()
    {
        $categories = Category::where(['position' => 0])->get();
        return view('admin-views.product.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'image' => 'required',
            'price' => 'required|numeric|between:.01,999999999999.99',
            // 'currency' => 'required',
            // 'weight' => 'required|numeric|between:.01,999999999999.99',
            'discount' => 'required|numeric|min:0',
            'store_id' => 'required',
            'module_id' => 'required',
            'description.*' => 'max:1000',
        ], [
            'description.*.max' => translate('messages.description_length_warning'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
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

        $item = new Item;
        $item->name = $request->name[array_search('en', $request->lang)];

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

        $item->category_ids = json_encode($category);
        $item->category_id = $request->sub_category_id ? $request->sub_category_id : $request->category_id;
        $item->description =  $request->description[array_search('en', $request->lang)];

        // dd($request->attribute_id);
        // dd($request->options);
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
    
    
            foreach($request['options'] as $numCount => $choice_option)
            {
                // dd($choice_option);
                $options=[];
                $variant['name'] = 'choice_'.$attributes[$attr_count];
                $attr = Attribute::where('id',$attributes[$attr_count])->first();
                $variant['title'] = $attr->name;
                $variant['multiselect'] = $choice_option['type']=='single'?0:1;
                $variant['optional'] = (!empty($request['optional'][$numCount]['optional']) ? ($request['optional'][$numCount]['optional']=='1'?1:0):0);
    
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
        $item->choice_options = $request->has('attribute_id') ? json_encode($choice_options) : json_encode([]);

        // dd(json_encode($choice_options));
        // return $choice_options;
        
        // $choice_options = [];
        // if ($request->has('choice')) {
        //     foreach ($request->choice_no as $key => $no) {
        //         $str = 'choice_options_' . $no;
        //         if ($request[$str][0] == null) {
        //             $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
        //             return response()->json(['errors' => Helpers::error_processor($validator)]);
        //         }
        //         $temp['name'] = 'choice_' . $no;
        //         $temp['title'] = $request->choice[$key];
        //         $temp['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
        //         array_push($choice_options, $temp);
        //     }
        // }

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
        //         foreach ($combination as $k => $temp) {
        //             if ($k > 0) {
        //                 $str .= '-' . str_replace(' ', '', $temp);
        //             } else {
        //                 $str .= str_replace(' ', '', $temp);
        //             }
        //         }
        //         $temp = [];
        //         $temp['type'] = $str;
        //         $temp['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
        //         $temp['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
        //         array_push($variations, $temp);
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

        // dd($item);

        $item->variations = json_encode([]);
        // $item->price = $request->price;
        // $storeData = Store::find($request->store_id);
        // $onlinePayment = 0;
        // $cashPayment = 0;
        // if (isset($storeData)) {
        //     if ($request->module_id == 1) {
        //         $getState = json_decode($storeData->state_info);
        //         $onlinePayment = $getState->store_online_payment;
        //         $cashPayment = $getState->store_cash_payment;
        //     } else if ($request->module_id == 2) {
        //         $getState = json_decode($storeData->state_info);
        //         dd($getState);
        //         $onlinePayment = $getState->restaurant_online_payment;
        //         $cashPayment = $getState->restaurant_cash_payment;
        //     }
        // }
        // dd($onlinePayment, 'online payment', $cashPayment, 'cash payment');
        // dd('stop');
        // $item->currency = $request->currency;
        $item->weight = isset($request->weight) ? $request->weight : '';
        $item->image = Helpers::upload('product/', 'png', $request->file('image'));
        $item->available_time_starts = $request->available_time_starts ?? '00:00:00';
        $item->available_time_ends = $request->available_time_ends ?? '23:59:59';
        $item->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $item->discount_type = $request->discount_type;
        $item->unit_id = $request->unit;
        $item->multi_select = 0;
        // dd($item);
        $item->attributes = $request->has('attribute_id') ? json_encode($attributes) : json_encode([]);
        // dd($item);
        $item->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $item->store_id = $request->store_id;
        $item->veg = $request->veg;
        $item->module_id = $request->module_id;
        $item->stock = $request->current_stock ?? 100000;

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Accounts Starts
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // $item->country = !empty($request->country) ? $request->country : '';
        $item->price = $request->price; // total amount after tax calculation
        $item->sales_tax = !empty($request->sales_tax) ? $request->sales_tax : '0.00'; // percentage
        // if (!empty($request->sales_tax)) {
        //     $item->sales_tax = $request->sales_tax; // percentage
        //     $salesTaxPercent = $request->sales_tax;
        //     $taxFactor = $request->sales_tax / (100 + $salesTaxPercent);
        //     $taxAmount = $request->price * $taxFactor;
        //     $amountExOfTax = $request->price - $taxAmount;
        //     $item->total_sales_tax_amount = $taxAmount;
        //     $item->product_price = $amountExOfTax;
        // } else {
        //     if (!empty($request->country)) {
        //         if ($request->country == 'pk') {
        //             $item->sales_tax = 16; // percentage
        //             $salesTaxPercent = 16;
        //             $taxFactor = $salesTaxPercent / (100 + $salesTaxPercent);
        //             $taxAmount = $request->price * $taxFactor;
        //             $amountExOfTax = $request->price - $taxAmount;
        //             $item->total_sales_tax_amount = $taxAmount;
        //             $item->product_price = $amountExOfTax;
        //         } else {
        //             $item->product_price = $request->price;
        //         }
        //     } else {
        //         $item->product_price = $request->price;
        //     }
        // }

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Accounts Ends
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////


        $item->gm_commission = $request->gm_commission ?? 0;
        $item->images = $images;
        // dd($item);
        try
        {

            $item->save();
            $storeCatMap = StoreCatMap::where('store_id',$request->store_id)
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
                $store = Store::where('id',$request->store_id)->first();
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
                    'translationable_id' => $item->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                ));
            }
            if ($request->description[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Models\Item',
                    'translationable_id' => $item->id,
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
        $product = Item::with('store.country')->withoutGlobalScope(StoreScope::class)->where(['id' => $id])->first();
        $reviews = Review::where(['item_id' => $id])->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.view', compact('product', 'reviews'));
    }

    public function edit($id)
    {
        $product = Item::withoutGlobalScope(StoreScope::class)->withoutGlobalScope('translate')->with('store', 'category', 'module')->findOrFail($id);
        if (!$product) {
            Toastr::error(translate('messages.item') . ' ' . translate('messages.not_found'));
            return back();
        }
        // $product['food_variations'] = 
        $temp = $product->category;
        if ($temp->position) {
            $sub_category = $temp;
            $category = $temp->parent;
        } else {
            $category = $temp;
            $sub_category = null;
        }

        // $variat

        // dd($product);

        return view('admin-views.product.edit', compact('product', 'sub_category', 'category'));
    }

    public function status(Request $request)
    {
        $product = Item::withoutGlobalScope(StoreScope::class)->findOrFail($request->id);
        $product->status = $request->status;
        $product->save();
        Toastr::success(translate('messages.item_status_updated'));
        return back();
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'price' => 'required|numeric|between:.01,999999999999.99',
            // 'currency' => 'required',
            // 'weight' => 'required|numeric|between:.01,999999999999.99',
            'store_id' => 'required',
            'description' => 'array',
            'module_id' => 'required',
            'description.*' => 'max:1000',
            'discount' => 'required|numeric|min:0',
        ], [
            'description.*.max' => translate('messages.description_length_warning'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
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

        $item = Item::withoutGlobalScope(StoreScope::class)->find($id);

        $item->name = $request->name[array_search('en', $request->lang)];

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

        $item->category_id = $request->sub_category_id ? $request->sub_category_id : $request->category_id;
        $item->category_ids = json_encode($category);
        $item->description =  $request->description[array_search('en', $request->lang)];

        
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
    
            foreach($request->choice_options as $numCount => $choice_option)
            {
                $options=[];
                $choice_option['name'] = 'choice_'.$attributes[$attr_count];
                $attr = Attribute::where('id',$attributes[$attr_count])->first();
                $choice_option['title'] = $attr->name;
                $choice_option['multiselect'] = !(empty($choice_option['multiselect'])) ? (is_string($choice_option['multiselect'])?intval($choice_option['multiselect']):$choice_option['multiselect']) : 0;
                $choice_option['optional'] = (!empty($request['optional'][$numCount]['optional']) ? ($request['optional'][$numCount]['optional']=='1'?1:0):0);
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
        
        $item->choice_options = $request->has('attribute_id') ? json_encode($choice_options) : json_encode([]);
        
        $images = $item['images'];
        if ($request->has('item_images')) {
            foreach ($request->item_images as $img) {
                $image = Helpers::upload('product/', 'png', $img);
                array_push($images, $image);
            }
        }

        $item->variations = json_encode([]);
        // $item->price = $request->price;
        // $item->currency = $request->currency;
        $item->weight = isset($request->weight) ? $request->weight : '';
        $item->image = $request->has('image') ? Helpers::update('product/', $item->image, 'png', $request->file('image')) : $item->image;
        $item->available_time_starts = $request->available_time_starts ?? '00:00:00';
        $item->available_time_ends = $request->available_time_ends ?? '23:59:59';

        $item->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $item->discount_type = $request->discount_type;
        $item->unit_id = isset($request->unit) ? $request->unit : '';
        $item->attributes = $request->has('attribute_id') ? json_encode($attributes) : json_encode([]);
        $item->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $item->store_id = $request->store_id;
        $item->module_id= $request->module_id;
        // $item->sales_tax = $request->sales_tax ?? 0;
        // $item->gm_commission = $request->gm_commission ?? 0;
        $item->stock = $request->current_stock ?? 100000;
        $item->veg = $request->veg;
        $item->images = $images;
        $item->price = $request->price; // total amount after tax calculation

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Accounts Starts
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $salesTax = !empty($request->sales_tax) ? $request->sales_tax : 0.00;
        $item->sales_tax = $salesTax; // percentage
        $salesTaxAmount = 0;

        // if (empty($request->sales_tax)) {
        //     $sales_Tax = 16; // percentage
        //     $salesTaxAmount = ($request->price * $sales_Tax) / (100 + $request->sales_tax); // total tax amount after tax calculation
        // } else {
        //     $salesTaxAmount = ($request->price * $request->sales_tax) / 100; // total tax amount after tax calculation
        // }
        // $item->total_sales_tax_amount = $salesTaxAmount; // sales tax amount
        // $item->product_price = $request->price - $salesTaxAmount; // product price

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Accounts Ends
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        try
        {

            $item->save();
            $storeCatMap = StoreCatMap::where('store_id',$request->store_id)
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
                $store = Store::where('id',$request->store_id)->first();
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
                    [
                        'translationable_type' => 'App\Models\Item',
                        'translationable_id' => $item->id,
                        'locale' => $key,
                        'key' => 'name'
                    ],
                    ['value' => $request->name[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Models\Item',
                        'translationable_id' => $item->id,
                        'locale' => $key,
                        'key' => 'description'
                    ],
                    ['value' => $request->description[$index]]
                );
            }
        }

        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        $product = Item::withoutGlobalScope(StoreScope::class)->withoutGlobalScope('translate')->find($request->id);

        if ($product->image) {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }
        $product->translations()->delete();
        $product->delete();
        Toastr::success(translate('messages.product_deleted_successfully'));
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
        $stock = $request->stock == 'true' ? true : false;
        return response()->json([
            'view' => view('admin-views.product.partials._variant-combinations', compact('combinations', 'price', 'product_name', 'stock'))->render(),
            'length' => count($combinations),
            'stock' => $stock,
        ]);
    }

    public function variant_price(Request $request)
    {
        if ($request->item_type == 'food') {
            $product = Item::withoutGlobalScope(StoreScope::class)->find($request->id);
        } else {
            $product = ItemCampaign::find($request->id);
        }
        // $product = Item::withoutGlobalScope(StoreScope::class)->find($request->id);
        $str = '';
        $quantity = 0;
        $price = 0;
        $addon_price = 0;

        foreach (json_decode($product->choice_options) as $key => $choice) {
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $price = json_decode($product->variations)[$i]->price - Helpers::product_discount_calculate($product, json_decode($product->variations)[$i]->price, $product->store);
                }
            }
        } else {
            $price = $product->price - Helpers::product_discount_calculate($product, $product->price, $product->store);
        }

        return array('price' => Helpers::format_currency(($price * $request->quantity) + $addon_price));
    }
    public function get_categories(Request $request)
    {
        $key = explode(' ', $request['q']);
        $cat = Category::when(isset($request->module_id), function ($query) use ($request) {
                $query->where('module_id', $request->module_id);
            })
            ->when($request->sub_category, function ($query) {
                $query->where('position', '>', '0');
            })
            ->when(isset($key),function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            })
            ->where(['parent_id' => $request->parent_id])->get([DB::raw('id, name as text')]);

        return response()->json($cat);
    }
    public function get_attributes(Request $request)
    {
        $key = explode(' ', $request['q']);
        $attr = Attribute::when(isset($key),function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            })->where('id','>',0)->get();
            // })->get([DB::raw('id, name as text')]);

        return response()->json($attr);
    }

    public function get_items(Request $request)
    {
        $items = Item::withoutGlobalScope(StoreScope::class)->with('store')
            ->when($request->zone_id, function ($q) use ($request) {
                $q->whereHas('store', function ($query) use ($request) {
                    $query->where('zone_id', $request->zone_id);
                });
            })
            ->when($request->module_id, function ($q) use ($request) {
                $q->where('module_id', $request->module_id);
            })->get();
        $res = '';
        if (count($items) > 0 && !$request->data) {
            $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        }

        foreach ($items as $row) {
            $res .= '<option value="' . $row->id . '" ';
            if ($request->data) {
                $res .= in_array($row->id, $request->data) ? 'selected ' : '';
            }
            $res .= '>' . $row->name . ' (' . $row->store->name . ')' . '</option>';
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        // dd($request->page);
        // $page = isset($request->page)?$request->page:1;
        // $page = 1;
        $store_id = $request->query('store_id', 'all');
        $category_id = $request->query('category_id', 'all');
        // $module_id = $request->query('module_id', 'all');
        $type = $request->query('type', 'all');
        $items = Item::with('store.country')->withoutGlobalScope(StoreScope::class)
            // ->when($request->query('module_id', null), function ($query) use ($request) {
            //     return $query->module($request->query('module_id'));
            // })
            // ->when(is_numeric($module_id), function ($query) use ($module_id) {
            //     return $query->where('module_id', $module_id);
            // })
            ->when(is_numeric($store_id), function ($query) use ($store_id) {
                return $query->where('store_id', $store_id);
            })
            ->when(is_numeric($category_id), function ($query) use ($category_id) {
                return $query->whereHas('category', function ($q) use ($category_id) {
                    return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->type($type)
            // ->latest()->paginate(config('default_pagination'));
            ->latest()->paginate(10); // make 20
        $store = $store_id != 'all' ? Store::findOrFail($store_id) : null;
        $category = $category_id != 'all' ? Category::findOrFail($category_id) : null;
        // $module = $module_id != 'all' ? Module::findOrFail($module_id) : null;
        
        // dd($items->links());
        return view('admin-views.product.list', compact('items', 'store', 'category', 'type'));
    }

    public function remove_image(Request $request)
    {
        if (Storage::disk('public')->exists('product/' . $request['name'])) {
            Storage::disk('public')->delete('product/' . $request['name']);
        }
        $item = Item::withoutGlobalScope(StoreScope::class)->find($request['id']);
        $array = [];
        if (count($item['images']) < 2) {
            Toastr::warning(translate('all_image_delete_warning'));
            return back();
        }
        foreach ($item['images'] as $image) {
            if ($image != $request['name']) {
                array_push($array, $image);
            }
        }
        Item::withoutGlobalScope(StoreScope::class)->where('id', $request['id'])->update([
            'images' => json_encode($array),
        ]);
        Toastr::success(translate('item_image_removed_successfully'));
        return back();
    }

    public function search(Request $request)
    {
        // $key = explode(' ', $request['search']);
        $key = $request['search'];
        // $store=$request->store_id;
        $category_id = $request->query('category_id', 'all');

        $items=null;
        if(isset($request->store_id) && $request->store_id!='all')
        {
            if(isset($request->category_id) && $request->category_id != 'all')
            {
                $cat = Category::where('id',$request->category_id)->first();
                if($cat->position == 0 )
                {

                    $items = Item::where('store_id',$request->store_id)
                    ->when(is_numeric($category_id), function ($query) use ($category_id) {
                        return $query->whereHas('category', function ($q) use ($category_id) {
                            return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                        });
                    })
                    ->where('name', 'like', "%{$key}%")->get();
                }
                else
                {
                    $items = Item::where('store_id',$request->store_id)
                    ->where('category_id',$request->category_id)
                    ->where('name', 'like', "%{$key}%")->get();
                    
                }
                
            }
            else
            {
                $items = Item::where('store_id',$request->store_id)
                ->where('name', 'like', "%{$key}%")->get();
            }
        }
        else
        {
            // dd($key);
            $items = Item::
                where('name', 'like', "%{$key}%")->paginate(100);
                // where('name', 'like', "%{$key}%")->take(100)->get();
                // return $items;
                // dd($items);
        }
        // $key = $request['search'];
        // $items = Item::withoutGlobalScope(StoreScope::class)->where(function ($q) use ($key) {
        //     foreach ($key as $value) {
        //         $q->where('name', 'like', "%{$value}%");
        //     }
        // })->get();
        // ->limit()
        // $items = Item::where('name', 'like', "%{$key}%")->paginate(100);
        // dd(count($items));
        return response()->json([
            'count' => count($items),
            'view' => view('admin-views.product.partials._table', compact('items'))->render()
        ]);
    }

    public function review_list(Request $request)
    {
        $reviews = Review::with(['item', 'customer'])->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.reviews-list', compact('reviews'));
    }

    public function reviews_status(Request $request)
    {
        $review = Review::find($request->id);
        $review->status = $request->status;
        $review->save();
        Toastr::success(translate('messages.review_visibility_updated'));
        return back();
    }

    public function bulk_import_index()
    {
        return view('admin-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $data = [];
        foreach ($collections as $collection) {
            if ($collection['name'] === "" || $collection['category_id'] === "" || $collection['sub_category_id'] === "" || $collection['price'] === "" || $collection['store_id'] === "" || $collection['module_id'] === "") {
                Toastr::error(translate('messages.please_fill_all_required_fields'));
                return back();
            }

            $url = $collection['image'];
            
            $image_name='';
            if($url)
            {
                $contents = file_get_contents($url);
                $image_name = substr($url, strrpos($url, '/') + 1);
                Storage::put($name, $contents);
            }
            
            array_push($data, [
                'name' => $collection['name'],
                'category_id' => $collection['sub_category_id'] ? $collection['sub_category_id'] : $collection['category_id'],
                'category_ids' => json_encode([['id' => $collection['category_id'], 'position' => 0], ['id' => $collection['sub_category_id'], 'position' => 1]]),
                'veg' => $collection['veg'] ?? 0,  //$request->item_type;
                'price' => $collection['price'],
                'discount' => $collection['discount'],
                'discount_type' => $collection['discount_type'],
                'description' => $collection['description'],
                'available_time_starts' => $collection['available_time_starts'] ?? '00:00:00',
                'available_time_ends' => $collection['available_time_ends'] ?? '23:59:59',
                'unit_id' => is_int($collection['unit_id']) ? $collection['unit_id'] : null,
                // 'image' => $collection['image'],
                'image' => $image_name,
                'sales_tax' => $collection['sales_tax'],
                'status' => $collection['status'],
                'images' => $collection['images'] ?? json_encode([]),
                'store_id' => $collection['store_id'],
                'module_id' => $collection['module_id'],
                'stock' => $collection['module_id'] ?? 0,
                'add_ons' => $collection['add_ons'] ?? json_encode([]),
                'attributes' => $collection['attributes'] ??  json_encode([]),
                'choice_options' => $collection['choice_options'] ?? json_encode([]),
                'variations' => $collection['variations'] ?? json_encode([]),
                // 'variations' => json_encode([['type' => $collection['category_id'], 'price' => 0], ['price' => $collection['sub_category_id'], 'position' => 1]]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        try {
            DB::beginTransaction();
            DB::table('items')->insert($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.product_imported_successfully', ['count' => count($data)]));
        return back();
    }

    public function bulk_export_index()
    {
        return view('admin-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'start_id' => 'required_if:type,id_wise',
            'end_id' => 'required_if:type,id_wise',
            'from_date' => 'required_if:type,date_wise',
            'to_date' => 'required_if:type,date_wise'
        ]);
        $products = Item::when($request['type'] == 'date_wise', function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['from_date'] . ' 00:00:00', $request['to_date'] . ' 23:59:59']);
        })
            ->when($request['type'] == 'id_wise', function ($query) use ($request) {
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            })
            ->withoutGlobalScope(StoreScope::class)->get();
        return (new FastExcel(ProductLogic::format_export_items($products)))->download('Items.xlsx');
    }

    public function get_variations(Request $request)
    {
        $product = Item::withoutGlobalScope(StoreScope::class)->find($request['id']);

        return response()->json([
            'view' => view('admin-views.product.partials._update_stock', compact('product'))->render()
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


        $product = Item::withoutGlobalScope(StoreScope::class)->find($request['product_id']);

        $product->stock = $stock_count ?? 0;
        $product->variations = json_encode($variations);
        $product->save();
        Toastr::success(translate("messages.product_updated_successfully"));
        return back();
        
        
    }
}
