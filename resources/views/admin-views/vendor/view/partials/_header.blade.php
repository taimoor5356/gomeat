    <!-- Page Header -->
    <div class="page-header">
        <div class="row">
            <div class="col-6">
                <h1 class="page-header-title text-break">{{$store->name}}</h1>
            </div>
            <div class="col-6">
                @if($store->vendor->status)
                @if (!empty($store->country->short_name) && $store->country->short_name == 'PK')
                <a href="{{route('admin.vendor.edit',[$store['id'], 'pk'])}}" class="btn btn-primary float-right">
                    <i class="tio-edit"></i> {{translate('messages.edit')}} {{translate('messages.store')}}
                </a>
                @else
                <a href="{{route('admin.vendor.edit',[$store->id])}}" class="btn btn-primary float-right">
                    <i class="tio-edit"></i> {{translate('messages.edit')}} {{translate('messages.store')}}
                </a>
                @endif
                @else
                    @if(!isset($store->vendor->status))
                    <a class="btn btn-danger text-capitalize font-weight-bold float-right" 
                    onclick="request_alert('{{route('admin.vendor.application',[$store['id'],0])}}','{{translate('messages.you_want_to_deny_this_application')}}')"
                        href="javascript:">{{translate('messages.deny')}}</a>
                    @endif
                    <a class="btn btn-primary text-capitalize font-weight-bold float-right mr-2"
                    onclick="request_alert('{{route('admin.vendor.application',[$store['id'],1])}}','{{translate('messages.you_want_to_approve_this_application')}}')"
                        href="javascript:">{{translate('messages.approve')}}</a>
                @endif
            </div>
        </div>
        @if($store->vendor->status)
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs">
            <li class="nav-item">
                    <a class="nav-link {{request('tab')==null?'active':''}}" href="{{route('admin.vendor.view', $store->id)}}">{{translate('messages.store')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='order'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'order'])}}"  aria-disabled="true">{{translate('messages.order')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='item'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'item'])}}"  aria-disabled="true">{{translate('messages.item')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='discount'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'discount'])}}"  aria-disabled="true">{{translate('messages.discount')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='settings'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'settings'])}}"  aria-disabled="true">{{translate('messages.settings')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='transaction'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{translate('messages.transaction')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'reviews'])}}"  aria-disabled="true">{{translate('messages.reviews')}}</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
        @endif
    </div>
    <!-- End Page Header -->