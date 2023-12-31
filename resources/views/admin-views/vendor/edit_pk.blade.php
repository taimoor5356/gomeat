@extends('layouts.admin.app')

@section('title','Update restaurant info')

@push('css_or_js')
<style>
    #map{
        height: 100%;
    }
    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        #map{
            height: 200px;
        }
    }
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{translate('messages.update')}} {{translate('messages.store')}} (For PAK)</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.vendor.update',[$store['id']])}}" method="post" class="js-validate"
                      enctype="multipart/form-data" id="vendor_form">
                    @csrf
                    <small class="nav-subtitle text-secondary border-bottom">{{translate('messages.store')}} {{translate('messages.info')}}</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.module')}}</label>
                                <select name="module_id" required class="form-control js-select2-custom"  data-placeholder="{{translate('messages.select')}} {{translate('messages.module')}}" disabled>
                                        <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.module')}}</option>
                                    @foreach(\App\Models\Module::notParcel()->get() as $module)
                                        <option value="{{$module->id}}" {{$store->module_id==$module->id?'selected':''}}>{{$module->module_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label class="input-label">Select Country</label>
                            <select name="country_id" required id="country_id" class="form-control js-select2-custom" data-placeholder="Select Country">
                                <option value="" selected disabled>Select Country</option>
                                @foreach(\App\Models\Country::where('id', $store->country_id)->get() as $country)
                                <option value="{{$country->id}}" {{$store->country_id==$country->id?'selected':''}}>{{$country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label class="input-label">Select State</label>
                            <select name="state_id" required id="state_id" class="form-control js-select2-custom" data-placeholder="Select State">
                                <option>Select State</option>
                                @if(!empty($store->state_id))
                                    @foreach(\App\Models\CountryHasState::where('id', $store->state_id)->get() as $state)
                                    <option value="{{$store->state_id}}" {{$store->state_id==$state->id?'selected':''}}>{{$state->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-3">
                            <label class="input-label" for="name">{{translate('messages.store')}} {{translate('messages.name')}}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{translate('messages.store')}} {{translate('messages.name')}}"
                                    required value="{{$store->name}}">
                        </div>
                        <div class="form-group col-3">
                            <label class="input-label" for="legal_business_name">Legal Business Name</label>
                            <input type="text" name="legal_business_name" class="form-control" placeholder="Enter Legal Business Name" value="{{$store->legal_business_name}}">
                        </div>
                        <div class="form-group col-3">
                            <div class="d-flex justify-content-between">
                                <label class="input-label" for="ntn_number">Enter NTN number</label>
                                <div class="">
                                    FBR Registration
                                    <input type="checkbox" name="fbr_registration_status" id="fbr_registration_status" @if($store->fbr_registration_status == 'active') checked @endif value="active">
                                </div>
                            </div>
                            <input type="number" id="ntn_number" name="ntn_number" class="form-control strn_ntn_number" placeholder="Enter NTN number" value="{{$store->ntn_number}}">
                        </div>
                        <div class="form-group col-3">
                            <div class="form-group">
                                <label class="input-label" for="strn_number">Enter STRN</label>
                                <input type="number" id="strn_number" name="strn_number" class="form-control strn_ntn_number" placeholder="Enter STRN" value="{{$store->strn_number}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-12">
                            <div class="row store-data @if($store->module_id != '1') d-none @endif">
                                <!-- Store -->
                                <div class="form-group col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="store_online_payment">Store Online Payment</label>
                                        <input type="number" disabled id="store_online_payment" name="store_online_payment" class="form-control" placeholder="" value="@if(isset($store->state)){{$store->state->store_online_payment}}@endif">
                                    </div>
                                </div>

                                <div class="form-group col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="store_cash_payment">Store Cash Payment</label>
                                        <input type="number" disabled id="store_cash_payment" name="store_cash_payment" class="form-control" placeholder="" value="@if(isset($store->state)){{$store->state->store_cash_payment}}@endif">
                                    </div>
                                </div>
                                <!-- Store -->
                            </div>
                            <div class="row restaurant-data @if($store->module_id != '2') d-none @endif">
                                <!-- Restaurant -->
                                <div class="form-group col-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                            <label class="input-label" for="sales_tax_amount">Restaurant Online Payment %</label>
                                            <div class="">
                                                Filer
                                                <input type="checkbox" name="filer_status" id="filer_status" value="active" @if($store->filer_status == 'active') checked @endif>
                                            </div>
                                        </div>
                                        <input type="number" id="restaurant_online_payment" name="restaurant_online_payment" class="form-control restaurant_cash_amount_field" placeholder="" @if($store->filer_status == 'active') value="@isset($store->state){{$store->state->restaurant_online_payment}}@endisset" @else value="0" @endif>
                                    </div>
                                </div>

                                <div class="form-group col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="sales_tax_amount">Restaurant Cash Payment %</label>
                                        <input type="number" id="restaurant_cash_payment" name="restaurant_cash_payment" class="form-control restaurant_cash_amount_field" placeholder="" @if($store->filer_status == 'active') value="@isset($store->state){{$store->state->restaurant_cash_payment}}@endisset" @else value="0" @endif>
                                    </div>
                                </div>
                                <!-- Restaurant -->
                            </div>
                            <div class="row">
                                <div class="form-group col-4">
                                    <div class="form-group">
                                        <label class="input-label" for="gm_commission">GoMeat Commission % <small class="text-danger">(Inclusive of Tax)</small></label>
                                        <input type="number" name="gm_commission" class="form-control" placeholder="e.g 10.00" min="0" step=".01" value="{{$store->gm_commission}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group col-6">
                                    <label class="input-label" for="address">{{translate('messages.store')}} {{translate('messages.address')}}</label>
                                    <textarea  type="text" name="address" class="form-control" placeholder="{{translate('messages.store')}} {{translate('messages.address')}}"
                                        required>{{$store->address}}</textarea>
                                </div>
                                
                                <div class="form-group col-6">
                                    <label class="input-label">{{translate('messages.store')}} {{translate('messages.logo')}}<small style="color: red"> ( {{translate('messages.ratio')}} 1:1 )</small></label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{translate('messages.choose')}} {{translate('messages.file')}}</label>
                                    </div>
                                </div>
                                
                                <div class="form-group col-6">
                                    <label class="input-label" for="choice_zones">{{translate('messages.zone')}}<span
                                            class="input-label-secondary" title="{{translate('messages.select_zone_for_map')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.select_zone_for_map')}}"></span></label>
                                    <select name="zone_id" id="choice_zones" onchange="get_zone_data(this.value)" data-placeholder="{{translate('messages.select')}} {{translate('messages.zone')}}"
                                            class="form-control js-select2-custom">
                                        @foreach(\App\Models\Zone::active()->get() as $zone)
                                            @if(isset(auth('admin')->user()->zone_id))
                                                @if(auth('admin')->user()->zone_id == $zone->id)
                                                    <option value="{{$zone->id}}" {{$store->zone_id == $zone->id? 'selected': ''}}>{{$zone->name}}</option>
                                                @endif
                                            @else
                                                <option value="{{$zone->id}}" {{$store->zone_id == $zone->id? 'selected': ''}}>{{$zone->name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6 col-6 d-flex justify-content-end">
                                    <div class="form-group" style="margin-bottom:0%;">                       
                                        <center>
                                            <img style="height: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                                src="{{asset('storage/app/public/store').'/'.$store->logo}}" alt="{{$store->name}}"/>
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.latitude')}}<span
                                        class="input-label-secondary" title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                <input type="text"
                                       name="latitude" class="form-control" id="latitude"
                                       placeholder="Ex : -94.22213" value="{{$store->latitude}}" readonly>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.longitude')}}<span
                                        class="input-label-secondary" title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                <input type="text"
                                       name="longitude" class="form-control" id="longitude"
                                       placeholder="Ex : 103.344322" value="{{$store->longitude}}" readonly>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="radius">Store Radius
                                        </label>
                                <input type="text"
                                       name="radius" class="form-control" id="radius"
                                       placeholder="Ex : 20" value="{{$store->radius}}" >
                            </div>
                        </div>
                        <div class="col-md-8 col-8 mt-5">
                            <input id="pac-input" class="controls rounded" style="height: 3em;width:fit-content;"
                                title="{{ translate('messages.search_your_location_here') }}" type="text"
                                placeholder="{{ translate('messages.search_here') }}" />
                            <div id="map"></div>
                        </div>

                        {{--<div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="">
                                    <i class="tio-info-outined"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="This value is the radius from your restaurant location, and customer can order food inside  the circle calculated by this radius."></i>
                                    {{translate('messages.coverage')}} ( {{translate('messages.km')}} )
                                </label>
                                <input type="number" value=""
                                       name="coverage" class="form-control" placeholder="Ex : 3">
                            </div>
                        </div>--}}
                    </div>
                    <div class="form-group">
                        <label for="name">{{translate('messages.upload')}} {{translate('messages.cover')}} {{translate('messages.photo')}} <span class="text-danger">({{translate('messages.ratio')}} 2:1)</span></label>
                        <div class="custom-file">
                            <input type="file" name="cover_photo" id="coverImageUpload" class="custom-file-input"
                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileUpload">{{translate('messages.choose')}} {{translate('messages.file')}}</label>
                        </div>
                    </div> 
                    <center>
                        <img style="max-width: 100%;border: 1px solid; border-radius: 10px; max-height:200px;" id="coverImageViewer"
                        onerror="this.src='{{asset('public/assets/admin/img/900x400/img1.jpg')}}'"
                        src="{{asset('storage/app/public/store/cover/'.$store->cover_photo)}}" alt="Product thumbnail"/>
                    </center>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-6">
                            <div class="form-group">
                                <label class="input-label" for="minimum_order">Minimum Order Amount</label>
                                <input type="text" name="minimum_order" class="form-control" placeholder="e.g 10.00" min="0" required value="{{$store->minimum_order}}">
                            </div>
                        </div>
                        <div class="col-md-6 col-6">
                            @php

                                $delivery_time_start = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode('-',$store->delivery_time)[0]:10;
                                $delivery_time_end = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode(' ',explode('-',$store->delivery_time)[1])[0]:30;
                                $delivery_time_type = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode(' ',explode('-',$store->delivery_time)[1])[1]:'min';
                            @endphp
                            <div class="form-group">
                                <label class="input-label" for="maximum_delivery_time">{{translate('messages.approx_delivery_time')}}</label>
                                <div class="input-group">
                                    <input type="number" name="minimum_delivery_time" class="form-control" placeholder="Min: 10" value="{{$delivery_time_start}}" title="{{translate('messages.minimum_delivery_time')}}">
                                    <input type="number" name="maximum_delivery_time" class="form-control" placeholder="Max: 20" value="{{$delivery_time_end}}" title="{{translate('messages.maximum_delivery_time')}}">
                                    <select name="delivery_time_type" class="form-control text-capitalize" id="" required>
                                        <option value="min" {{$delivery_time_type=='min'?'selected':''}}>{{translate('messages.minutes')}}</option>
                                        <option value="hours" {{$delivery_time_type=='hours'?'selected':''}}>{{translate('messages.hours')}}</option>
                                        <option value="days" {{$delivery_time_type=='days'?'selected':''}}>{{translate('messages.days')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <small class="nav-subtitle text-secondary border-bottom">Owner Info</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.first')}} {{translate('messages.name')}}</label>
                                <input type="text" name="f_name" class="form-control" placeholder="{{translate('messages.first')}} {{translate('messages.name')}}" value="{{$store->vendor->f_name}}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.last')}} {{translate('messages.name')}}</label>
                                <input type="text" name="l_name" class="form-control" placeholder="{{translate('messages.last')}} {{translate('messages.name')}}"
                                value="{{$store->vendor->l_name}}"  required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.phone')}}</label>
                                <input type="text" name="phone" class="form-control" placeholder="Ex : 017********"
                                value="{{$store->phone}}"   required>
                            </div>
                        </div>
                    </div>
                    <br>
                    <small class="nav-subtitle text-secondary border-bottom">Bank Info</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="account_title">Account Title</label>
                                <input type="text" name="account_title" class="form-control" placeholder="Enter Account Title" value="{{$store->account_title}}">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="bank_name">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Enter Bank Name" value="{{$store->bank_name}}">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="bank_iban">IBAN</label>
                                <input type="text" name="bank_iban" class="form-control" placeholder="Enter IBAN Number" value="{{$store->bank_iban}}">
                            </div>
                        </div>
                    </div>
                    <br>
                    <small class="nav-subtitle text-secondary border-bottom">{{translate('messages.login')}} {{translate('messages.info')}}</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.email')}}</label>
                                <input type="email" name="email" class="form-control" placeholder="Ex : ex@example.com" value="{{$store->email}}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="js-form-message form-group">
                                <label class="input-label" for="signupSrPassword">Password</label>

                                <div class="input-group input-group-merge">
                                    <input type="password" class="js-toggle-password form-control" name="password" id="signupSrPassword" placeholder="{{translate('messages.password_length_placeholder',['length'=>'6+'])}}" aria-label="6+ characters required"
                                    data-msg="Your password is invalid. Please try again."
                                    data-hs-toggle-password-options='{
                                    "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                    "defaultClass": "tio-hidden-outlined",
                                    "showClass": "tio-visible-outlined",
                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                    }'>
                                    <div class="js-toggle-password-target-1 input-group-append">
                                        <a class="input-group-text" href="javascript:;">
                                            <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="js-form-message form-group">
                                <label class="input-label" for="signupSrConfirmPassword">Confirm password</label>

                                <div class="input-group input-group-merge">
                                <input type="password" class="js-toggle-password form-control" name="confirmPassword" id="signupSrConfirmPassword" placeholder="{{translate('messages.password_length_placeholder', ['length'=>'6+'])}}" aria-label="6+ characters required"                                      data-msg="Password does not match the confirm password."
                                        data-hs-toggle-password-options='{
                                        "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                        }'>
                                <div class="js-toggle-password-target-2 input-group-append">
                                    <a class="input-group-text" href="javascript:;">
                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                    </a>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">{{translate('messages.submit')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+viewer).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function () {
            readURL(this, 'coverImageViewer');
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=places&callback=initMap&v=3.45.8"></script>
    <script> 
        let myLatlng = { lat: {{$store->latitude}}, lng: {{$store->longitude}} };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: myLatlng,
        });
        var zonePolygon = null;
        let infoWindow = new google.maps.InfoWindow({
                content: "Click the map to get Lat/Lng!",
                position: myLatlng,
            });
        var bounds = new google.maps.LatLngBounds();
        function initMap() {
            // Create the initial InfoWindow.
            new google.maps.Marker({
                position: { lat: {{$store->latitude}}, lng: {{$store->longitude}} },
                map,
                title: "{{$store->name}}",
            });
            infoWindow.open(map);    
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (places.length == 0) {
                return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                marker.setMap(null);
                });
                markers = [];
                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    const icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25),
                    };
                    // Create a marker for each place.
                    markers.push(
                        new google.maps.Marker({
                        map,
                        icon,
                        title: place.name,
                        position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });          
        }
        initMap();
        function get_zone_data(id)
        {
            $.get({
                url: '{{url('/')}}/admin/zone/get-coordinates/'+id,
                dataType: 'json',
                success: function (data) {
                    if(zonePolygon)
                    {
                        zonePolygon.setMap(null);
                    }
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function (mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                        content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });    
                },
            });
        }
        $(document).on('ready', function (){

            $(document).on('click', '#fbr_registration_status', function () {
                if ($(this).is(':checked')) {
                    $('.strn_ntn_number').attr('readonly', false);
                } else {
                    $('.strn_ntn_number').attr('readonly', true);
                    $('.strn_ntn_number').val('');
                }
            });

            $(document).on('change', '#filer_status', function () {
                let _this = $(this);
                if (_this.is(':checked')) {
                    // $('.restaurant_cash_amount_field').prop('disabled', false);
                } else {
                    // $('.restaurant_cash_amount_field').prop('disabled', true);
                }
            });

            $(document).on('change', '#country_id', function () {
                $('#store_cash_payment').val(0);
                $('#store_online_payment').val(0);
                $('#restaurant_cash_payment').val(0);
                $('#restaurant_online_payment').val(0);
                var _this = $(this);
                var url = "{{route('admin.countries.show', [':id'])}}";
                url = url.replace(':id', _this.val());
                $.ajax({
                    url: url,
                    success:function(response) {
                        var states = response.states;
                        var _html = "<option>Select State</option>";
                        states.forEach(state => {
                            _html += `<option value="`+state.id+`">`+state.name+`</option>`;
                        });
                        $('#state_id').html(_html);
                    }
                });
            });
            var id = $('#choice_zones').val();
            $.get({
                url: '{{url('/')}}/admin/zone/get-coordinates/'+id,
                dataType: 'json',
                success: function (data) {
                    if(zonePolygon)
                    {
                        zonePolygon.setMap(null);
                    }
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    zonePolygon.getPaths().forEach(function(path) {
                        path.forEach(function(latlng) {
                            bounds.extend(latlng);
                            map.fitBounds(bounds);
                        });
                    });
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function (mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                        content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });    
                },
            });
        });
    </script>
<script>
      $(document).on('ready', function () {
        // INITIALIZATION OF SHOW PASSWORD
        // =======================================================
        $('.js-toggle-password').each(function () {
          new HSTogglePassword(this).init()
        });


        // INITIALIZATION OF FORM VALIDATION
        // =======================================================
        $('.js-validate').each(function() {
          $.HSCore.components.HSValidation.init($(this), {
            rules: {
              confirmPassword: {
                equalTo: '#signupSrPassword'
              }
            }
          });
        });

        get_zone_data({{$store->zone_id}});
      });
        $("#vendor_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })
    </script>
@endpush
