@extends('layouts.admin.app')

@section('title','Clone Store')

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
        <div class="page-header" style="border-bottom:0;padding-bottom:0;">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> Clone Store</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.vendor.store-clone')}}" method="post" enctype="multipart/form-data" class="js-validate" id="vendor_form">
                    @csrf

                    <small class="nav-subtitle text-secondary border-bottom">Store Info</small>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            {{--<div class="form-group">
                                <label class="input-label">{{translate('messages.module')}}</label>
                                <select name="module_id" required
                                        class="form-control js-select2-custom"  data-placeholder="{{translate('messages.select')}} {{translate('messages.module')}}" onchange="modulChange(this.value)">
                                        <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.module')}}</option>
                                    @foreach(\App\Models\Module::notParcel()->get() as $module)
                                        <option value="{{$module->id}}">{{$module->module_name}}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger">{{translate('messages.module_change_warning')}}</small>
                            </div>--}}
                            <div class="form-group">
                                <label class="input-label" for="store_tobe_clone">Store To be Cloned<span
                                        class="input-label-secondary"></span></label>
                                <select name="store_tobe_clone" id="store_tobe_clone" class="js-data-example-ajax form-control" required data-placeholder="{{translate('messages.select')}} {{translate('messages.store')}}" >
                                <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.store')}}</option>    
                                </select>
                            </div>
                            {{--<div class="form-group">
                                <label class="input-label" for="number_of_clones">Number of clones</label>
                                <input type="number" name="number_of_clones" class="form-control" placeholder="Number of clones" value="{{old('number_of_clones')}}" required>
                            </div>--}}
                            <div class="form-group">
                                <label class="input-label" for="name">{{translate('messages.store')}} {{translate('messages.name')}}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{translate('messages.store')}} {{translate('messages.name')}}" value="{{old('name')}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="address">{{translate('messages.store')}} {{translate('messages.address')}}</label>
                                <textarea type="text" name="address" class="form-control" placeholder="{{translate('messages.store')}} {{translate('messages.address')}}" required >{{old('address')}}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="gm_commission">GoMeat Commission %</label>
                                <input type="number" name="gm_commission" class="form-control" placeholder="e.g 10.00" min="0" step=".01" required value="{{old('gm_commission')}}">
                            </div>
                            <!-- <div class="form-group">
                                <label class="input-label" for="maximum_delivery_time">{{translate('messages.approx_delivery_time')}}</label>
                                <div class="input-group">
                                    <input type="number" name="minimum_delivery_time" class="form-control" placeholder="Min: 10" value="{{old('minimum_delivery_time')}}">
                                    <input type="number" name="maximum_delivery_time" class="form-control" placeholder="Max: 20" value="{{old('maximum_delivery_time')}}">
                                    <select name="delivery_time_type" class="form-control text-capitalize" id="" required>
                                        <option value="min">{{translate('messages.minutes')}}</option>
                                        <option value="hours">{{translate('messages.hours')}}</option>
                                        <option value="days">{{translate('messages.days')}}</option>
                                    </select>
                                </div>
                            </div> -->

                            <div class="form-group">
                                <label class="input-label">{{translate('messages.store')}} {{translate('messages.logo')}}<small style="color: red"> ( {{translate('messages.ratio')}} 1:1 )</small></label>
                                <div class="custom-file">
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                    <label class="custom-file-label" for="logo">{{translate('messages.choose')}} {{translate('messages.file')}}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12" style="margin-top: auto;margin-bottom: auto;">
                            <div class="form-group" style="margin-bottom:0%;">                       
                                <center>
                                    <img style="height: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                        src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt="{{translate('store_logo')}}"/>
                                </center>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="choice_zones">{{translate('messages.zone')}}<span
                                        class="input-label-secondary" title="{{translate('messages.select_zone_for_map')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.select_zone_for_map')}}"></span></label>
                                <select name="zone_id" id="choice_zones" required
                                        class="form-control js-select2-custom"  data-placeholder="{{translate('messages.select')}} {{translate('messages.zone')}}">
                                        <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.zone')}}</option>
                                    @foreach(\App\Models\Zone::active()->get() as $zone)
                                        @if(isset(auth('admin')->user()->zone_id))
                                            @if(auth('admin')->user()->zone_id == $zone->id)
                                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                                            @endif
                                        @else
                                        <option value="{{$zone->id}}">{{$zone->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="latitude">{{translate('messages.latitude')}}<span
                                        class="input-label-secondary" title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                <input type="text" id="latitude"
                                       name="latitude" class="form-control"
                                       placeholder="Ex : -94.22213" value="{{old('latitude')}}" required readonly>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="longitude">{{translate('messages.longitude')}}<span
                                        class="input-label-secondary" title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                <input type="text" 
                                       name="longitude" class="form-control"
                                       placeholder="Ex : 103.344322" id="longitude" value="{{old('longitude')}}" required readonly>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="radius">Store Radius<span
                                        class="input-label-secondary">
                                        </label>
                                <input type="text" 
                                       name="radius" class="form-control"
                                       placeholder="Ex : 20" id="radius" value="{{old('radius')}}" required>
                            </div>
                        </div>

                        <div class="col-md-8 col-12">
                            <input id="pac-input" class="controls rounded" style="height: 3em;width:fit-content;"
                                title="{{ translate('messages.search_your_location_here') }}" type="text"
                                placeholder="{{ translate('messages.search_here') }}" />
                            <div id="map"></div>
                        </div>
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
                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="Product thumbnail"/>
                    </center>  
                    <br>
                    <small class="nav-subtitle text-secondary border-bottom">{{translate('messages.owner')}} {{translate('messages.info')}}</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="f_name">{{translate('messages.first')}} {{translate('messages.name')}}</label>
                                <input type="text" name="f_name" class="form-control" placeholder="{{translate('messages.first')}} {{translate('messages.name')}}"
                                     value="{{old('f_name')}}"  required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="l_name">{{translate('messages.last')}} {{translate('messages.name')}}</label>
                                <input type="text" name="l_name" class="form-control" placeholder="{{translate('messages.last')}} {{translate('messages.name')}}"
                                value="{{old('l_name')}}"  required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="phone">{{translate('messages.phone')}}</label>
                                <input type="text" name="phone" class="form-control" placeholder="Ex : 017********"
                                value="{{old('phone')}}" required>
                            </div>
                        </div>
                    </div>
                    <br>
                    
                    <small class="nav-subtitle text-secondary border-bottom">{{translate('messages.login')}} {{translate('messages.info')}}</small>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="email">{{translate('messages.email')}}</label>
                                <input type="email" name="email" class="form-control" placeholder="Ex : ex@example.com"
                                value="{{old('email')}}"  required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="js-form-message form-group">
                                <label class="input-label" for="signupSrPassword">{{translate('messages.password')}}</label>

                                <div class="input-group input-group-merge">
                                    <input type="password" class="js-toggle-password form-control" name="password" id="signupSrPassword" placeholder="{{translate('messages.password_length_placeholder',['length'=>'5+'])}}" aria-label="{{translate('messages.password_length_placeholder',['length'=>'5+'])}}" required
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
                                <label class="input-label" for="signupSrConfirmPassword">{{translate('messages.confirm_password')}}</label>

                                <div class="input-group input-group-merge">
                                <input type="password" class="js-toggle-password form-control" name="confirmPassword" id="signupSrConfirmPassword" placeholder="{{translate('messages.password_length_placeholder',['length'=>'5+'])}}" aria-label="{{translate('messages.password_length_placeholder',['length'=>'5+'])}}" required
                                        data-msg="Password does not match the confirm password."
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
                    
                    <button type="submit" class="btn btn-primary">{{translate('messages.submit')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
      $(document).on('ready', function () {
        @if (isset(auth('admin')->user()->zone_id))
            $('#choice_zones').trigger('change');
        @endif
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
      });
    </script>
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

    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/admin/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate('messages.please_only_input_png_or_jpg_type_file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate('messages.file_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=places&callback=initMap&v=3.45.8"></script>
    <script> 
        @php($default_location=\App\Models\BusinessSetting::where('key','default_location')->first())
        @php($default_location=$default_location->value?json_decode($default_location->value, true):0)
        let myLatlng = { lat: {{$default_location?$default_location['lat']:'23.757989'}}, lng: {{$default_location?$default_location['lng']:'90.360587'}} };
        let map = new google.maps.Map(document.getElementById("map"), {
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
            infoWindow.open(map);
             //get current location block
             infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                    myLatlng = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    infoWindow.setPosition(myLatlng);
                    infoWindow.setContent("Location found.");
                    infoWindow.open(map);
                    map.setCenter(myLatlng);
                },
                () => {
                    handleLocationError(true, infoWindow, map.getCenter());
                    }
                );
            } else {
            // Browser doesn't support Geolocation
                handleLocationError(false, infoWindow, map.getCenter());
            }
            //-----end block------
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
        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(
                browserHasGeolocation
                ? "Error: The Geolocation service failed."
                : "Error: Your browser doesn't support geolocation."
            );
            infoWindow.open(map);
        }
        $('#choice_zones').on('change', function(){
            var id = $(this).val();
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
        $("#vendor_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })
    </script>

    <script>
        var module_id = 0;
        var parent_category_id = 0;
        var module_data = null;
        var stock = true;

        function modulChange(id)
        {
            // $("#store_tobe_clone").val("");
            // $("#store_tobe_clone").val($("#store_tobe_clone").data("default-value"));
            // alert(id);
            // $("#store_tobe_clone").prop('selectedIndex', 0);
            // document.getElementById('store_tobe_clone').selectedIndex = 0;
            // $.get({
            //     url: "{{url('/')}}/admin/module/"+id,
            //     dataType: 'json',
            //     success: function (data) {
            //         module_data = data.data;
            //         stock = module_data.stock;
            //         module_type = data.type;
            //     },
            // });
            module_id = id;
        }
        $(document).on('ready', function () {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('#store_tobe_clone').select2({
            ajax: {
                url: '{{url('/')}}/admin/vendor/get-stores',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        // module_id: module_id
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    var $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
    </script>
@endpush
