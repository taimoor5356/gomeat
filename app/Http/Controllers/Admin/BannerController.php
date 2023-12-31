<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Banner;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;

class BannerController extends Controller
{
    function index()
    {
        $banners = Banner::with('module')->latest()->paginate(config('default_pagination'));
        return view('admin-views.banner.index', compact('banners'));
    }

    public function store(Request $request)
    {        
        // dd('request came');
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191',
            'image' => 'required',
            // 'banner_type' => 'required',
            // 'zone_id' => 'required',
            // 'store_id' => 'required_if:banner_type,store_wise',
            // 'item_id' => 'required_if:banner_type,item_wise',
            // 'module_id'=>'required'
        ], [
            // 'zone_id.required' => translate('messages.select_a_zone'),
            // 'store_id.required_if'=> translate('messages.store is required when banner type is store wise'),
            // 'item_id.required_if'=> translate('validation.required_if',['attribute'=>translate('messages.item'), 'other'=>translate('messages.banner').' '.translate('messages.type'), 'value'=>translate('messages.item_wise')]),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $banner = new Banner;
        $banner->title = $request->title;
        $banner->type = $request->banner_type;
        $banner->country_id = !empty($request->region_id) ? $request->region_id : null;
        // $banner->zone_id = $request->zone_id;
        $banner->zone_id = 0;
        $banner->image = Helpers::upload('banner/', 'png', $request->file('image'));
        // $banner->data = ($request->banner_type == 'store_wise')?$request->store_id:$request->item_id;
        $banner->data = 0;
        // $banner->module_id = $request->module_id;
        $banner->module_id = 0;
        // dd($banner);
        $banner->save();
 
        return response()->json([], 200);
    }

    public function edit(Banner $banner)
    {
        return view('admin-views.banner.edit', compact('banner'));
    }

    public function status(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_status_updated'));
        return back();
    }

    public function featured(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $banner->featured = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_featured_status_updated'));
        return back();
    }

    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191',
            // 'banner_type' => 'required',
            // 'zone_id' => 'required',
            // 'store_id' => 'required_if:banner_type,store_wise',
            // 'item_id' => 'required_if:banner_type,item_wise',
        ], [
            // 'zone_id.required' => translate('messages.select_a_zone'),
            // 'store_id.required_if'=> translate('messages.store is required when banner type is store wise'),
            // 'item_id.required_if'=> translate('validation.required_if',['attribute'=>translate('messages.item'), 'other'=>translate('messages.banner').' '.translate('messages.type'), 'value'=>translate('messages.item_wise')]),
        ]);

   
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $banner->title = $request->title;
        $banner->type = $request->banner_type;
        $banner->country_id = !empty($request->region_id) ? $request->region_id : null;
        $banner->zone_id = 0;
        $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->data = 0;
        $banner->save();

        return response()->json([], 200);
    }

    public function delete(Banner $banner)
    {
        if (Storage::disk('public')->exists('banner/' . $banner['image'])) {
            Storage::disk('public')->delete('banner/' . $banner['image']);
        }
        $banner->delete();
        Toastr::success(translate('messages.banner_deleted_successfully'));
        return back();
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $banners=Banner::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('title', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('admin-views.banner.partials._table',compact('banners'))->render(),
            'count'=>$banners->count()
        ]);
    }
}
