@extends('store_owner_views.layouts.app')
@section('page_title', 'Orders List - GoMeat')
@section('styles_css')
<style>
    .font-sm {
        font-size: 12px;
        color: black;
        margin: 0px;
        padding: 0px;
    }

    .scrollable-container {
        overflow-x: auto;
        white-space: nowrap;
    }

    .scrollable-container::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .scrollable-container::-webkit-scrollbar-thumb {
        background-color: #b3b5ff;
    }

    .scrollable-container::-webkit-scrollbar-thumb:hover {
        background-color: #696cff;
    }

    .scrollable-container::-webkit-scrollbar-track {
        background-color: #f0f0f0;
    }

    /* Additional styling for the thin scrollbar */
    .scrollable-container {
        scrollbar-width: thin;
        scrollbar-color: #888888 #f0f0f0;
    }
</style>
@endsection
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                {{Auth::user()}}
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollable-container overflow-auto py-2">
                                    <div class="row flex-nowrap">
                                        <div class="col-2">
                                            <a href="#">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-timer text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Pending</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-check-circle text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Confirmed</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-run text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Processing</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bxs-flag-checkered text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Ready for Delivery</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bxs-truck text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">OTW Items</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-check-square text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Delivered</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-revision text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Refunded</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a href="">
                                                <div class="report-list">
                                                    <div class="report-list-item rounded-2 p-2">
                                                        <div class="d-flex align-items-start">
                                                            <div class="report-list-icon me-2 bg-white rounded py-2">
                                                                <!-- <img src="../../assets/svg/icons/paypal-icon.svg" width="22" height="22" alt="Paypal"> -->
                                                                <span class="avatar-initial rounded bg-white p-2"><i class="bx bx-time-five text-primary"></i></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                                                                <div class="d-flex flex-column">
                                                                    <span class="font-sm">Scheduled</span>
                                                                    <h5 class="font-sm">48</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-3">
                                        <label class="form-label" for="order_status">Order Status</label>
                                        <select name="order_status" id="order_status" class="form-control select2">
                                            <option value="" selected>Select Order Status</option>
                                            <option value="">All</option>
                                            <option value="">Pending</option>
                                            <option value="">Confirmed</option>
                                            <option value="">Processing</option>
                                            <option value="">OTW Items</option>
                                            <option value="">Delivered</option>
                                            <option value="">Refunded</option>
                                            <option value="">Ready for Delivery</option>
                                            <option value="">Scheduled</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col-12">
                                <h4>All Orders</h4>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped datatable">
                                        <thead>
                                            <th>SR</th>
                                            <th>Order</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Payment Status</th>
                                            <th>Total</th>
                                            <th>Order Status</th>
                                            <th>Order Type</th>
                                            <th>Actions</th>
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
        </div>
    </div>
</div>
@endsection
@section('scripts')
@include('store_owner_views.orders._js')
@endsection