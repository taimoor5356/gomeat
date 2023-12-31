<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Campaign;
use App\Models\Banner;
use App\CentralLogics\BannerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function get_banners(Request $request)
    {
        // if (!$request->hasHeader('zoneId')) {
        //     $errors = [];
        //     array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
        //     return response()->json([
        //         'errors' => $errors
        //     ], 403);
        // }
        // $zone_id= $request->header('zoneId');
        $countryName = $request->header('country');
        $country = Country::where('short_name', '=', $countryName)->first();
        $countryId = NULL;
        if (isset($country)) {
            $countryId = $country->id;
        }
        $zone_id= 0;
        // $banners = BannerLogic::get_banners($zone_id, $request->query('featured'));

        if($request->featured)
        {

            $banners = Banner::where('status',1)->where('featured',1)->where('country_id', $countryId)->get();
        }
        else
        {
            $banners = Banner::where('status',1)->where('country_id', $countryId)->get();
        }

        $campaigns = [];
        // if(!$request->featured)
        // {
        //     $campaigns = Campaign::
        //     when(config('module.current_module_data'), function($query)use($zone_id){
        //         $query->module(config('module.current_module_data')['id']);
        //         if(!config('module.current_module_data')['all_zone_service']) {
        //             $query->whereHas('stores', function($q)use($zone_id){
        //                 $q->whereIn('zone_id', json_decode($zone_id, true));
        //             });
        //         }
        //     })
        //     ->running()->active()->get();
        // }

        try {
            return response()->json(['campaigns'=>Helpers::basic_campaign_data_formatting($campaigns, true),'banners'=>$banners], 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
