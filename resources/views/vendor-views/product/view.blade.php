@extends('layouts.vendor.app')

@section('title',translate('Item Preview'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-6">
                    <h1 class="page-header-title text-break">{{$product['name']}}</h1>
                </div>
                <div class="col-6">
                    <a href="{{route('vendor.item.edit',[$product['id']])}}" class="btn btn-primary float-right">
                        <i class="tio-edit"></i> {{translate('messages.edit')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card mb-3 mb-lg-5">
            <!-- Body -->
            <div class="card-body">
                <div class="row align-items-md-center">
                    <div class="col-md-auto mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <img class="avatar avatar-xxl avatar-4by3 mr-4"
                                 src="{{asset('storage/app/public/product')}}/{{$product['image']}}"
                                 onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                 alt="Image Description">
                            <div class="d-block">
                                <h4 class="display-2 text-dark mb-0">{{round($product->avg_rating,1)}}</h4>
                                <p> {{translate('messages.of')}} {{$product->reviews->count()}} {{translate('messages.reviews')}}
                                    <span class="badge badge-soft-dark badge-pill ml-1"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md">
                        <ul class="list-unstyled list-unstyled-py-2 mb-0">

                        @php($total=$product->rating?array_sum(json_decode($product->rating, true)):0)
                        <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($five=$product->rating?json_decode($product->rating, true)[5]:0)
                                <span
                                    class="mr-3">5 {{translate('messages.star')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{$total==0?0:($five/$total)*100}}%;"
                                        aria-valuenow="{{$total==0?0:($five/$total)*100}}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$five}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($four=$product->rating?json_decode($product->rating, true)[4]:0)
                                <span class="mr-3">4 {{translate('messages.star')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{$total==0?0:($four/$total)*100}}%;"
                                        aria-valuenow="{{$total==0?0:($four/$total)*100}}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$four}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($three=$product->rating?json_decode($product->rating, true)[3]:0)
                                <span class="mr-3">3 {{translate('messages.star')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{$total==0?0:($three/$total)*100}}%;"
                                        aria-valuenow="{{$total==0?0:($three/$total)*100}}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$three}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($two=$product->rating?json_decode($product->rating, true)[2]:0)
                                <span class="mr-3">2 {{translate('messages.star')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{$total==0?0:($two/$total)*100}}%;"
                                        aria-valuenow="{{$total==0?0:($two/$total)*100}}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$two}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($one=$product->rating?json_decode($product->rating, true)[1]:0)
                                <span class="mr-3">1 {{translate('messages.star')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{$total==0?0:($one/$total)*100}}%;"
                                        aria-valuenow="{{$total==0?0:($one/$total)*100}}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$one}}</span>
                            </li>
                            <!-- End Review Ratings -->
                        </ul>
                    </div>

                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-4 pt-2">
                        <h4 class="border-bottom">{{$product['name']}}</h4>
                        <span>{{translate('messages.price')}} :
                            <span>{{\App\CentralLogics\Helpers::format_currency($product['price'])}}</span>
                        </span><br>
                        <!-- <span>{{translate('messages.tax')}} :
                            <span>{{\App\CentralLogics\Helpers::format_currency(\App\CentralLogics\Helpers::tax_calculate($product,$product['price']))}}</span>
                        </span><br> -->
                        <span>{{translate('messages.discount')}} :
                            <span>{{\App\CentralLogics\Helpers::format_currency(\App\CentralLogics\Helpers::discount_calculate($product,$product['price']))}}</span>
                        </span><br>
                        @if (config('module.'.\App\CentralLogics\Helpers::get_store_data()->module->module_type)['item_available_time'])
                            <span>
                                {{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.starts')}} : {{date(config('timeformat'), strtotime($product['available_time_starts']))}}
                            </span><br>
                            <span>
                                {{translate('messages.available')}} {{translate('messages.time')}} {{translate('messages.ends')}} : {{date(config('timeformat'), strtotime($product['available_time_ends']))}}
                            </span>                            
                        @endif

                        {{--@if ($product->variations && is_array(json_decode($product['variations'],true)))
                            <h4 class="border-bottom mt-2"> {{translate('messages.variations')}} </h4>
                            @foreach(json_decode($product['variations'],true) as $variation)
                                <span class="text-capitalize">
                                {{$variation['type']}} : {{\App\CentralLogics\Helpers::format_currency($variation['price'])}}
                                </span><br>
                            @endforeach                            
                        @endif

                        @if (config('module.'.\App\CentralLogics\Helpers::get_store_data()->module->module_type)['add_on'])
                        <h4 class="border-bottom mt-2"> Addons </h4>
                        @foreach(\App\Models\AddOn::whereIn('id',json_decode($product['add_ons'],true))->get() as $addon)
                            <span class="text-capitalize">
                              {{$addon['name']}} : {{\App\CentralLogics\Helpers::format_currency($addon['price'])}}
                            </span><br>
                        @endforeach
                        @endif--}}

                        @if(isset($product->choice_options))
                        <h4 class="border-bottom mt-2"> {{translate('messages.variations')}} </h4>
                            @foreach(json_decode($product->choice_options) as $choice_options)
                                <h5>{{$choice_options->title}}</h5>
                                @foreach($choice_options->options as $option)
                                <span class="text-capitalize">
                                {{$option->type}} : {{\App\CentralLogics\Helpers::format_currency($option->price)}}
                                </span><br>
                                @endforeach
                                <br>
                            @endforeach
                        @else
                            <span class="text-capitalize">
                                    No Variants
                            </span>
                        @endif
                    </div>

                    <div class="col-8 pt-2 border-left">
                        <h4>{{translate('messages.short')}} {{translate('messages.description')}} : </h4>
                        <p>{{$product['description']}}</p>
                    </div>
                </div>
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
        @if(\App\CentralLogics\Helpers::get_store_data()->reviews_section)
        <!-- Card -->
        <div class="card">
            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable" class="table table-borderless table-thead-bordered table-nowrap card-table"
                       data-hs-datatables-options='{
                     "columnDefs": [{
                        "targets": [0, 3, 6],
                        "orderable": false
                      }],
                     "order": [],
                     "info": {
                       "totalQty": "#datatableWithPaginationInfoTotalQty"
                     },
                     "search": "#datatableSearch",
                     "entries": "#datatableEntries",
                     "pageLength": 25,
                     "isResponsive": false,
                     "isShowPaging": false,
                     "pagination": "datatablePagination"
                   }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('messages.reviewer')}}</th>
                        <th>{{translate('messages.review')}}</th>
                        <th>{{translate('messages.date')}}</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($reviews as $review)
                        <tr>
                            <td>
                                @if ($review->customer)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-circle">
                                            <img class="avatar-img" width="75" height="75"
                                                onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                src="{{asset('storage/app/public/profile/'.$review->customer->image)}}"
                                                alt="Image Description">
                                        </div>
                                        <div class="ml-3">
                                        <span class="d-block h5 text-hover-primary mb-0">{{$review->customer['f_name']." ".$review->customer['l_name']}} <i
                                                class="tio-verified text-primary" data-toggle="tooltip" data-placement="top"
                                                title="Verified Customer"></i></span>
                                            <span class="d-block font-size-sm text-body">{{$review->customer->email}}</span>
                                        </div>
                                    </div>
                                @else
                                    {{translate('messages.customer_not_found')}}
                                @endif

                            </td>
                            <td>
                                <div class="text-wrap" style="width: 18rem;">
                                    <div class="d-flex mb-2">
                                        <label class="badge badge-soft-info">
                                            {{$review->rating}} <i class="tio-star"></i>
                                        </label>
                                    </div>

                                    <p>
                                        {{$review['comment']}}
                                    </p>
                                </div>
                            </td>
                            <td>
                                {{date('d M Y '.config('timeformat'),strtotime($review['created_at']))}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-12">
                        {!! $reviews->links() !!}
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->
        @endif
    </div>
@endsection

@push('script_2')

@endpush
