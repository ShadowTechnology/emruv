@extends('layouts.admin_master')
@section('content')

    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            Servicer Bookings
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
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts') 
     <script>

        $(function() {

            var table = $('#bookings').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("servicerbookings.data", ["id"=>$id, "code"=>$code])}}',
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

                            return '<a href="{{URL::to('admin/view/servicers/bookings')}}/'+tid+'/{{$id}}/{{$code}}"><i class="ft-eye"></i></a>';
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

        });
    </script>
@endsection
