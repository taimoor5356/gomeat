@extends('layouts.vendor.app')
@section('title',translate('messages.store_view'))
@push('css_or_js')
    <!-- Custom styles for this page -->
@endpush

@section('content')
<div class="content container-fluid"> 
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" >
                <h3 class="mb-0  text-capitalize position-absolute">{{translate('messages.my_shop')}} {{translate('messages.info')}} </h3>
            </div>
            <div class="card-body">
                @if($shop->cover_photo)
                <div class="row">
                    <div class="col-12"  style="max-height:250px; overflow-y: hidden;">
                         <img src="{{asset('storage/app/public/store/cover/'.$shop->cover_photo)}}" onerror="this.src='{{asset('public/assets/admin/img/900x400/img1.jpg')}}'" style="max-height:auto;width: 100%;">
                    </div>
                </div>
                @endif
                <div class="row mt-2">
                    @if($shop->image=='def.png')
                    <div class="col-md-4">
                        <img height="200" width="200"  class="rounded-circle border"
                        src="{{asset('public/assets/back-end')}}/img/shop.png"
                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                        alt="User Pic">
                    </div>
                    
                    @else
                    
                        <div class="col-md-4">
                            <img src="{{asset('storage/app/public/store/'.$shop->logo)}}" class="rounded-circle border"
                            onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                            height="200" width="200" alt="">
                        </div>

                    
                    @endif
                 
                    <!-- http://localhost/Food-multivendor/public/assets/admin/img/restaurant_cover.jpg -->
                    <div class="col-md-8 mt-4">
                        <span class="h4">{{translate('messages.name')}} : {{$shop->name}}</span><br>
                        <span class="h5">{{translate('messages.phone')}} : <a style="text-decoration:none; color:black;" href="tel:{{$shop->phone}}">{{$shop->phone}}</a></span><br>
                        <span class="h5">{{translate('messages.address')}} : {{$shop->address}}</span><br>
                        <span class="h5">{{translate('messages.gomeat_commission')}} : {{$shop->gm_commission}}%</span><br>
                        {{-- <span class="h5">{{translate('messages.service_fee')}} : {{\App\Models\BusinessSetting::where('key','service_fee')->first()->value}}%</span><br> --}}
                        <a class="btn btn-primary mt-1" href="{{route('vendor.shop.edit')}}">EDIT</a>
                    </div>
                </div>
                
               
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')
    <!-- Page level plugins -->
@endpush
