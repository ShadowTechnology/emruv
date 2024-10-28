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
                            Payouts
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
                                    <th>Booking No</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                    <th>Commission</th>
                                    <th>Provider</th>
                                    <th>Payout Date</th> 
                                    <th>Mode</th> 
                                    <th>Comments</th> 
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
                    "url": '{{route("payouts.data")}}',
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

                            return '<a href="{{URL::to('admin/view/bookings')}}/'+tid+'">'+ref_no+'</a>&nbsp; &nbsp; ';
                        },
                        name:'ref_no'
                    }, 
                    { data: 'transaction_amount', name:'transaction_amount'},
                    { data: 'transaction_details', name:'transaction_details'},
                    { data: 'commission_amount', name:'commission_amount'},
                    { data: 'service_provider.mobile', name:'users.mobile'},
                    { data: 'payout_date', name:'payout_date'},
                    { data: 'mode', name:'mode'},  
                    { data: 'comments', name:'comments'},  
    
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ],
                "order": [[ 0, "desc" ]],
                dom: 'Blfrtip',
                buttons: [ 
                    { 
          
                        extend: 'excel',
                        text: 'Export Excel',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("payouts_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'Payouts.xls');
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
