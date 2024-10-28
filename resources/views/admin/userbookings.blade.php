@extends('layouts.admin_master') 
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
                            Bookings
                            @if($rights['rights']['add'] == 1)
                                <a href="#" data-toggle="modal" data-target="#smallModal" id="addzone"><button class="btn btn-primary" style="float: right;">Add</button></a> 
                            @endif
                        </h2>
                        <div class="row">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" id="status" required>
                                    <option value="0" @if($status == 0) selected @endif>All</option>
                                    <option value="4" @if($status == 4) selected @endif>Pendings</option>
                                    <option value="1" @if($status == 1) selected @endif>On Going</option>
                                    <option value="2" @if($status == 2) selected @endif>Completed</option>
                                    <option value="3" @if($status == 3) selected @endif>Cancelled</option>
                                </select>
                            </div>
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
                                    <th>User</th>
                                    <th>Service Provider</th>
                                    <th>Amount</th>
                                    <th>Job Date Slot</th>
                                    <th>Is Emergency</th>
                                    <th>Location Type</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Payment Date</th>
                                    <th>Payment Mode</th>
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
@if($rights['rights']['add'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add User Bookings</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/banks')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Category</label>
                                <div class="form-line"> 
                                    <input type="text" class="form-control" name="bank_name" id="bank_name" required>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Sub Category</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="position" id="position" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Service</label>
                                <div class="form-line"> 
                                    <input type="text" class="form-control" name="bank_name" id="bank_name" required>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Sub Service</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="position" id="position" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Job Date</label>
                                <div class="form-line"> 
                                    <input type="text" class="form-control" name="bank_name" id="bank_name" required>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Job Slot</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="position" id="position" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-line">
                                    <select class="form-control" name="status" id="status" required>
                                      <option value="ACTIVE">ACTIVE</option>
                                      <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
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
@section('scripts') 
    <script>

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('#bookings').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("userbookings.data", ["id"=>$id, "code"=>$code])}}',
                    data: function ( d ) {
                        var status  = $('#status').val();
                        var minDateFilter  = $('#datepicker_from').val();
                        var maxDateFilter  = $('#datepicker_to').val();
                        $.extend(d, {minDateFilter:minDateFilter, maxDateFilter:maxDateFilter, status:status});

                    }
                },
                columns: [
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;

                            return '<a href="{{URL::to('admin/view/users/bookings')}}/'+tid+'/{{$id}}/{{$code}}"><i class="ft-eye"></i></a>';
                        },

                    },
                    { data: 'user_id', name:'u.name'},
                    { data: 'service_provider_id', name:'sr.name'},
                    { data: 'total_amount', name:'total_amount'},
                    { data: 'job_date', name:'job_date'},
                    { data: 'is_emergency', name:'is_emergency'},
                    { data: 'location_type', name:'location_type'},
                    { data: 'status', name:'bk_booking.status'},
                    { data: 'payment_status', name:'payment_status'},
                    { data: 'payment_date', name:'payment_date'},
                    { data: 'payment_mode', name:'payment_mode'},

                ],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ],
                "order": [[ 4, "desc" ]],
                dom: 'Bfrtip',
                buttons: [
                    'excel'
                ],

            });

            $('#bookings tfoot th').each( function () {
                var title = $(this).text();
                if($(this).index() >=1)
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
            } );

            $('#status').on('change', function() {
                table.draw();
            });

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
            @endif
        });
    </script> 
@endsection
