@extends('layouts.vendor.app')

@section('title',translate('messages.item_wise_report'))

@section('content')

<div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12 mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-filter-list"></i> {{translate('messages.item_wise_report')}} </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row card" style="border-radius: 10px">
            <div class="col-lg-12 pt-3">
                <form action="{{route('vendor.item.item-wise-report')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">{{translate('messages.export')}} {{translate('messages.data')}} by {{translate('messages.date')}}
                                    {{translate('messages.range')}}</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <input type="date" name="from" id="from_date" {{session()->has('from_date')?'value='.session('from_date'):''}}
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <input type="date" name="to" id="to_date" {{session()->has('to_date')?'value='.session('to_date'):''}}
                                       class="form-control" required>
                                <input type="hidden" name="type" id="type"
                                class="form-control" value="date_wise">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-block">{{translate('messages.export')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>            
        </div>
        <!-- End Stats -->
    </div>
@endsection

