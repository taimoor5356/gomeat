@extends('layouts.admin.app')
@section('title','Add New Country')
@push('css_or_js')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="content container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{translate('messages.dashboard')}}</a></li>
            <li class="breadcrumb-item" aria-current="page">Countries</li>
            <li class="breadcrumb-item" aria-current="page">{{translate('messages.list')}}</li>
        </ol>
    </nav>
    <!-- Page Heading -->
    <div class="d-md-flex_ align-items-center justify-content-between mb-2">
        <div class="row">
            <div class="col-md-8">
                <h3 class="h3 mb-0 text-black-50">Countries {{translate('messages.list')}}</h3>
            </div>

            <div class="col-md-4">
                <button class="btn btn-primary float-right">
                    <i class="tio-add-circle"></i>
                    <span class="text">{{translate('messages.add')}} {{translate('messages.new')}}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px">
        <div class="col-md-12">
            <div class="card border-0 shadow-none">
                <div class="card-header px-0 d-block py-4">
                    <div class="row">
                        <div class="col-4">
                            <select name="country_id" id="country_id" class="form-control m-0 p-0">
                                <option value="">Select Country</option>
                                <option value="1">Pakistan</option>
                            </select>
                        </div>
                    </div>
                    <!-- <form action="javascript:" id="search-form"> 
                        @csrf
                        <div class="input-group input-group-merge input-group-flush">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('messages.search')}}" aria-label="Search">
                            <button type="submit" class="btn btn-light">{{translate('messages.search')}}</button>
                        </div>
                    </form> -->
                </div>
                <div class="card-body" style="padding: 0">
                    <div class="table-responsive">
                        <table id="data-table" class="table table-hover table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Short Name</th>
                                    <th>Currency Name</th>
                                    <th>Currency Sybmol</th>
                                    <th>GST</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<!-- <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script> -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    // Call the dataTables jQuery plugin
    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            searching: true,
            processing: true,
            // stateSave: true,
            serverSide: true,
            // bDestroy: true,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('admin.countries.index') }}",
                data: function(d) {
                    d.country_id = $('#country_id').val();
                }
            },
            columns: [{
                    data: 'name',
                    name: 'name',
                },
                {
                    data: 'short_name',
                    name: 'short_name',
                },
                {
                    data: 'currency_name',
                    name: 'currency_name',
                },
                {
                    data: 'currency_symbol',
                    name: 'currency_symbol',
                },
                {
                    data: 'gst',
                    name: 'gst',
                },
                {
                    data: 'action',
                    name: 'action',
                }
            ],
        });
        $(document).on('change', '#country_id', function() {
            table.draw();
        });
    });
</script>
@endpush