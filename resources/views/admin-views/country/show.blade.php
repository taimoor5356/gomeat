@extends('layouts.admin.app')
@section('title','Countries List')
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
                <h3 class="h3 mb-0 text-black-50">{{$country->name}}'s States {{translate('messages.list')}}</h3>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px">
        <div class="col-md-12">
            <div class="card border-0 shadow-none">
                <div class="card-body" style="padding: 0">
                    <div class="table-responsive">
                        <table id="data-table" class="table table-hover table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Store Online Payment</th>
                                    <th>Store Cash Payment</th>
                                    <th>Restaurant Online Payment</th>
                                    <th>Restaurant Cash Payment</th>
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

    <!-- Add New Country Modal -->
    <div class="modal fade" id="addNewCountryModal" tabindex="-1" role="dialog" aria-labelledby="addNewCountryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-new-countryLabel">Add Country Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.countries.store')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input name="name" type="text" class="form-control" id="name" placeholder="Enter Name">
                                </div>
                                <div class="form-group">
                                    <label for="short_name">Short Name</label>
                                    <input name="short_name" type="text" class="form-control" id="short_name" placeholder="Enter Short Name">
                                </div>
                                <div class="form-group">
                                    <label for="currency_name">Currency Name</label>
                                    <input name="currency_name" type="text" class="form-control" id="currency_name" placeholder="Enter Currency Name">
                                </div>
                                <div class="form-group">
                                    <label for="currency_symbol">Currency Symbol</label>
                                    <input name="currency_symbol" type="text" class="form-control" id="currency_symbol" placeholder="Enter Currency Symbol">
                                </div>
                                <div class="form-group">
                                    <label for="gst">GST</label>
                                    <input name="gst" type="text" class="form-control" id="gst" placeholder="Enter GST">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Save changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Country Modal -->
    <div class="modal fade" id="editCountryModal" tabindex="-1" role="dialog" aria-labelledby="editCountryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-new-countryLabel">Edit State Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body editModalBody">
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div> -->
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
        @if(session('success'))
        toastr.success('Successful', {
            CloseButton: true,
            ProgressBar: true
        });
        @elseif(session('error'))
        toastr.error('Something went wrong', {
            CloseButton: true,
            ProgressBar: true
        });
        @endif
        var countryId = "{{$country->id}}";
        var url = "{{route('admin.countries.states', [':country_id'])}}";
        url = url.replace(':country_id', countryId);
        var table = $('#data-table').DataTable({
            searching: true,
            processing: true,
            // stateSave: true,
            serverSide: true,
            // bDestroy: true,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: url
            },
            columns: [{
                    data: 'name',
                    name: 'name',
                },
                {
                    data: 'store_online_payment',
                    name: 'store_online_payment',
                },
                {
                    data: 'store_cash_payment',
                    name: 'store_cash_payment',
                },
                {
                    data: 'restaurant_online_payment',
                    name: 'restaurant_online_payment',
                },
                {
                    data: 'restaurant_cash_payment',
                    name: 'restaurant_cash_payment',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false, // Make sure 'action' column is not sortable
                    searchable: false, // Make sure 'action' column is not searchable
                    render: function(data, type, row, meta) {
                        return '<button class="mr-1 btn btn-info edit-btn">Edit</button>';
                    },
                }
            ],
        });

        // Add click event handler for the 'Edit' button
        $('#data-table').on('click', '.edit-btn', function() {
            var row = $(this).closest('tr');
            var data = $('#data-table').DataTable().row(row).data();

            // Assuming you have a modal with the id 'editModal'
            $('#editCountryModal').modal('show');

            // Populate the modal with data
            var url = "{{route('admin.countries.state.update', [':id'])}}";
            url = url.replace(':id', data.state_id);
            $('#editCountryModal').find('.editModalBody').html(
                `
                <form action="` + url + `" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input name="name" type="text" class="form-control" id="name" placeholder="Enter Name" value="` + data.name + `">
                            </div>
                            <div class="form-group">
                                <label for="store_online_payment">Store Online Payment</label>
                                <input name="store_online_payment" type="text" class="form-control" id="store_online_payment" placeholder="Enter Store Online Payment" value="` + data.store_online_payment + `">
                            </div>
                            <div class="form-group">
                                <label for="store_cash_payment">Store Cash Payment</label>
                                <input name="store_cash_payment" type="text" class="form-control" id="store_cash_payment" placeholder="Enter Store Cash Payment" value="` + data.store_cash_payment + `">
                            </div>
                            <div class="form-group">
                                <label for="restaurant_online_payment">Restaurant Online Payment</label>
                                <input name="restaurant_online_payment" type="text" class="form-control" id="restaurant_online_payment" placeholder="Enter Restaurant Online Payment" value="` + data.restaurant_online_payment + `">
                            </div>
                            <div class="form-group">
                                <label for="restaurant_cash_payment">Restaurant Cash Payment</label>
                                <input name="restaurant_cash_payment" type="text" class="form-control" id="restaurant_cash_payment" placeholder="Enter Restaurant Cash Payment" value="` + data.restaurant_cash_payment + `">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save changes</button>
                        </div>
                    </div>
                </form>`
            );
        });
    });
</script>
@endpush