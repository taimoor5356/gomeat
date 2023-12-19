@extends('layouts.admin.app')

@section('title',translate('messages.coupons'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.add')}} {{translate('messages.new')}} {{translate('messages.coupon')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.coupon.store')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.title')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{translate('messages.new_coupon')}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('messages.module')}}</label>
                                        <select name="module_id" class="form-control js-select2-custom"  data-placeholder="{{translate('messages.select')}} {{translate('messages.module')}}" id="module_select">
                                                <option value="" selected disabled>{{translate('messages.select')}} {{translate('messages.module')}}</option>
                                                <option value="">All</option>
                                            @foreach(\App\Models\Module::notParcel()->get() as $module)
                                                <option value="{{$module->id}}" >{{$module->module_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">Region</label>
                                        {{-- <select name="region_id" required --}}
                                        <select name="region_id" 
                                                class="form-control js-select2-custom"  data-placeholder="Select Region" id="region_select">
                                                <option value="" selected disabled>Select Region</option>
                                            @foreach(\App\Models\Country::get() as $country)
                                                <option value="{{$country->id}}">{{$country->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.coupon')}} {{translate('messages.type')}}</label>
                                        <select name="coupon_type" class="form-control" onchange="coupon_type_change(this.value)">
                                            <option value="store_wise">{{translate('messages.store')}} {{translate('messages.wise')}}</option>
                                            <option value="zone_wise">{{translate('messages.zone')}} {{translate('messages.wise')}}</option>
                                            <option value="free_delivery">{{translate('messages.free_delivery')}}</option>
                                            <option value="first_order">{{translate('messages.first')}} {{translate('messages.order')}}</option>
                                            <option value="default">{{translate('messages.default')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6" >
                                    <div class="form-group" id="store_wise">
                                        <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.store')}}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="store_ids[]" class="js-data-example-ajax form-control" data-placeholder="{{translate('messages.select_store')}}" title="{{translate('messages.select_store')}}">                                            
                                        </select>
                                    </div>
                                    <div class="form-group" id="zone_wise">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select')}} {{translate('messages.zone')}}</label>
                                        <select name="zone_ids[]" id="choice_zones"
                                            class="form-control js-select2-custom"
                                            multiple="multiple" data-placeholder="{{translate('messages.select_zone')}}">
                                        @foreach(\App\Models\Zone::all() as $zone)
                                            <option value="{{$zone->id}}">{{$zone->name}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.code')}}</label>
                                        <input type="text" name="code" class="form-control"
                                            placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.limit')}} {{translate('messages.for')}} {{translate('messages.same')}} {{translate('messages.user')}}</label>
                                        <input type="number" name="limit" id="coupon_limit" class="form-control" placeholder="EX: 10" max="100">
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.start')}} {{translate('messages.date')}}</label>
                                        <input type="date" name="start_date" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.expire')}} {{translate('messages.date')}}</label>
                                        <input type="date" name="expire_date" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} {{translate('messages.type')}}</label>
                                        <select name="discount_type" class="form-control" id="discount_type">
                                            <option value="amount">{{translate('messages.amount')}}</option>
                                            <option value="percent">{{translate('messages.percent')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}}</label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99" name="discount" id="discount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="max_discount">{{translate('messages.max')}} {{translate('messages.discount')}}</label>
                                        <input type="number" step="0.01" min="0" value="0" max="999999999999.99" name="max_discount" id="max_discount" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.min')}} {{translate('messages.purchase')}}</label>
                                        <input type="number" step="0.01" name="min_purchase" value="0" min="0" max="999999999999.99" class="form-control"
                                            placeholder="100">
                                    </div>
                                </div>                                
                            </div>
                            <button type="submit" class="btn btn-primary">{{translate('messages.submit')}}</button>
                        </form>
                    </div>
                </div>
                
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                    <h5>{{translate('messages.coupon')}} {{translate('messages.list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$coupons->total()}}</span></h5>
                        <form id="dataSearch">
                        @csrf
                            <!-- Search -->
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{translate('messages.search_here')}}" aria-label="{{translate('messages.search_here')}}">
                                <button type="submit" class="btn btn-light">{{translate('messages.search')}}</button>
                            </div>
                            <!-- End Search -->
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="table-div">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                
                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('messages.#')}}</th>
                                <th>{{translate('messages.title')}}</th>
                                <th>{{translate('messages.code')}}</th>
                                <!-- <th>{{translate('messages.module')}}</th> -->
                                <th>Region</th>
                                <th>{{translate('messages.type')}}</th>
                                <th>{{translate('messages.total_uses')}}</th>
                                <th>{{translate('messages.min')}} {{translate('messages.purchase')}}</th>
                                <th>{{translate('messages.max')}} {{translate('messages.discount')}}</th>
                                <th>{{translate('messages.discount')}}</th>
                                <th>{{translate('messages.discount')}} {{translate('messages.type')}}</th>
                                <th>{{translate('messages.start')}} {{translate('messages.date')}}</th>
                                <th>{{translate('messages.expire')}} {{translate('messages.date')}}</th>
                                <th>{{translate('messages.status')}}</th>
                                <th>{{translate('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($coupons as $key=>$coupon)
                                <tr>
                                    <td>{{$key+$coupons->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                    {{Str::limit($coupon['title'],15,'...')}}
                                    </span>
                                    </td>
                                    <td>{{$coupon['code']}}</td>
                                    <td>@isset($coupon->country){{$coupon->country->name}}@endisset</td>
                                    <td>{{translate('messages.'.$coupon->coupon_type)}}</td>
                                    <td>{{$coupon->total_uses}}</td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($coupon['min_purchase'])}}</td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($coupon['max_discount'])}}</td>
                                    <td>{{$coupon['discount']}}</td>
                                    <td>{{$coupon['discount_type']}}</td>
                                    <td>{{$coupon['start_date']}}</td>
                                    <td>{{$coupon['expire_date']}}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="couponCheckbox{{$coupon->id}}">
                                            <input type="checkbox" onclick="location.href='{{route('admin.coupon.status',[$coupon['id'],$coupon->status?0:1])}}'" class="toggle-switch-input" id="couponCheckbox{{$coupon->id}}" {{$coupon->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-white" href="{{route('admin.coupon.update',[$coupon['id']])}}"title="{{translate('messages.edit')}} {{translate('messages.coupon')}}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn-white" href="javascript:" onclick="form_alert('coupon-{{$coupon['id']}}','Want to delete this coupon ?')" title="{{translate('messages.delete')}} {{translate('messages.coupon')}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.coupon.delete',[$coupon['id']])}}"
                                                    method="post" id="coupon-{{$coupon['id']}}">
                                                @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <table>
                            <tfoot>
                            {!! $coupons->links() !!}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
<script>
    $("#date_from").on("change", function () {
        $('#date_to').attr('min',$(this).val());
    });

    $("#date_to").on("change", function () {
        $('#date_from').attr('max',$(this).val());
    });
    
    $(document).on('ready', function () {
        $('#discount_type').on('change', function() {
         if($('#discount_type').val() == 'amount')
            {
                $('#max_discount').attr("readonly","true");
                $('#max_discount').val(0);
            }
            else
            {
                $('#max_discount').removeAttr("readonly");
            }
        });
        
        $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);

        var module_id = 0;
        $('#module_select').on('change', function(){
            if($(this).val())
            {
                module_id = $(this).val();
            }
        });

        $('.js-data-example-ajax').select2({
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
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                    '<img class="mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description" style="width: 7rem;">' +
                    '<p class="mb-0">No data to show</p>' +
                    '</div>'
                }
            });

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('#zone_wise').hide();
        function coupon_type_change(coupon_type) {
           if(coupon_type=='zone_wise')
            {
                $('#store_wise').hide();
                $('#zone_wise').show();
            }
            else if(coupon_type=='store_wise')
            {
                $('#store_wise').show();
                $('#zone_wise').hide();
            }
            else if(coupon_type=='first_order')
            {
                $('#zone_wise').hide();
                $('#store_wise').hide();
                $('#coupon_limit').val(1);
                $('#coupon_limit').attr("readonly","true");
            }
            else{
                $('#zone_wise').hide();
                $('#store_wise').hide();
                $('#coupon_limit').val('');
                $('#coupon_limit').removeAttr("readonly");
            }

            if(coupon_type=='free_delivery')
            {
                $('#discount_type').attr("disabled","true");
                $('#discount_type').val("").trigger( "change" );
                $('#max_discount').val(0);
                $('#max_discount').attr("readonly","true");
                $('#discount').val(0);
                $('#discount').attr("readonly","true");
            }
            else{
                $('#max_discount').removeAttr("readonly");
                $('#discount_type').removeAttr("disabled");
                $('#discount').removeAttr("readonly");
            }
        }
        $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.coupon.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
