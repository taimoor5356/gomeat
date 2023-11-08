@extends('layouts.vendor.app')

@section('title','Update Item')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
@endpush

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{translate('messages.item')}} {{translate('messages.update')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="javascript:" method="post" id="product_form"
                      enctype="multipart/form-data">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'bn')
                    @if($language)
                        @php($default_lang = json_decode($language)[0])
                        <ul class="nav nav-tabs mb-4">
                            @foreach(json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link {{$lang == $default_lang? 'active':''}}" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                </li>
                            @endforeach
                        </ul>
                        @foreach(json_decode($language) as $lang)
                            <?php
                                if(count($product['translations'])){
                                    $translate = [];
                                    foreach($product['translations'] as $t)
                                    {
                                        if($t->locale == $lang && $t->key=="name"){
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                        if($t->locale == $lang && $t->key=="description"){
                                            $translate[$lang]['description'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            <div class="card p-4 {{$lang != $default_lang ? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                <div class="form-group">
                                    <label class="input-label" for="{{$lang}}_name">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                    <input type="text" {{$lang == $default_lang? 'required':''}} name="name[]" id="{{$lang}}_name" class="form-control" placeholder="{{translate('messages.new_item')}}" value="{{$translate[$lang]['name']??$product['name']}}" oninvalid="document.getElementById('en-link').click()">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="form-group pt-4">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short')}} {{translate('messages.description')}} ({{strtoupper($lang)}})</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor">{!! $translate[$lang]['description']??$product['description'] !!}</textarea>
                                </div>
                            </div>
                        @endforeach
                    @else
                    <div class="card p-4" id="{{$default_lang}}-form">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} (EN)</label>
                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_item')}}" value="{{$product['name']}}" required>
                        </div>
                        <input type="hidden" name="lang[]" value="en">
                        <div class="form-group pt-4">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short')}} {{translate('messages.description')}}</label>
                            <textarea type="text" name="description[]" class="form-control ckeditor">{!! $product['description'] !!}</textarea>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.price')}}</label>
                                <input type="number" value="{{$product['price']}}" min="0" max="100000" name="price"
                                       class="form-control" step="0.01"
                                       placeholder="Ex : 100" required>
                            </div>
                        </div>
                        
                        {{-- <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}}</label>
                                <input type="number" min="0" value="{{$product['discount']}}" max="100000"
                                       name="discount" class="form-control"
                                       placeholder="Ex : 100">
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} {{translate('messages.type')}}</label>
                                <select name="discount_type" class="form-control js-select2-custom">
                                    <option value="percent" {{$product['discount_type']=='percent'?'selected':''}}>
                                        {{translate('messages.percent')}}
                                    </option>
                                    <option value="amount" {{$product['discount_type']=='amount'?'selected':''}}>
                                        {{translate('messages.amount')}}
                                    </option>
                                </select>
                            </div>
                        </div> --}}
                    </div>


                    <div class="row mt-2">
                        @if ($module_data['stock'])
                        <div class="col-12">
                            <div class="form-group">
                                {{-- <label class="input-label" for="total_stock">{{translate('messages.total_stock')}}</label>                                 --}}
                                <input type="hidden" class="form-control" name="current_stock" value="1000" id="quantity">
                            </div>
                        </div>
                        @endif
                        @if ($module_data['add_on'])
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.addon')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="addon_ids[]" class="form-control js-select2-custom" multiple="multiple">
                                    @foreach(\App\Models\AddOn::where('store_id', \App\CentralLogics\Helpers::get_store_id())->orderBy('name')->get() as $addon)
                                        <option
                                            value="{{$addon['id']}}" {{in_array($addon->id,json_decode($product['add_ons'],true))?'selected':''}}>{{$addon['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                    </div>
                    @if ($module_data['item_available_time'])
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.starts')}}</label>
                                <input type="time" value="{{$product['available_time_starts']}}" 
                                       name="available_time_starts" class="form-control"
                                       placeholder="Ex : 10:30 am" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.ends')}}</label>
                                <input type="time" value="{{$product['available_time_ends']}}"
                                       name="available_time_ends" class="form-control" placeholder="5:45 pm"
                                       required>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row">
                        @if ($module_data['unit'])
                            <div class="col-6" id="unit_input">
                                <div class="form-group">
                                    <label class="input-label text-capitalize" for="unit">{{translate('messages.unit')}}</label>
                                    <select name="unit" class="form-control js-select2-custom">
                                        @foreach (\App\Models\Unit::all() as $unit)
                                            <option value="{{$unit->id}}" {{$unit->id == $product->unit_id? 'selected':''}}>{{$unit->unit}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                            
                        @endif
                        @if ($module_data['veg_non_veg'])
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item_type')}}</label>
                                    <select name="veg" class="form-control js-select2-custom">
                                        <option value="0" {{$product['veg']==0?'selected':''}}>{{translate('messages.non_veg')}}</option>
                                        <option value="1" {{$product['veg']==1?'selected':''}}>{{translate('messages.veg')}}</option>
                                    </select>
                                </div>
                            </div>                            
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.category')}}<span
                                        class="input-label-secondary">*</span></label>
                                <select name="category_id" id="category-id" class="form-control js-select2-custom"
                                        onchange="getRequest('{{url('/')}}/vendor-panel/item/get-categories?parent_id='+this.value,'sub-categories')">
                                    @foreach($categories as $category)
                                        <option
                                            value="{{$category['id']}}" {{ $category->id==$product_category[0]->id ? 'selected' : ''}} >{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.sub_category')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="sub_category_id" id="sub-categories"
                                        data-id="{{count($product_category)>=2?$product_category[1]->id:''}}"
                                        class="form-control js-select2-custom"
                                        onchange="getRequest('{{url('/')}}/vendor-panel/item/get-categories?parent_id='+this.value,'sub-sub-categories')">

                                </select>
                            </div>
                        </div>
                        {{--<div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Sub Sub Category<span
                                        class="input-label-secondary"></span></label>
                                <select name="sub_sub_category_id" id="sub-sub-categories"
                                        data-id="{{count($product_category)>=3?$product_category[2]->id:''}}"
                                        class="form-control js-select2-custom">

                                </select>
                            </div>
                        </div>--}}
                    </div>
                   
                    <!-- variants start -->
                    <div class="col-lg-12" id="food_variation_section">
                        <div class="card shadow--card-2 border-0">
                            <div class="card-header flex-wrap">
                                <h5 class="card-title">
                                    <span class="card-header-icon mr-2">
                                        <i class="tio-canvas-text"></i>
                                    </span>
                                    <span>Variants</span>
                                </h5>
                                <a class="btn text--primary-2" id="add_new_option_button">
                                    Add New Variant
                                    <i class="tio-add"></i>
                                </a>
                            </div>
                            <div class="card-body">
                                <div id="add_new_option">
                                    @if (isset($product->attributes) && count(json_decode($product->attributes,true))>0)
                                        @foreach (json_decode($product->choice_options, true) as $key_choice_options => $item)
                                        {{-- dd($item) --}}
                                        {{--@if (isset($item['price']))
                                            @break

                                        @else --}}
                                            @include('vendor-views.product.partials._new_variations', [
                                                'item' => $item,
                                                'key' => $key_choice_options ,
                                            
                                            ]) 
                                            
                                        {{--@endif--}}
                                        @endforeach
                                    @endif
                                </div>

                                    <!-- Empty Variation -->
                                    @if (!isset($product->attributes) || count(json_decode($product->attributes,true))<1)
                                    <div id="empty-variation">
                                        <div class="text-center">
                                            <img src="{{ asset('/public/assets/admin/img/variation.png') }}" alt="">
                                            <div>No variation added</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- variants end -->

                    <br><br>
                    <div class="form-group">
                        <label>{{translate('messages.item')}} {{translate('messages.thumbnail')}}</label><small style="color: red">* ( {{translate('messages.ratio')}} 1:1 )</small>
                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg1">{{translate('messages.choose')}} {{translate('messages.file')}}</label>
                        </div>

                        <center style="display: block" id="image-viewer-section" class="pt-2">
                            <img style="height: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                 src="{{asset('storage/app/public/product')}}/{{$product['image']}}"
                                 alt="product image"/>
                        </center>
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item')}} {{translate('messages.images')}}</label>
                        <div>
                            <div class="row" id="coba">
                                @foreach ($product->images as $key => $photo)
                                    <div class="col-lg-2 col-md-4 col-sm-4 col-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <img style="width: 100%" height="auto"
                                                        onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                        src="{{asset("storage/app/public/product/$photo")}}"
                                                        alt="Product image">
                                                <a href="{{route('vendor.item.remove-image',['id'=>$product['id'],'name'=>$photo])}}"
                                                    class="btn btn-danger btn-block">{{translate('messages.remove')}}</a>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- debug start -->

                    
                        


                   
                    
                    <!-- debug end -->

                    <hr>
                    <button type="submit" class="btn btn-primary">{{translate('messages.update')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')

@endpush

@push('script_2')
    <script>
        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
            $('#image-viewer-section').show(1000)
        });

        $(document).ready(function () {
            setTimeout(function () {
                let category = $("#category-id").val();
                let sub_category = '{{count($product_category)>=2?$product_category[1]->id:''}}';
                let sub_sub_category ='{{count($product_category)>=3?$product_category[2]->id:''}}';
                getRequest('{{url('/')}}/vendor-panel/item/get-categories?parent_id=' + category + '&&sub_category=' + sub_category, 'sub-categories');
                getRequest('{{url('/')}}/vendor-panel/item/get-categories?parent_id=' + sub_category + '&&sub_category=' + sub_sub_category, 'sub-sub-categories');
            }, 1000)
        });
    </script>

    <script>
        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        var count = {{ isset($product->attributes) ? count(json_decode($product->attributes, true)) : 0 }};
        $(document).ready(function() {

            // $('#organic').hide();
            // food
            $("#add_new_option_button").click(function(e) {
                $('#empty-variation').hide();
                count++;
                var add_option_view = `
                        <div class="__bg-F8F9FC-card view_new_option mb-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-check form--check">
                                        {{--<input id="choice_options[` + count + `][required]" name="options[` + count + `][required]" class="form-check-input" type="checkbox">--}}
                                        {{--<span class="form-check-label">{{ translate('Required') }}</span>--}}
                                        <span class="form-check-label">variant# `+count+`</span>
                                    </label>
                                    <div>
                                        <button type="button" class="btn btn-danger btn-sm delete_input_button" onclick="removeOption(this)"
                                            title="Delete">
                                            <i class="tio-add-to-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-xl-4 col-lg-6">
                                        <label for="">Name</label>
                                        {{--<input required name=options[` + count +
                    `][name] class="form-control" type="text" onkeyup="new_option_name(this.value,` +
                    count + `)">--}}

                                        <select name="attribute_id[`+count+`]" id="choice_attributes"
                                            class="form-control js-select2-custom" >
                                                @foreach (\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                                <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                                                @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-4 col-lg-6">
                                        <div>
                                            <label class="input-label text-capitalize d-flex align-items-center"><span class="line--limit-1">{{ translate('messages.selcetion_type') }} </span>
                                            </label>
                                            <div class="resturant-type-group px-0">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value=1
                                                    name="choice_options[` + count + `][multiselect]" id="type` + count +
                    `" checked onchange="hide_min_max(` + count + `)"
                                                    >
                                                    <span class="form-check-label">
                                                        Single Selection
                                                    </span>
                                                </label>

                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="multi"
                                                    name="choice_options[` + count + `][multiselect]" id="type` + count +
                    `"  onchange="show_min_max(` + count + `)"
                                                    >
                                                    <span class="form-check-label">
                                                        Multiple Selection
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="option_price_` + count + `" >
                                    <div class="bg-white border rounded p-3 pb-0 mt-3">
                                        <div  id="option_price_view_` + count + `">
                                            <div class="row g-3 add_new_view_row_class mb-3">
                                                <div class="col-md-4 col-sm-6">
                                                    <label for="">Option Name</label>
                                                    <input class="form-control" required type="text" name="choice_options[` +
                    count +
                    `][options][0][type]" id="">
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <label for="">Additional Price</label>
                                                    <input class="form-control" required type="number" min="0" step="0.01" name="choice_options[` +
                    count + `][options][0][price]" id="">

                    <input class="form-control" name="choice_options[` +
                    count + `][options][0][stock]" type="hidden" value = 100000>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3 p-3 mr-1 d-flex "  id="add_new_button_` + count +
                    `">
                                            <button type="button" class="btn btn--primary btn-outline-primary" onclick="add_new_row_button(` +
                    count + `)" >Add New Option</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                $("#add_new_option").append(add_option_view);
            });
        });

        function new_option_name(value, data) {
        $("#new_option_name_" + data).empty();
        $("#new_option_name_" + data).text(value)
        console.log(value);
        }

        function removeOption(e) {
            element = $(e);
            element.parents('.view_new_option').remove();
        }

        function deleteRow(e) {
            element = $(e);
            element.parents('.add_new_view_row_class').remove();
        }


        function add_new_row_button(data) {
            count = data;
            countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            var add_new_row_view = `
                <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                    <div class="col-md-4 col-sm-5">
                            <label for="">Option Name</label>
                            <input class="form-control" required type="text" name="choice_options[` + count + `][options][` +
                countRow + `][type]" id="">
                        </div>
                        <div class="col-md-4 col-sm-5">
                            <label for="">Additional Price</label>
                            <input class="form-control"  required type="number" min="0" step="0.01" name="choice_options[` +
                count +
                `][options][` + countRow + `][price]" id="">
                            <input class="form-control" name="choice_options[` +
                                count + `][options][`+countRow+`][stock]" type="hidden" value = 100000>
                        </div>
                        <div class="col-sm-2 max-sm-absolute">
                            <label class="d-none d-sm-block">&nbsp;</label>
                            <div class="mt-1">
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)"
                                    title="{{ translate('Delete') }}">
                                    <i class="tio-add-to-trash"></i>
                                </button>
                            </div>
                    </div>
                </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);

        }
    </script>


    <script src="{{asset('public/assets/admin')}}/js/tags-input.min.js"></script>

    <script>
        $('#choice_attributes').on('change', function () {
            combination_update();
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function () {
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
        });

        function add_more_customer_choice_option(i, name) {
            let n = name;
            $('#customer_choice_options').append('<div class="row"><div class="col-md-3"><input type="hidden" name="choice_no[]" value="' + i + '"><input type="text" class="form-control" name="choice[]" value="' + n + '" placeholder="{{translate('messages.choice_title')}}" readonly></div><div class="col-lg-9"><input type="text" class="form-control" name="choice_options_' + i + '[]" placeholder="{{translate('messages.enter_choice_values')}}" data-role="tagsinput" onchange="combination_update()"></div></div>');
            $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
        }

        setTimeout(function () {
            $('.call-update-sku').on('change', function () {
                combination_update();
            });
        }, 2000)

        $('#colors-selector').on('change', function () {
            combination_update();
        });

        $('input[name="unit_price"]').on('keyup', function () {
            combination_update();
        });

        function combination_update() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: '{{route('vendor.item.variant-combination')}}',
                data: $('#product_form').serialize()+'&stock={{$module_data['stock']}}',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    $('#variant_combination').html(data.view);
                    if (data.length > 1) {
                        $('#quantity').hide();
                    } else {
                        $('#quantity').show();
                    }
                }
            });
        }
    </script>

    <script>
        $('#product_form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.item.update',[$product['id']])}}',
                data: $('#product_form').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{translate('messages.product_updated_successfully')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('vendor.item.list')}}';
                        }, 2000);
                    }
                }
            });
        });
    </script>
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
            if(lang == 'en')
            {
                $("#from_part_2").removeClass('d-none');
            }
            else
            {
                $("#from_part_2").addClass('d-none');
            }
        })
    </script>

    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'item_images[]',
                maxCount: 6,
                rowHeight: '120px',
                groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{asset('public/assets/admin/img/400x400/img2.jpg')}}",
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
                    toastr.error("{{translate('messages.please_only_input_png_or_jpg_type_file')}}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error("{{translate('messages.file_size_too_big')}}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush


