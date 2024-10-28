@extends('layouts.admin_master')
@section('content')

    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            View Servicer Bookings

                            <a href="{{URL('/')}}/admin/servicers/bookings/{{$uid}}/{{$code}}" class="btn btn-info float-right" >BACK</a>
                        </h2>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bookings" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Reference Number</th>
                                    <th>User </th>
                                    <th>Service Provider </th>
                                    <th>Location</th>
                                </tr>
                                <tr>
                                    <td>{{$normal_bookings['ref_no']}}</td>
                                    <td>{{$normal_bookings['customer']['name']}}<br/>
                                        {{$normal_bookings['customer']['email']}}<br/>
                                        {{$normal_bookings['customer']['mobile']}}</td>
                                    <td>{{$normal_bookings['service_provider']['name']}}<br/>
                                        {{$normal_bookings['service_provider']['email']}}<br/>
                                        {{$normal_bookings['service_provider']['mobile']}}</td>
                                    <td>@if($normal_bookings['location_type'] == 1)
                                        Provider's Location
                                        @elseif($normal_bookings['location_type'] == 2)
                                        Customer's Location
                                        @elseif($normal_bookings['location_type'] == 3)
                                        Online
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Job Date</th>
                                    <th>Slot</th>
                                    <th>Is Emergency</th>
                                    <th>Address</th>
                                </tr>
                                <tr>
                                    <td>{{date('Y-m-d', strtotime($normal_bookings['job_date']))}}</td>
                                    <td>{{$normal_bookings['is_slot']->slot_name}} {{$normal_bookings['is_slot']->period_name}}</td>
                                    <td>@if($normal_bookings['is_emergency'] == 0) No @else Yes @endif</td>
                                    <td>@if(isset($normal_bookings['is_cart_address']) && count($normal_bookings['is_cart_address'])>0)
                                            {{$normal_bookings['is_cart_address']['user_name']}}<br/>
                                            {{$normal_bookings['is_cart_address']['door_no']}} {{$normal_bookings['is_cart_address']['street_name']}} 
                                            @if($normal_bookings['is_cart_address']['mode'] == 'manual')
                                            {{$normal_bookings['is_cart_address']['pin_code']}} <br/>
                                            {{$normal_bookings['is_cart_address']['city']}} 
                                            {{$normal_bookings['is_cart_address']['pinarea']}} 
                                            @else 
                                            {{$normal_bookings['is_cart_address']['pincode']}}
                                            {{$normal_bookings['is_cart_address']['city_name']}} 
                                            {{$normal_bookings['is_cart_address']['area_name']}}
                                            @endif
                                            <br/>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="4">Services</th>
                                </tr>
                                @foreach($normal_bookings['book_services'] as $k=>$v)
                                <tr>
                                    <td colspan="4">{{$v['service_name']}} @if($v['qty']>0) {{$v['qty']}} @endif</td>
                                </tr>
                                    @foreach($v['book_sub_services'] as $k1=>$v1)
                                    <tr>
                                        <td>{{$v1['sub_service_name']}}</td>
                                        <td>{{$v1['qty']}}</td>
                                        <td>{{$v1['price']}}</td>
                                        <td>{{$v1['amount']}}</td>
                                    </tr>
                                    @endforeach
                                <tr>
                                    <td colspan="3" style="text-align: right;"> Sub Total </td>
                                    <td>{{$v['service_total']}}</td>
                                </tr>
                                @endforeach
                                
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection