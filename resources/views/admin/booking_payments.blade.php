@extends('layouts.admin_master') 
@section('reports', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            Booking Payments
                        </h2>
                        <div class="row">
                            <p id="date_filter">
                                <span id="date-label-from" class="date-label">From: </span><input class="date_range_filter date" type="text" id="datepicker_from" />
                                <span id="date-label-to" class="date-label">To:<input class="date_range_filter date" type="text" id="datepicker_to" /></span>
                            </p>
                        </div>
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bookings" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Booking No</th>
                                    <th>Service Provider</th>
                                    <th>Customer</th>
                                    <th>Sub Total</th>
                                    <th>Total</th>
                                    <th>Job Date</th>
                                    <th>Job Slot</th> 
                                    <th>Payment Date</th>
                                    <th>Payment Mode</th>
                                    <th>Commmission Percentage</th> 
                                    <th>Commission Amount</th>
                                    <th>Servicer Amount</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th> 
                                    <th></th>
                                    <th></th>
                                    <th></th> 
                                </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
@if($rights['rights']['add'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add Payout</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/savepayout')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="booking_id" id="booking_id">
                    <div class="modal-body"> 
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                 <label class="form-label">Payment Mode *</label>
                            
                                <select class="form-control" name="payment_mode" id="payment_mode" required>
                                    
                                    <option value="CASH" selected>CASH</option>
                                
                                    <option value="CHEQUE">CHEQUE</option>
                                    
                                    <option value="ONLINE">ONLINE</option>
                                    
                                </select>
                            </div>

                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Amount *</label>
                                <span class="form-control" name="amount_txt" id="amount_txt"></span>
                                <input type="hidden" class="form-control" name="amount" id="amount">
                            </div>
                    
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="text" class="form-control date" name="payout_date" id="payout_date" required autocomplete="off">
                            </div>
                            
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Transaction Details *</label>
                                <textarea id="transaction_details" name="transaction_details" class="form-control" autocomplete="off"></textarea>
                            </div>

                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Comments *</label>
                                <textarea id="comments" name="comments" class="form-control" autocomplete="off"></textarea>
                            </div>
 
                        </div>
                    </div>
                    <div class="modal-footer">
                       <button type="sumbit" class="btn btn-link waves-effect" id="add_style">SAVE</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts') 
    <script>

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('#bookings').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("booking_payments.data")}}',
                    data: function ( d ) { 
                        var minDateFilter  = $('#datepicker_from').val();
                        var maxDateFilter  = $('#datepicker_to').val();
                        $.extend(d, {minDateFilter:minDateFilter, maxDateFilter:maxDateFilter});

                    }
                }, 
                columns: [
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            var ref_no = data.ref_no;
                            var amount = data.servicer_amount;
                            var provider_settlement = data.provider_settlement;
                            if(provider_settlement == 'PENDING') {
                                return '<a href="javascript:void(0);" onclick="makePayment('+tid+','+ref_no+','+amount+');">Make Payment</a>';
                            }   else {
                                return 'PAID';
                            }
                            
                        },

                    },
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            var ref_no = data.ref_no;

                            return '<a href="{{URL::to('admin/view/bookings')}}/'+tid+'">'+ref_no+'</a>&nbsp; &nbsp; ';
                        },
                        name:'ref_no'
                    }, 
                    { data: 'provider_name', name:'sp.name'},
                    { data: 'user_name', name:'u.name'},
                    { data: 'sub_total', name:'sub_total'},
                    { data: 'total_amount', name:'em_booking.total_amount'},
                    { data: 'job_date', name:'job_date'},
                    { data: 'name', name:'em_slots.slot_name'}, 
                    { data: 'payment_date', name:'em_booking.payment_date'},
                    { data: 'payment_mode', name:'em_booking.payment_mode'}, 
                    { data: 'commission_percentage', name:'commission_percentage'}, 
                    { data: 'commission_amount', name:'commission_amount'},
                    { data: 'servicer_amount', name:'em_service_provider_payments.total_amount'},   
    
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ],
                "order": [[ 1, "desc" ]],
                dom: 'Blfrtip',
                buttons: [ 
                    { 
          
                        extend: 'excel',
                        text: 'Export Excel',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("booking_payments_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'BookingPayments.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],

            });

            $('#bookings tfoot th').each( function () {
                var title = $(this).text();
                if($(this).index() >=1)
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
            } );
 
            $("#datepicker_from").datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
            }).change(function() {
                table.draw();
            }).keyup(function() {
                table.draw();
            });

            $("#datepicker_to").datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
            }).change(function() {
                table.draw();

            }).keyup(function() {
                table.draw();
            });

            // Apply the search
            table.columns().every( function () {
                var that = this;

                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                                .search( this.value )
                                .draw();
                    }
                } );
            } );

            $("#payout_date").datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
            });
            @endif
            @if($rights['rights']['add'] == 1)
            $('#add_style').on('click', function () {

                var options = {

                    beforeSend: function (element) {

                        $("#add_style").text('Processing..');

                        $("#add_style").prop('disabled', true);

                    },
                    success: function (response) {



                        $("#add_style").prop('disabled', false);

                        $("#add_style").text('SUBMIT');

                        if (response.status == "SUCCESS") {

                           swal('Success',response.message,'success');

                           $('#bookings').DataTable().ajax.reload();

                           $('#smallModal').modal('hide');

                        }
                        else if (response.status == "FAILED") {

                            swal('Oops',response.message,'warning');

                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $("#add_style").prop('disabled', false);

                        $("#add_style").text('SUBMIT');

                        swal('Oops','Something went to wrong.','error');

                    }
                };
                $("#style-form").ajaxForm(options);
            });
            @endif
        });

        function makePayment(id, ref_no, amount){
            $('#booking_id').val(id);
            $('#amount').val(amount);
            $('#amount_txt').text(amount);
            $('#comments').val('Payment for the Booking #'+ref_no);
            $('#smallModal').modal('show');
        }

    </script> 
@endsection
