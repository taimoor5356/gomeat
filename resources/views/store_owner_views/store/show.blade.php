@extends('store_owner_views.layouts.app')
@section('page_title', 'Store Edit - GoMeat')
@section('styles_css')
<style>
    {!! file_get_contents(resource_path('views/store_owner_views/store/styles.css')) !!}

    #upload-container {
        position: relative;
        display: inline-block;
    }

    #upload-image {
        cursor: pointer;
    }

    #file-input {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endsection
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="user-profile-header-banner">
                        <img src="{{asset('public/assets/store_owner/img/pages/product-image.jpg')}}" alt="Banner image" class="rounded-top">
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <label for="file-input">
                                <img src="{{asset('public/assets/store_owner/img/pages/product-image.jpg')}}" alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" id="upload-image">
                            </label>
                            <input type="file" id="file-input" style="display: none;">
                        </div>
                        <!-- <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <label for="fileInput" class="user-profile-img">
                                <img src="{{asset('public/assets/store_owner/img/pages/product-image.jpg')}}" alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded">
                                <input type="file" id="fileInput">
                            </label>
                        </div> -->
                        <div class="flex-grow-1 mt-3 mt-sm-5">
                            <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4>{{$shop->name}}</h4>
                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                        <li class="list-inline-item fw-medium">
                                            <i class="bx bx-user"></i> Store Owner
                                        </li>
                                        <li class="list-inline-item fw-medium">
                                            <i class="bx bx-phone"></i> {{$shop->phone}}
                                        </li>
                                        <li class="list-inline-item fw-medium">
                                            <i class="bx bx-map"></i> {{$shop->state->name}}
                                        </li>
                                        <!-- <li class="list-inline-item fw-medium">
                                            <i class="bx bx-calendar-alt"></i> Joined April 2021
                                        </li> -->
                                    </ul>
                                </div>
                                <div>
                                    @if($shop->status == 1)
                                    <button type="button" class="btn btn-success text-nowrap">
                                        <i class="bx bx-check-circle me-1"></i> Store Approved
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-danger text-nowrap">
                                        <i class="bx bx-x-circle me-1"></i> Store Not Approved
                                    </button>
                                    @endif
                                    @if($shop->filer_status == 'active')
                                    <button type="button" class="btn btn-success text-nowrap">
                                        <i class="bx bx-message-alt-check me-1"></i> Filer
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-danger text-nowrap">
                                        <i class="bx bx-message-alt-x me-1"></i> Non-Filer
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-4">Edit Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 mx-auto">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h6>Store Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="module_id">System Module</label>
                                            <select name="module_id" id="module_id" class="form-control select2">
                                                <option value="">Select System Module</option>
                                                <option selected value="{{$shop->module_id}}">{{$shop->module->module_name}}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="country_id">Country</label>
                                            <select name="country_id" id="country_id" class="form-control select2">
                                                <option value="">Select Country</option>
                                                <option value="{{$shop->country_id}}" selected>{{$shop->country->name}}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="state_id">State</label>
                                            <select name="state_id" id="state_id" class="form-control select2">
                                                <option value="" selected>Select State</option>
                                                <option value="{{$shop->state_id}}" selected>{{$shop->state->name}}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="name">Store Name</label>
                                            <input type="text" name="name" id="name" class="form-control name" placeholder="Enter Store Name" value="{{$shop->name}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="legal_business_name">Legal Business Name</label>
                                            <input type="text" name="legal_business_name" id="legal_business_name" class="form-control legal_business_name" placeholder="Enter Legal Business Name" value="{{$shop->legal_business_name}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="ntn_number">NTN Number</label>
                                            <input type="text" name="ntn_number" id="ntn_number" class="form-control ntn_number" placeholder="Enter NTN Number" value="{{$shop->ntn_number}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="online_payment">Online Payment %</label>
                                            <input type="text" name="online_payment" id="online_payment" class="form-control " placeholder="Enter Online Payment Percentage" value="@if($shop->module_id == '1'){{$shop->state->store_online_payment}}@elseif($shop->module_id == '2'){{$shop->state->restaurant_online_payment}}@endif">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="cash_payment">Cash Payment %</label>
                                            <input type="text" name="cash_payment" id="cash_payment" class="form-control " placeholder="Enter Cash Payment Percentage" value="@if($shop->module_id == '1'){{$shop->state->store_cash_payment}}@elseif($shop->module_id == '2'){{$shop->state->restaurant_cash_payment}}@endif">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="gm_commission">GoMeat Commission % <small class="text-danger">(inclusive of TAX)</small></label>
                                            <input type="text" name="gm_commission" id="gm_commission" class="form-control " placeholder="Enter GoMeat Commission Percentage" value="{{$shop->gm_commission}}">
                                        </div>
                                        <div class="col-12 mt-5">
                                            <h6>Address / Location Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="address">Address</label>
                                            <textarea name="address" class="form-control" id="address" rows="1" placeholder="Enter Address">{{$shop->address}}</textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="zone_id">Zone</label>
                                            <select name="zone_id" id="zone_id" class="form-control">
                                                <option value="">Select Zone</option>
                                                <option value="{{$shop->zone_id}}" selected>{{$shop->zone->name}}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="latitude">Latitude</label>
                                            <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Enter Latitude" value="{{$shop->latitude}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="longitude">Longitude</label>
                                            <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Enter Longitude" value="{{$shop->longitude}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="store_radius">Store Radius</label>
                                            <input type="text" name="store_radius" id="store_radius" class="form-control " placeholder="Enter Store Radius" value="{{$shop->radius}}">
                                        </div>
                                        <div class="col-12 mt-5">
                                            <h6>Order Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="min_order_amount">Minimum Order Amount</label>
                                            <input type="text" name="min_order_amount" id="min_order_amount" class="form-control " placeholder="Enter Minimum Order Amount" value="{{$shop->minimum_order}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="approx_delivery_time">Approx Delivery Time</label>
                                            <input type="text" name="approx_delivery_time" id="approx_delivery_time" class="form-control " placeholder="Enter Approx Delivery Time" value="{{$shop->delivery_time}}">
                                        </div>
                                        <div class="col-12 mt-5">
                                            <h6>User Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="user_first_name">User First Name</label>
                                            <input type="text" name="user_first_name" id="user_first_name" class="form-control " placeholder="Enter User First Name" value="{{$shop->vendor->f_name}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="user_last_name">User Last Name</label>
                                            <input type="text" name="user_last_name" id="user_last_name" class="form-control " placeholder="Enter User Last Name" value="{{$shop->vendor->l_name}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="text" name="phone" id="phone" class="form-control " placeholder="Enter Phone Number" value="{{$shop->vendor->phone}}">
                                        </div>
                                        <div class="col-12 mt-5">
                                            <h6>Bank Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="account_title">Account Title</label>
                                            <input type="text" name="account_title" id="account_title" class="form-control " placeholder="Enter Account Title" value="{{$shop->account_title}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="bank_name">Bank Name</label>
                                            <input type="text" name="bank_name" id="bank_name" class="form-control " placeholder="Enter Bank Name" value="{{$shop->bank_name}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="iban">IBAN</label>
                                            <input type="text" name="iban" id="iban" class="form-control " placeholder="Enter IBAN" value="{{$shop->bank_iban}}">
                                        </div>
                                        <div class="col-12 mt-5">
                                            <h6>User Login Info</h6>
                                            <hr class="my-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="text" name="email" id="email" class="form-control " placeholder="Enter Email Address" value="{{$shop->vendor->email}}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="password">Password</label>
                                            <input type="text" name="password" id="password" class="form-control " placeholder="Enter Password">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="confirm_password">Confirm Password</label>
                                            <input type="text" name="confirm_password" id="confirm_password" class="form-control " placeholder="Enter Password Again To Confirm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="content-backdrop fade"></div>
</div>
@endsection
@section('scripts')
@include('store_owner_views.store._js')
@endsection