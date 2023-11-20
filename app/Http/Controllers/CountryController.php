<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $countries = Country::get();
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');
            $columnIndex = $columnIndex_arr[0]['column'];
            $columnName = $columnName_arr[$columnIndex]['data'];
            $columnSortOrder = $order_arr[0]['dir'];
            $searchValue = $search_arr['value'];

            $query = Country::query();

            if ($request->country_id) {
                $query->where('id', $request->country_id);
            }

            if (!empty($searchValue)) {
                $query->where('name', 'like', '%' . $searchValue . '%');
            }

            $totalRecords = $query->count();
            // SORTING
            if (!empty($columnName)) {
                if ($columnName == 'name') {
                    $query->orderBy('name', $columnSortOrder);
                } else {
                    $query = $query->orderBy($columnName, $columnSortOrder);
                }
            }
            $query = $query->skip($start)
                ->take($rowperpage);

            $data = $query->get();

            $arrData = [];

            foreach ($data as $key => $country) {
                $arrData[] = [
                    'country_id' => $country->id,
                    'name' => $country->name,
                    'short_name' => $country->short_name,
                    'currency_name' => $country->currency_name,
                    'currency_symbol' => $country->currency_symbol,
                    'gst' => $country->gst,
                    'action' => ''
                ];
            }

            return DataTables::of($arrData)
                ->addIndexColumn()
                ->with([
                    "draw" => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    "data" => $arrData
                ])
                ->make(true);
        }
        return view('admin-views.country.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin-views.country.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        return $this->storeData($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        return $this->storeData($request, $id);
    }

    public function storeData($request, $id = null)
    {
        try {
            $country = new Country();
            if (!empty($id)) {
                $country = Country::find($id);
            }
            $country->name = $request->name;
            $country->short_name = $request->short_name;
            $country->currency_name = $request->currency_name;
            $country->currency_symbol = $request->currency_symbol;
            $country->gst = $request->gst;
            $country->save();
            return redirect()->back()->withSuccess('Successful');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        //
    }
}
