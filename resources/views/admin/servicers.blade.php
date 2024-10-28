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
                            Service Providers
                        </h2>
                        <input type="hidden" name="status" id="status" value="{{$status}}">
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="users" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Country</th>
                                    <th>Referral Code</th>
                                    <th>Status</th>
                                    <th>Action</th>
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
            @if($rights['rights']['view'] == 1)
            var table = $('#users').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("servicers.data")}}',
                    data: function ( d ) {

                        var status  = $('#status').val();
                        $.extend(d, {status:status});

                    }
                },
                columns: [
                    { data: 'name'},
                    { data: 'email'},
                    { data: 'mobile'},
                    { data: 'country_code'},
                    { data: 'referal_code'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            var tcode = data.reg_no;
                            @if($rights['rights']['view'] == 1)
                            return '<a href="{{URL::to('admin/servicers/info')}}/'+tid+'/'+tcode+'"><i class="ft-eye"></i></a>&nbsp;<a href="{{URL::to('admin/servicers/edit')}}/'+tid+'/'+tcode+'"><i class="fas fa-edit"></i></a>&nbsp;<a target="_blank" href="{{URL::to('admin/servicers/bookings')}}/'+tid+'/'+tcode+'"><i class="ft-layout"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ],

            });

            $('#users tfoot th').each( function () {
                var title = $(this).text();
                if($(this).index() != 6)
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
            } );



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
