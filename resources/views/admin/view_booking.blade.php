@extends('layouts.admin_master')
@section('content')
    <style type="text/css">
        span.fa.fa-star.checked {
            color: orange;
        }
    </style>
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            View Bookings
                            <a onclick="generatePDF('invoice')"><button class="btn btn-info ml-2 pull-right">Download pdf</button></a>
                            <a href="{{URL('/')}}/admin/bookings" class="btn btn-info float-right" >BACK</a>
                        </h2>

                    </div>
                    <div class="card-body" id="invoice">
                        <div class="table-responsive">
                            <table id="bookings" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Reference Number / Status</th>
                                    <th>Customer </th>
                                    <th>Service Provider </th>
                                    <th>Location</th>
                                </tr>
                                <tr>
                                    <td>{{$normal_bookings['ref_no']}}<br/>
                                        <b>Job Status: {{$normal_bookings['status']}}</b><br/>
                                        <b>Payment Status: {{$normal_bookings['payment_status']}}</b>
                                    </td>
                                    <td>{{$normal_bookings['customer']['name']}}<br/>
                                        {{$normal_bookings['customer']['email']}}<br/>
                                        {{$normal_bookings['customer']['mobile']}}</td>
                                    <td>{{$normal_bookings['service_provider']['name']}}<br/>
                                        {{$normal_bookings['service_provider']['email']}}<br/>
                                        {{$normal_bookings['service_provider']['mobile']}}</td>
                                    <!-- <td>@if($normal_bookings['location_type'] == 1)
                                        Provider's Location
                                        @elseif($normal_bookings['location_type'] == 2)
                                        Customer's Location
                                        @elseif($normal_bookings['location_type'] == 3)
                                        Online
                                        @endif
                                    </td> -->
                                    <td>
                                        {{$normal_bookings['is_user_address']['user_name']}}<br/>
                                        {{$normal_bookings['is_user_address']['address']}}<br/>
                                        {{$normal_bookings['is_user_address']['city']}}, 
                                        {{$normal_bookings['is_user_address']['pin_code']}}<br/>
                                        {{$normal_bookings['is_user_address']['country']}}<br/>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Job Date</th>
                                    <th>Slot</th>
                                    <th>Customer Rating</th>
                                    <th>Provider Rating</th>
                                </tr>
                                <tr> 
                                    <td>{{date('Y-m-d', strtotime($normal_bookings['job_date']))}}</td>
                                    <td>{{$normal_bookings['is_slot']->slot_name}} {{$normal_bookings['is_slot']->period_name}}</td>
                                    <td> {{$normal_bookings['rating_comment']}} <br/>
                                        @if(ceil($normal_bookings['rating']) == 5)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                        @elseif(ceil($normal_bookings['rating']) == 4)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['rating']) == 3)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['rating']) == 2)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['rating']) == 1)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @endif
                                    </td>
                                    <td>
                                        {{$normal_bookings['provider_rating_comment']}}<br/>
                                        @if(ceil($normal_bookings['provider_rating']) == 5)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                        @elseif(ceil($normal_bookings['provider_rating']) == 4)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['provider_rating']) == 3)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['provider_rating']) == 2)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @elseif(ceil($normal_bookings['provider_rating']) == 1)

                                            <span class="fa fa-star checked"></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star "></span>
                                            <span class="fa fa-star"></span>

                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="4">Services</th>
                                </tr>  <?php //echo "<pre>"; print_r($normal_bookings); exit ?>
                                @php($itemtotal = 0)
                                @if(!empty($normal_bookings['book_items']))
                                <tr><td colspan="4">
                                    <table class="table table-bordered table-hover">
                                        <tr>
                                            <th>Service</th>
                                            <th>Sub Service</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Amount</th>
                                        </tr>
                                    
                                @foreach($normal_bookings['book_items'] as $k=>$v1)
                                   
                                    <tr>
                                        <td>{{$v1['mainCategoryName']}}</td>
                                        <td>{{$v1['sub_service_name']}}</td>
                                        <td>{{$v1['qty']}}</td>
                                        <td>{{$v1['price']}}</td>
                                        <td>{{$v1['amount']}}</td>
                                    </tr>
                                    @php($itemtotal += $v1['amount'])
                                @endforeach
                                @endif
                                <tr>
                                    <th colspan="4" style="text-align: right;"> Sub Total </th>
                                    <th>{{$itemtotal - $normal_bookings['tax_total']}}</th>
                                </tr>
                                <tr><th colspan="4" style="text-align: right;"> Tax</th> <th>{{$normal_bookings['tax_total']}}</th> </tr>
                                @php($feestotal = 0)
                                @if(!empty($normal_bookings['additional_fees']))
                                <tr><th colspan="4"> Fees</th> <th>Amount</th> </tr>
                                
                                @foreach($normal_bookings['additional_fees'] as $k=>$v1)
                                   
                                    <tr>
                                        <td colspan="4">{{$v1->fees_name}}</td>
                                        <td>{{$v1->fees_value}}</td>
                                    </tr>
                                    @php($feestotal += $v1->fees_value)
                                @endforeach
                                    <tr><th colspan="4" style="text-align: right;"> Additional Fees</th> <th>{{$feestotal}}</th> </tr>
                                @endif
                                
                                <tr><th colspan="4" style="text-align: right;"> Additional Charge<br/>{{$normal_bookings['additional_charge_text']}}</th> <th>{{$normal_bookings['additional_charge']}}</th> </tr>
                                <tr><th colspan="4" style="text-align: right;"> Total</th> <th>{{$normal_bookings['total_amount']}}</th> </tr>
                                    </table>
                                </td></tr>
                                <tr>
                                    <td colspan="2"> Customer Images <br/>
                                        <div class="card profile-with-cover">
                                            <div class="owl-carousel owl-carousel-slider" id="property_image">

                                              @if(isset($normal_bookings['users_images']) && count($normal_bookings['users_images'])>0)
                                              @foreach($normal_bookings['users_images'] as $pk => $pv)
                                              <div class="item">
                                                  <a href="#"><img class="img-fluid" src="{{$pv['image']}}" alt="Image" style="object-fit: fill;width: 100%;height: 250px; "></a>
                                              </div>
                                              @endforeach
                                              @endif

                                            </div>
                                        </div>
                                    </td>
                                    <td colspan="2">  Provider Images <br/>
                                        <div class="card profile-with-cover">
                                            <div class="owl-carousel owl-carousel-slider" id="property_image">

                                              @if(isset($normal_bookings['servicers_images']) && count($normal_bookings['servicers_images'])>0)
                                              @foreach($normal_bookings['servicers_images'] as $pk => $pv)
                                              <div class="item">
                                                  <a href="#"><img class="img-fluid" src="{{$pv['image']}}" alt="Image" style="object-fit: fill;width: 100%;height: 250px; "></a>
                                              </div>
                                              @endforeach
                                              @endif

                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="{!!asset('/public/js/pdfscript.js') !!}"></script>
<script type="text/javascript">
    function generatePDF(id) {
         
        // Choose the element that our invoice is rendered in.
        const element = document.getElementById(id);
         
        // clone the element
        var clonedElement = element.cloneNode(true);
        
        // change display of cloned element 
        $(clonedElement).css("display", "block");
        
        // Choose the element and save the PDF for our user.
        // Choose the clonedElement and save the PDF for our user.
        html2pdf(clonedElement);
        
        // remove cloned element
        clonedElement.remove();
        // html2pdf()
        //   .from(element)
        //   .save();
      }
</script>
@endsection