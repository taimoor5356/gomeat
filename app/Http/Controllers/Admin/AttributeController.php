<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Item;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    function index()
    {
        $attributes = Attribute::orderBy('name')->paginate(config('default_pagination'));
        return view('admin-views.attribute.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:attributes|max:100',
        ], [
            'name.required' => translate('messages.Name is required!'),
        ]);

        $attribute = new Attribute;
        $attribute->name = $request->name;
        $attribute->save();

        Toastr::success(translate('messages.attribute_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);
        return view('admin-views.attribute.edit', compact('attribute'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100|unique:attributes,name,'.$id,
        ], [
            'name.required' => translate('messages.Name is required!'),
        ]);

        $attribute = Attribute::findOrFail($id);
        $attribute->name = $request->name;
        $attribute->save();
        Toastr::success(translate('messages.attribute_updated_successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $attribute = Attribute::findOrFail($request->id);
        $attribute->delete();
        Toastr::success(translate('messages.attribute_deleted_successfully'));
        return back();
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $attributes=Attribute::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('admin-views.attribute.partials._table',compact('attributes'))->render()
        ]);
    }

    public function bulk_import_index()
    {
        return view('admin-views.attribute.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        // dd($request->file('products_file'));

        try {
            // $collections = (new FastExcel)->import($request->file('products_file'));
            $collections = fastexcel()->import($request->file('products_file'));
            // dd($collections);
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        // $data = [];
        // $skip = ['youtube_video_url'];

        // dd($collections);
        $product_id=0;
        $addon_name='';
        // $data=array();
        $product_ids=[];
        $addon_index=0;
        foreach ($collections as $collection) {

                // if ($collection['name'] === "" ) {
                //     Toastr::error(translate('messages.please_fill_all_required_fields'));
                //     return back();
                // }

                // dd($collection);

                
                if($collection['Product Id*'])
                {
                    // $product='';
                    $product_id=$collection['Product Id*'];
                    $addon_name=$collection['Addon Name*'];
                    // dd(!isset($product_ids[$product_id]));
                    
                    // dd($product_id);
                    if(!isset($product_ids[$product_id]))
                    {
                        $product_ids[$product_id]='yes';

                        $product=Item::where('old_id',$product_id)
                        ->first();

                        if($product)
                        {

                            $product->attributes='[]';
                            $product->variations='[]';
                            $product->choice_options='[]';
                            
                            $product->update();
                        }
                        // dd($product_ids);
                    }
                    
                    if($addon_name && $product_id)
                    {
                        // dd(array_search($collection['product_id'], array_keys($collections)));
                        if(!Attribute::where('name',$collection['Addon Name*'])->first())
                        {
    
                            $attribute = new Attribute();
                            $attribute->name=$collection['Addon Name*'];
                            $attribute->save();
                            // dd('attribute '.$collection['addon_name'].' saved');
                        }

                        $attribute=Attribute::where('name',$addon_name)->first();
                        
                        // dd($attribute);
                        if($attribute)
                        {
                            // $product=Item::where('old_id',65920)
                            // // ->where('attributes','[]')
                            // ->first();
                            $product=Item::where('old_id',$product_id)
                            ->first();

                            // dd($product);
                            if($product)
                            {
                                // $product->attributes='[]';
                                // $product->variations='[]';
                                // $product->choice_options='[]';
                                
                                // dd($product);
                                if($product->attributes!='[]' )
                                {
                                    $atr=json_decode($product->attributes);

                                    // dd($attribute->id);
                                    
                                    if(!in_array($attribute->id,$atr))
                                    {
                                        array_push($atr,"$attribute->id");
                                        // dd($atr);
                                        $product->attributes=json_encode($atr);
                                        
                                        // $product->multi_select=$collection['Multi Select*'];
                                        
                                        $variations = json_decode($product->variations);

                                        $choice_options = json_decode($product->choice_options);

                                        $options=array();

                                        for($i = $addon_index+1; $i<=sizeof($collections) ; $i++)
                                        {
                                            if($collections[$i]['Product Id*'] || $collections[$i]['Product Id*']=='end')
                                            {

                                                $choices=json_encode([
                                                    "name"=>"choice_".$attribute->id,
                                                    "title"=>$addon_name,
                                                    "multiselect"=>$collection['Multi Select*'],
                                                    "options"=>$options
                                                ]);

                                                array_push($choice_options,json_decode($choices));
                                                // $choice_options->push([
                                                //     "name"=>"choice_".$attribute->id,
                                                //     "title"=>$addon_name,
                                                //     "multiselect"=>$collection['Multi Select*'],
                                                //     "options"=>$options
                                                // ]);
                                                // dd($choice_options);
                                                
                                                $product->choice_options=json_encode($choice_options);
                                                $product->variations=json_encode($variations);
                                                $addon_index=$i;
                                                $i=sizeof($collections);
                                                
                                                // dd($product);
                                            }
                                            else
                                            {
                                                $variants = json_encode([
                                                    "type"=>$collections[$i]['Addon Option Name*'],
                                                    "price"=>$collections[$i]['Price*'],//==0?$product->price:$collections[$i]['Price*'],
                                                    "stock"=>$product->stock
                                                ]);
                                                    
                                                array_push($variations,json_decode($variants));
                                                
                                                array_push($options,json_decode($variants));
                                                // dd($options);
                                            }
                                        }
                                        // dd($product);
            
                                        $product->update();
                                    
                                        // dd($product);
                                        // dd($attribute->id);
                                        // dd(in_array($attribute->id,$atr));
                                    }

                                }
                                else
                                {
                                    $product->attributes=json_encode(["$attribute->id"]);
                                    // $product->multi_select=$collection['Multi Select*'];
       
                                   //  dd(sizeof($collections) );
                                           
                                   $variations = array();
                                   $variants='';
       
                                   $choice_options = array();
                                   $options=array();
                                   // $i=0;
                                   // dd($addon_index);
                                   for($i = $addon_index+1; $i<=sizeof($collections) ; $i++)
                                   {
                                       if($collections[$i]['Product Id*'] || $collections[$i]['Product Id*']=='end')
                                       {
                                           $choice_options=json_encode([[
                                               "name"=>"choice_".$attribute->id,
                                               "title"=>$addon_name,
                                               "multiselect"=>$collection['Multi Select*'],
                                               "options"=>$options
                                           ]]);
       
                                           // dd("[".implode(",", $variants)."]");
                                           
                                           $product->choice_options=$choice_options;
                                           $product->variations="[".implode(",", $variations)."]";
                                           $addon_index=$i;
                                           $i=sizeof($collections);

                                        //    dd($product);
                                       }
                                       else
                                       {
                                           $variants = json_encode([
                                               "type"=>$collections[$i]['Addon Option Name*'],
                                               "price"=>$collections[$i]['Price*'],//==0?$product->price:$collections[$i]['Price*'],
                                               "stock"=>$product->stock
                                           ]);

                                        //    $options_data = json_encode([

                                        //    ]);
                                           
                                           array_push($variations,$variants);
                                           
                                           array_push($options,json_decode($variants));
                                       }
                                   }
                                   
                                   // $addon_index=$i-1;
                                   
                                   // dd($addon_index);
                                   
                                   $product->update();
                                //    dd($product);
                                }
                                // dd('stop');
                                
                            }
    
                            // dump($product);
                        }
                    }
                    // dd('attribute '.$collection['addon_name'].' exists');


                }
              
        }
        // DB::table('attributes')->insert($data);
        Toastr::success(translate('messages.attribute_imported_successfully'));
        return back();
    }

    public function bulk_export_index()
    {
        return view('admin-views.attribute.bulk-export');
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
        $attributes = Attribute::when($request['type']=='date_wise', function($query)use($request){
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
        ->when($request['type']=='id_wise', function($query)use($request){
            $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
        })
        ->get();
        return (new FastExcel($attributes))->download('Attributes.xlsx');
    }
}