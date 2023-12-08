<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_categories(Request $request)
    {
        try {
            $categories = Category::where(['position'=>0,'status'=>1]);
            if ($request->header('country') == 'PK') {
                $getcategories = $categories->whereNotIn('id', [1,2,4,5]);
            } else {
                $getcategories = $categories->whereNotIn('id', [1664, 1666]);
            }
            $finalcategories = $getcategories->orderBy('priority','desc')->get();
            return response()->json(Helpers::category_data_formatting($finalcategories, true), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_childes($id)
    {
        try {
            $categories = Category::where(['parent_id' => $id,'status'=>1])->orderBy('priority','desc')->get();
            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
