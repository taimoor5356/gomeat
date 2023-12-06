@extends('store_owner_views.layouts.app')
@section('page_title', 'Store Edit - GoMeat')
@section('styles_css')
<style>
    {!! file_get_contents(resource_path('views/store_owner_views/store/styles.css')) !!}
</style>
@endsection
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="user-profile-header-banner">
                        <img src="{{asset('public/assets/store_owner/img/pages/product-image.jpg')}}" alt="Banner image" class="rounded-top">
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img src="{{asset('public/assets/store_owner/img/pages/product-image.jpg')}}" alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img">
                        </div>
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
                                    <!-- 1. Delivery Address -->
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h6>Store Info</h6>
                                            <hr class="mt-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="fullname">System Module</label>
                                            <select name="" id="" class="form-control">
                                                <option value="" selected>Select System Module</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="email">Country</label>
                                            <!-- <div class="input-group input-group-merge"> -->
                                            <select name="" id="" class="form-control">
                                                <option value="" selected>Select Country</option>
                                            </select>
                                            <!-- <span class="input-group-text" id="email3">@example.com</span> -->
                                            <!-- </div> -->
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="email">State</label>
                                            <select name="" id="" class="form-control">
                                                <option value="" selected>Select State</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Store Name</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Store Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Legal Business Name</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Legal Business Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">NTN Number</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter NTN Number">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Online Payment %</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Online Payment Percentage">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Cash Payment %</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Cash Payment Percentage">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">GoMeat Commission % <small class="text-danger">(inclusive of TAX)</small></label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter GoMeat Commission Percentage">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="address">Address</label>
                                            <textarea name="address" class="form-control" id="address" rows="1" placeholder="Enter Address"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="pincode">Zone</label>
                                            <select name="" id="" class="form-control">
                                                <option value="" selected>Select Zone</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="landmark">Latitude</label>
                                            <input type="text" id="landmark" class="form-control" placeholder="Enter Latitude">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="city">Longitude</label>
                                            <input type="text" id="city" class="form-control" placeholder="Enter Longitude">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Store Radius</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Store Radius">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Minimum Order Amount</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Minimum Order Amount">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Approx Delivery Time</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Approx Delivery Time">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">User First Name</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter User First Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">User Last Name</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter User Last Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Phone</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Phone Number">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Account Title</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Account Title">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Bank Name</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Bank Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">IBAN</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter IBAN">
                                        </div>
                                        <div class="col-12">
                                            <h6>User Login Info</h6>
                                            <hr class="mt-0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Email</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Email Address">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Password</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Password">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="alt-num">Confirm Password</label>
                                            <input type="text" id="alt-num" class="form-control phone-mask" placeholder="Enter Password Again To Confirm">
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