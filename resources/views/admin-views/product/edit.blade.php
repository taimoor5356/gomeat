@extends('layouts.admin.app')

@section('title',translate('Edit item'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
@endpush

@section('content')
    @php($opening_time='')
    @php($closing_time='')
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
                    @php($default_lang = 'en')
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
                                    <input type="text" name="name[]" id="{{$lang}}_name" class="form-control" placeholder="{{translate('messages.new_food')}}" value="{{$translate[$lang]['name']??$product['name']}}" {{$lang == $default_lang? 'required':''}} oninvalid="document.getElementById('en-link').click()">
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
                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_food')}}" value="{{$product['name']}}" required>
                        </div>
                        <input type="hidden" name="lang[]" value="en">
                        <div class="form-group pt-4">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short')}} {{translate('messages.description')}}</label>
                            <textarea type="text" name="description[]" class="form-control ckeditor">{!! $product['description'] !!}</textarea>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.module')}}</label>
                                <select name="module_id" required class="form-control js-select2-custom"  data-placeholder="{{translate('messages.select')}} {{translate('messages.module')}}" onchange="modulChange(this.value)" >
                                        <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.module')}}</option>
                                    @foreach(\App\Models\Module::notParcel()->get() as $module)
                                        <option value="{{$module->id}}" {{$module->id == $product->module_id?'selected':''}}>{{$module->module_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.store')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="store_id" data-placeholder="{{translate('messages.select')}} {{translate('messages.store')}}" id="store_id" class="js-data-example-ajax form-control" onchange="getStoreData('{{url('/')}}/admin/vendor/get-addons?data[]=0&store_id=', this.value,'add_on')"  title="Select Store" required oninvalid="this.setCustomValidity('{{translate('messages.please_select_store')}}')">
                                @if(isset($product->store))
                                <option value="{{$product->store_id}}" selected="selected">{{$product->store->name}}</option>
                                @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.price')}}</label>
                                <input type="number" value="{{$product['price']}}" min="0" max="999999999999.99" name="price"
                                       class="form-control" step="0.01"
                                       placeholder="Ex : 100" required>
                            </div>
                        </div>
                        <!-- <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="" selected disabled>Select Currency</option>
                                    <option value="$" @isset($product) @if($product['currency'] == '$') selected @endif @endisset>($) Dollar</option>
                                    <option value="Rs" @isset($product) @if($product['currency'] == 'Rs') selected @endif @endisset>(Rs) Rupees</option>
                                </select>
                            </div>
                        </div> -->

                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} {{translate('messages.type')}}</label>
                                <select name="discount_type" class="form-control">
                                    <option value="percent" {{$product['discount_type']=='percent'?'selected':''}}>
                                        {{translate('messages.percent')}}
                                    </option>
                                    <option value="amount" {{$product['discount_type']=='amount'?'selected':''}}>
                                        {{translate('messages.amount')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}}</label>
                                <input type="number" min="0" value="{{$product['discount']}}" max="100000"
                                       name="discount" class="form-control"
                                       placeholder="Ex : 100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6" id="unit_input">
                            <div class="form-group">
                                <label class="input-label text-capitalize" for="unit">{{translate('messages.unit')}}</label>
                                <select name="unit" class="form-control">
                                    @foreach (\App\Models\Unit::all() as $unit)
                                        <option value="{{$unit->id}}" {{$unit->id == $product->unit_id? 'selected':''}}>{{$unit->unit}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6" id="weight_input">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Weight</label>
                                <input type="number" value="{{$product['weight']}}"
                                       name="weight" class="form-control"
                                       placeholder="Ex : 100">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6" id="veg_input">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item_type')}}</label>
                                <select name="veg" class="form-control">
                                    <option value="0" {{$product['veg']==0?'selected':''}}>{{translate('messages.non_veg')}}</option>
                                    <option value="1" {{$product['veg']==1?'selected':''}}>{{translate('messages.veg')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.category')}}<span
                                        class="input-label-secondary">*</span></label>
                                <select name="category_id" class="js-data-example-ajax form-control" id="category_id" onchange="categoryChange(this.value)">
                                    @if($category)
                                        <option value="{{$category['id']}}" >{{$category['name']}}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.sub_category')}}<span
                                        class="input-label-secondary" title="{{translate('messages.category_required_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.category_required_warning')}}"></span></label>
                                <select name="sub_category_id" class="js-data-example-ajax form-control" id="sub-categories">
                                    @if(isset($sub_category))
                                    <option value="{{$sub_category['id']}}" >{{$sub_category['name']}}</option>
                                    @endif
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
                                            @include('admin-views.product.partials._new_variations', [
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

                    {{--<div class="row" style="border: 1px solid #80808045; border-radius: 10px;padding-top: 10px;margin: 1px">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Variants<span
                                        class="input-label-secondary"></span></label>
                                <select name="attribute_id[]" id="choice_attributes"
                                        class="form-control js-select2-custom"
                                        multiple="multiple">
                                    @foreach(\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                        <option value="{{$attribute['id']}}" {{in_array($attribute->id,json_decode($product['attributes'],true))?'selected':''}}>{{$attribute['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-2">
                            <div class="customer_choice_options" id="customer_choice_options">
                                @include('admin-views.product.partials._choices',['choice_no'=>json_decode($product['attributes']),'choice_options'=>json_decode($product['choice_options'],true)])
                            </div>
                        </div>
                         <div class="col-md-12 mt-2 mb-2">
                            <div class="variant_combination" id="variant_combination">
                                @include('admin-views.product.partials._edit-combinations',['combinations'=>json_decode($product['variations'],true),'stock'=>config('module.'.$product->module->module_type)['stock']])
                            </div>
                        </div> 
                    </div>--}}

                    {{--<div class="row mt-2">
                        <div class="col-sm-6" id="stock_input">
                            <div class="form-group">
                                <label class="input-label" for="total_stock">{{translate('messages.total_stock')}}</label>                                
                                <input type="number" class="form-control" name="current_stock" value="{{$product->stock}}" id="quantity">
                            </div>
                        </div>
                        <div class="col-sm-6" id="addon_input">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.addon')}}<span
                                        class="input-label-secondary" title="{{translate('messages.store_required_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_required_warning')}}"></span></label>
                                <select name="addon_ids[]" class="form-control js-select2-custom" multiple="multiple" id="add_on">
                                </select>
                            </div>
                        </div>
                    </div>--}}

                    <div class="row mt-2">
                        <!-- <div class="col-sm-6" id="sales_tax">
                            <div class="form-group">
                                <label class="input-label" for="sales_tax">Sales Tax %</label>                                
                                <input type="number" class="form-control" name="sales_tax" value="{{$product->sales_tax}}" id="quantity">
                            </div>
                        </div> -->
                        {{-- <div class="col-sm-6" id="gm_commission">
                            <div class="form-group">
                                <label class="input-label" for="gm_commission">GoMeat Commission %</label>                                
                                <input type="number" class="form-control" name="gm_commission" value="{{$product->gm_commission}}" id="quantity">
                            </div>
                        </div> --}}
                    </div>

                    {{--<div class="row"  id="time_input">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.starts')}}</label>
                                <input type="time" value="{{$product['available_time_starts']}}"
                                       name="available_time_starts" class="form-control" id="available_time_starts"
                                       placeholder="Ex : 10:30 am" >
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.ends')}}</label>
                                <input type="time" value="{{$product['available_time_ends']}}"
                                       name="available_time_ends" class="form-control" id="available_time_ends" placeholder="5:45 pm"
                                       >
                            </div>
                        </div>
                    </div>--}}

                    <div class="form-group">
                        <label class="text-dark">{{translate('messages.item')}} {{translate('messages.thumbnail')}}</label><small style="color: red">* ( {{translate('messages.ratio')}} 1:1 )</small>
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
                                                <a href="{{route('admin.item.remove-image',['id'=>$product['id'],'name'=>$photo])}}"
                                                    class="btn btn-danger btn-block">{{translate('messages.remove')}}</a>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
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
        function getStoreData(route, store_id, id) {
            $.get({
                url: route+store_id,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

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
            @if(count(json_decode($product['add_ons'], true))>0)
            getStoreData('{{url('/')}}/admin/vendor/get-addons?@foreach(json_decode($product['add_ons'], true) as $addon)data[]={{$addon}}& @endforeach store_id=','{{$product['store_id']}}','add_on');
            @else
            getStoreData('{{url('/')}}/admin/vendor/get-addons?data[]=0&store_id=','{{$product['store_id']}}','add_on');
            @endif
        });

        // food variations script

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
        var module_id = {{$product->module_id}};
        var parent_category_id = {{$category?$category->id:0}};
        <?php 
            $module_data = config('module.'.$product->module->module_type);
            unset($module_data['description']);
        ?>
        var module_data = {{str_replace('"','',json_encode($module_data))}};
        input_field_visibility_update();
        function modulChange(id)
        {
            $.get({
                url: "{{url('/')}}/admin/module/"+id,
                dataType: 'json',
                success: function (data) {
                    module_data = data.data;
                    stock = module_data.stock;
                    input_field_visibility_update();
                    combination_update();
                },
            });
            module_id = id;
        }

        function input_field_visibility_update()
        {
            if(module_data.stock)
            {
                $('#stock_input').show();
            }
            else
            {
                $('#stock_input').hide();
            }
            if(module_data.add_on)
            {
                $('#addon_input').show();
            }
            else{
                $('#addon_input').hide();
            }

            if(module_data.item_available_time)
            {
                $('#time_input').show();
            }
            else{
                $('#time_input').hide();
            }

            if(module_data.veg_non_veg)
            {
                $('#veg_input').show();
                $('#weight_input').hide(); 
            }
            else{
                $('#veg_input').hide();
                $('#weight_input').show();
            }

            if(module_data.unit)
            {
                $('#unit_input').show();
            }
            else{
                $('#unit_input').hide();
            }
        }

        function categoryChange(id)
        {
            parent_category_id = id;
            console.log(parent_category_id);
        }

        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        
        $('#store_id').select2({
            ajax: {
                url: '{{url('/')}}/admin/vendor/get-stores',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        module_id: module_id
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

        $('#category_id').select2({
            ajax: {
                url: '{{url('/')}}/admin/item/get-categories?parent_id=0',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        module_id: module_id
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

        $('#sub-categories').select2({
            ajax: {
                url: '{{url('/')}}/admin/item/get-categories',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        module_id: module_id,
                        parent_id: parent_category_id,
                        sub_category: true
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

        // $('#choice_attributes').on('change', function () {
        //     $('#customer_choice_options').html(null);
        //     combination_update();
        //     $.each($("#choice_attributes option:selected"), function () {
        //         add_more_customer_choice_option($(this).val(), $(this).text());
        //     });
        // });
        $('#choice_attributes').on('change', function() {
            if (module_id == 0) {
                toastr.error('{{ translate('messages.select_a_module') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                $(this).val("");
                return false;
            }
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function() {
                if ($(this).val().length > 50) {
                    toastr.error(
                        '{{ translate('validation.max.string', ['attribute' => translate('messages.variation'), 'max' => '50']) }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    return false;
                }
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
        });

        function add_more_customer_choice_option(i, name) {
            let n = name;

            $('#customer_choice_options').append(
                `<div class="__choos-item"><div><input type="hidden" name="choice_no[]" value="${i}"><input type="text" class="form-control d-none" name="choice[]" value="${n}" placeholder="{{ translate('messages.choice_title') }}" readonly> <label class="form-label">${n}</label> </div><div><input type="text" class="form-control" name="choice_options_${i}[]" placeholder="{{ translate('messages.enter_choice_values') }}" data-role="tagsinput" onchange="combination_update()"></div></div>`
            );
            $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
        }
        function add_more_customer_choice_option(i, name) {
            let n = name;
            $('#customer_choice_options').append('<div class="row"><div class="col-md-3"><input type="hidden" name="choice_no[]" value="' + i + '"><input type="text" class="form-control" name="choice[]" value="' + n + '" placeholder="Choice Title" readonly></div><div class="col-lg-9"><input type="text" class="form-control" name="choice_options_' + i + '[]" placeholder="Enter choice values" data-role="tagsinput" onchange="combination_update()"></div></div>');
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
                url: '{{route('admin.item.variant-combination')}}',
                data: $('#product_form').serialize(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    $('#variant_combination').html(data.view);
                    if (data.length < 1) {
                        $('input[name="current_stock"]').attr("readonly", false);
                    }
                }
            });
        }
    </script>

    <!-- submit form -->
    <script>
        $('#product_form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.item.update',[$product['id']])}}',
                data: $('#product_form').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    console.log(data);
                    $('#loading').hide();
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{translate("messages.product_updated_successfully")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{\Request::server('HTTP_REFERER')??route('admin.item.list')}}';
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
    <script>
        update_qty();
        function update_qty()
        {
            var total_qty = 0;
            var qty_elements = $('input[name^="stock_"]');
            for(var i=0; i<qty_elements.length; i++)
            {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            if(qty_elements.length > 0)
            {

                $('input[name="current_stock"]').attr("readonly", true);
                $('input[name="current_stock"]').val(total_qty);
            }
            else{
                $('input[name="current_stock"]').attr("readonly", false);
            }
        }
        $('input[name^="stock_"]').on('keyup', function () {
            var total_qty = 0;
            var qty_elements = $('input[name^="stock_"]');
            for(var i=0; i<qty_elements.length; i++)
            {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            $('input[name="current_stock"]').val(total_qty);
        });

    </script>
    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        var imageCount = {{6-count($product->images)}};
        if (imageCount > 0) {
                $("#coba").spartanMultiImagePicker({
                    fieldName: 'item_images[]',
                    maxCount: imageCount,
                    rowHeight: 'auto',
                    groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
                    maxFileSize: '',
                    placeholderImage: {
                        image: "{{asset('public/assets/admin/img/400x400/img2.jpg')}}",
                        width: '100%',
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
            }
    </script>
@endpush


