<?php

namespace App\Http\Controllers;

use App\Models\JazzcashOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;

class JazzcashController extends Controller
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
     * @param  \App\Models\JazzcashController  $jazzcashController
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
        return view('jazzcash_product_payment', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JazzcashController  $jazzcashController
     * @return \Illuminate\Http\Response
     */
    public function edit(JazzcashController $jazzcashController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JazzcashController  $jazzcashController
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JazzcashController $jazzcashController)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JazzcashController  $jazzcashController
     * @return \Illuminate\Http\Response
     */
    public function destroy(JazzcashController $jazzcashController)
    {
        //
    }

    public function checkOut(Request $request)
    {
        $product_id = $request->product_id;
        $pp_Amount = 30.00;
        $dateTime = new \DateTime();
        $pp_TxnDateTime = $dateTime->format('YmdHis');
        $expiryDateTime = $dateTime->modify('+' . 1 . ' hours');
        $pp_TxnExpiryDateTime = $expiryDateTime->format('YmdHis');
        $pp_TxnRefNo = 'T' . $pp_TxnDateTime;
        $postData = [
                "pp_Version" => config('constants.jazzcash.VERSION'),
                "pp_TxnType" => "MWALLET",
                "pp_Language" => config('constants.jazzcash.LANGUAGE'),
                "pp_MerchantID" => config('constants.jazzcash.MERCHANT_ID'),
                "pp_SubMerchantID" => '',
                "pp_Password" => config('constants.jazzcash.PASSWORD'),
                "pp_BankID" => "TBANK",
                "pp_ProductID" => "RETL",
                "pp_TxnRefNo" => $pp_TxnRefNo,
                "pp_Amount" => $pp_Amount,
                "pp_TxnCurrency" => config('constants.jazzcash.CURRENCY_CODE'),
                "pp_TxnDateTime" => $pp_TxnDateTime,
                "pp_BillReference" => "billRef",
                "pp_Description" => "Description of transaction",
                "pp_TxnExpiryDateTime" => $pp_TxnExpiryDateTime,
                "pp_ReturnURL" => config('constants.jazzcash.RETURN_URL'),
                "pp_SecureHash" => 'ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f',
                "ppmpf_1" => '1',
                "ppmpf_2" => '2',
                "ppmpf_3" => '3',
                "ppmpf_4" => '4',
                "ppmpf_5" => '5',
        ];
        
        JazzcashOrder::create([
            'TxnRefNo' => $pp_TxnRefNo,
            'amount' => $pp_Amount,
            'description' => "Description of transaction",
            'status' => "pending"
        ]);
        
        Session::put('post_data', $postData);
        
        echo '<pre>';
        print_r($postData);
        echo '</pre>';

        return view('jazzcash-checkout');
    }
}
