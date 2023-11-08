<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\BadResponseException;

use Twilio\Rest\Client;
use App\Models\BusinessSetting;



use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Module;
use App\Models\OrderTransaction;
use App\Models\Item;
use App\Models\StoreCatMap;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Vendor;
use App\Models\DeliveryMan;
use App\Models\GomtTransactions;
use App\Models\DataCon;
use App\Models\PhoneVerification;
// use App\Models\DeliveryMan;
// use App\Models\CustomerAddress;
use App\Models\Store;
use App\Models\StoreSchedule;
use App\Models\StoreWallet;
use App\Models\VendorEmployee;
use App\Models\Category;
use App\Models\PopUpUserMap;
use App\Models\PopUp;
use App\Models\UserFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Zone;
use Grimzy\LaravelMysqlSpatial\Types\Point;

use Illuminate\Support\Facades\File;
// $result = File::exists($myfile);
// use Storage;


class CustomController extends Controller
{
    
}





