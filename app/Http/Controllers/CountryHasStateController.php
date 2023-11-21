<?php

namespace App\Http\Controllers;

use App\Models\CountryHasState;
use Illuminate\Http\Request;

class CountryHasStateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CountryHasState  $countryHasState
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $state = CountryHasState::where('id', $id)->first();
        if (isset($state)) {
            return response()->json($state);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CountryHasState  $countryHasState
     * @return \Illuminate\Http\Response
     */
    public function edit(CountryHasState $countryHasState)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CountryHasState  $countryHasState
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        return $this->storeData($request->all(), $id);
    }

    public function storeData($request, $id = null)
    {
        $request = (object)$request;
        if (!empty($id)) {
            $state = CountryHasState::find($id);
        } else {
            $state = new CountryHasState();
        }
        $state->name = $request->name;
        $state->store_online_payment = $request->store_online_payment;
        $state->store_cash_payment = $request->store_cash_payment;
        $state->restaurant_online_payment = $request->restaurant_online_payment;
        $state->restaurant_cash_payment = $request->restaurant_cash_payment;
        $state->save();
        return redirect()->back()->withSuccess('Successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CountryHasState  $countryHasState
     * @return \Illuminate\Http\Response
     */
    public function destroy(CountryHasState $countryHasState)
    {
        //
    }
}
