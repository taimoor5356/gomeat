<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\OrderTransaction;
use App\Models\Zone;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Scopes\StoreScope;

class ReportController extends Controller
{
    public function item_wise_report(Request $request)
    {
        // $zone = null;
        // $store = null;
        // $items = null;
        return view('vendor-views.report.item-wise-report');
    }

}
