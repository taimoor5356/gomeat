@extends('layouts.vendor.app')

@section('title',translate('messages.add_new_item'))

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
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.add')}} {{translate('messages.new')}} {{translate('messages.item')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="javascript:" method="post" id="item_form"
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
                            <div class="card p-4 {{$lang != $default_lang ? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                <div class="form-group">
                                    <label class="input-label" for="{{$lang}}_name">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                    <input type="text" {{$lang == $default_lang? 'required':''}} name="name[]" id="{{$lang}}_name" class="form-control" placeholder="{{translate('messages.new_item')}}" oninvalid="document.getElementById('en-link').click()">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="form-group pt-4">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short')}} {{translate('messages.description')}} ({{strtoupper($lang)}})</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                </div>
                            </div>
                        @endforeach
                    @else
                    <div class="card p-4" id="{{$default_lang}}-form">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} (EN)</label>
                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_item')}}" required>
                        </div>
                        <input type="hidden" name="lang[]" value="en">
                        <div class="form-group pt-4">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short')}} {{translate('messages.description')}}</label>
                            <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.price')}}</label>
                                <input type="number" min="0" max="100000" step="0.01" value="1" name="price" class="form-control"
                                       placeholder="Ex : 100" required>
                            </div>
                        </div>
                        {{-- <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}}</label>
                                <input type="number" min="0" max="100000" value="0" name="discount" class="form-control"
                                       placeholder="Ex : 100" >
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} {{translate('messages.type')}}</label>
                                <select name="discount_type" class="form-control js-select2-custom">
                                    <option value="percent">{{translate('messages.percent')}}</option>
                                    <option value="amount">{{translate('messages.amount')}}</option>
                                </select>
                            </div>
                        </div> --}}
                    </div>

                    <div class="row">
                        @if ($module_data['unit'])
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize" for="unit">{{translate('messages.unit')}}</label>
                                <select name="unit" class="form-control js-select2-custom">
                                    @foreach (\App\Models\Unit::all() as $unit)
                                        <option value="{{$unit->id}}">{{$unit->unit}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                            
                        @endif
                        @if ($module_data['veg_non_veg'])
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item_type')}}</label>
                                <select name="veg" class="form-control js-select2-custom" required>
                                    <option value="0">{{translate('messages.non_veg')}}</option>
                                    <option value="1">{{translate('messages.veg')}}</option>
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
                                <select name="category_id" class="form-control js-select2-custom"
                                        onchange="getRequest('{{url('/')}}/vendor-panel/item/get-categories?parent_id='+this.value,'sub-categories')">
                                    <option value="">---{{translate('messages.select')}}---</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.sub_category')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="sub_category_id" id="sub-categories"
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
                                        class="form-control js-select2-custom">

                                </select>
                            </div>
                        </div>--}}
                    </div>

                    {{--<div class="row" style="border: 1px solid #80808045; border-radius: 10px;padding-top: 10px;margin: 1px">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.attribute')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="attribute_id[]" id="choice_attributes"
                                        class="form-control js-select2-custom"
                                        multiple="multiple">
                                    @foreach(\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                        <option value="{{$attribute['id']}}">{{$attribute['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2 mb-2">
                            <div class="customer_choice_options" id="customer_choice_options">
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-2">
                            <div class="variant_combination" id="variant_combination">
                            </div>
                        </div>
                    </div>--}}

                    <!-- food variation -->
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
                                <!-- Empty Variation -->
                                <div id="empty-variation">
                                    <div class="text-center">
                                        <img src="{{asset('/public/assets/admin/img/variation.png')}}" alt="">
                                        <div>No variation added</div>
                                    </div>
                                </div>
                                <div id="add_new_option">
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <!--  attribute or variants add -->

                    {{--<div class="col-md-12" id="attribute_section">
                        <div class="card shadow--card-2 border-0">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <span class="card-header-icon"><i class="tio-canvas-text"></i></span>
                                    <span>Variants</span>
                                </h5>
                            </div>
                            <div class="card-body pb-0">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label class="input-label"
                                                for="exampleFormControlSelect1">Variants<span
                                                    class="input-label-secondary"></span></label>
                                            <select name="attribute_id[]" id="choice_attributes"
                                                class="form-control js-select2-custom" multiple="multiple">
                                                @foreach (\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                                    <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <div class="customer_choice_options d-flex __gap-24px"
                                            id="customer_choice_options">

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="variant_combination" id="variant_combination">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                         @if ($module_data['stock'])
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="input-label" for="total_stock">{{translate('messages.total_stock')}}</label>                                 
                                    <input type="hidden" class="form-control" name="current_stock" id="quantity" value="1000">
                                </div>
                            </div>                            
                        @endif
                        
                        @if ($module_data['add_on'])
                            <div class="col-12" >
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.addon')}}<span
                                            class="input-label-secondary"></span></label>
                                    <select name="addon_ids[]" class="form-control js-select2-custom" multiple="multiple">
                                        @foreach(\App\Models\AddOn::where('store_id', \App\CentralLogics\Helpers::get_store_id())->orderBy('name')->get() as $addon)
                                            <option value="{{$addon['id']}}">{{$addon['name']}}</option>
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
                                <input type="time" name="available_time_starts" class="form-control" 
                                       placeholder="Ex : 10:30 am" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.ends')}}</label>
                                <input type="time" name="available_time_ends" class="form-control"  placeholder="5:45 pm"
                                       required>
                            </div>
                        </div>
                    </div>
                    @endif--}}


                    <div class="form-group">
                        <label>{{translate('messages.item')}} {{translate('messages.thumbnail')}}</label><small style="color: red">* ( {{translate('messages.ratio')}} 1:1 )</small>
                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                            <label class="custom-file-label" for="customFileEg1">{{translate('messages.choose')}} {{translate('messages.file')}}</label>
                        </div>

                        <center style="display: none" id="image-viewer-section" class="pt-2">
                            <img style="height: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                 src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt="banner image"/>
                        </center>
                    </div>
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item')}} {{translate('messages.images')}}</label>
                        <div>
                            <div class="row" id="coba"></div>
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
    
<!-- variant script -->
    <script>
        var count = 0;
        // var countRow=0;
        $(document).ready(function() {

            // $('#food_variation_section').hide();
            // $('#organic').hide();
            $("#add_new_option_button").click(function(e) {
                $('#empty-variation').hide();
                count++;
                var add_option_view = `
                    <div class="__bg-F8F9FC-card view_new_option mb-2">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check form--check">
                                    {{--<input id="options[` + count + `][required]" name="options[` + count + `][required]" class="form-check-input" type="checkbox">--}}
                                    {{--<span class="form-check-label">Required</span>--}}
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
                                        <label class="input-label text-capitalize d-flex align-items-center"><span class="line--limit-1">Selection Type</span>
                                        </label>
                                        <div class="resturant-type-group px-0">
                                            

                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="single"
                                                name="options[` + count + `][type]" id="type` + count +
                    `" checked onchange="hide_min_max(` + count + `)"
                                                >
                                                <span class="form-check-label">
                                                    Single Selection
                                                </span>
                                            </label>

                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="multi"
                                                name="options[` + count + `][type]" id="type` + count +
                    `"  onchange="show_min_max(` + count + `)"
                                                >
                                                <span class="form-check-label">
                                                    Multiple Selection
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                {{--<div class="col-xl-4 col-lg-6">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label for="">Min</label>
                                            <input id="min_max1_` + count + `" required  name="options[` + count + `][min]" class="form-control" type="number" min="1">
                                        </div>
                                        <div class="col-6">
                                            <label for="">Max</label>
                                            <input id="min_max2_` + count + `"   required name="options[` + count + `][max]" class="form-control" type="number" min="1">
                                        </div>
                                    </div>
                                </div>--}}
                            </div>

                            <div id="option_price_` + count + `" >
                                <div class="bg-white border rounded p-3 pb-0 mt-3">
                                    <div  id="option_price_view_` + count + `">
                                        <div class="row g-3 add_new_view_row_class mb-3">
                                            <div class="col-md-4 col-sm-6">
                                                <label for="">Option Name</label>
                                                <input class="form-control" required type="text" name="options[` +
                    count +
                    `][values][0][type]" id="">
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <label for="">Additional Price</label>
                                                <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][0][price]" id="">
                                            </div>
                                            {{--<div class="col-md-2 col-sm-3">
                                                <label for="">Quantity</label>
                                                <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][0][stock]" id="">
                                            </div>--}}
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

        // function show_min_max(data) {
        //     $('#min_max1_' + data).removeAttr("readonly");
        //     $('#min_max2_' + data).removeAttr("readonly");
        //     $('#min_max1_' + data).attr("required", "true");
        //     $('#min_max2_' + data).attr("required", "true");
        // }

        // function hide_min_max(data) {
        //     $('#min_max1_' + data).val(null).trigger('change');
        //     $('#min_max2_' + data).val(null).trigger('change');
        //     $('#min_max1_' + data).attr("readonly", "true");
        //     $('#min_max2_' + data).attr("readonly", "true");
        //     $('#min_max1_' + data).attr("required", "false");
        //     $('#min_max2_' + data).attr("required", "false");
        // }




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
                        <input class="form-control" required type="text" name="options[` + count + `][values][` +
                countRow + `][type]" id="">
                    </div>
                    <div class="col-md-4 col-sm-5">
                        <label for="">Additional Price</label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                count +
                `][values][` + countRow + `][price]" id="">
                    </div>
                    {{--<div class="col-md-2 col-sm-3">
                                                <label for="">Quantity</label>
                                                <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][`+countRow+`][stock]" id="">
                                            </div>--}}
                    <div class="col-sm-2 max-sm-absolute">
                        <label class="d-none d-sm-block">&nbsp;</label>
                        <div class="mt-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)"
                                title="Delete">
                                <i class="tio-add-to-trash"></i>
                            </button>
                        </div>
                </div>
            </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);

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
            $('#image-viewer-section').show(1000);
        });
    </script>

    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>


    <script src="{{asset('public/assets/admin')}}/js/tags-input.min.js"></script>

    <script>
        // $('#choice_attributes').on('change', function () {
        //     $('#customer_choice_options').html(null);
        //     $.each($("#choice_attributes option:selected"), function () {
        //         add_more_customer_choice_option($(this).val(), $(this).text());
        //     });
        // });

        // function add_more_customer_choice_option(i, name) {
        //     let n = name;
        //     $('#customer_choice_options').append(
        //         '<div class="row"><div class="col-md-3"><input type="hidden" name="choice_no[]" value="' + i + '"><input type="text" class="form-control" name="choice[]" value="' + n + '" placeholder="{{translate('messages.choice_title')}}" readonly></div><div class="col-lg-9"><input type="text" class="form-control" name="choice_options_' + i + '[]" placeholder="{{translate('messages.enter_choice_values')}}" data-role="tagsinput" onchange="combination_update()"></div></div>');
        //     $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
        // }

        // function combination_update() {
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         }
        //     });

        //     $.ajax({
        //         type: "POST",
        //         url: '{{route('vendor.item.variant-combination')}}',
        //         data: $('#item_form').serialize()+'&stock={{$module_data['stock']}}',
        //         beforeSend: function () {
        //             $('#loading').show();
        //         },
        //         success: function (data) {
        //             $('#loading').hide();
        //             $('#variant_combination').html(data.view);
        //             if (data.length < 1) {
        //                 $('input[name="current_stock"]').attr("readonly", false);
        //             }
        //         }
        //     });
        // }
    </script>


    <script>
        $('#item_form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.item.store')}}',
                data: $('#item_form').serialize(),
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
                        toastr.success('{{translate('messages.product_added_successfully')}}', {
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
            if(lang == '{{$default_lang}}')
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


