@extends('layouts.admin_master')
@section('mastersettings', 'active')
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
                            Cancellation Policies
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/cancellation_policy/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="cancellation_policy" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Policy Type</th>
                                        <th>Description</th>
                                        <th>Time</th>
                                        <th>Is Refund Avail</th>
                                        <th>Refund Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                      <tr><th></th><th></th><th></th>
                                          <th></th><th></th><th></th>
                                          <th></th>
                                      </tr>
                                </tfoot>
                                <tbody>
                        
                                </tbody>
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
            var table = $('#cancellation_policy').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("cancellation_policies.data")}}',
                },
                columns: [
                    { data: 'policy_type'},
                    { data: 'policy_description'},
                    { data: 'policy_hours'},
                    { data: 'is_refund_avail'},
                    { data: 'refund_amount'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/edit/cancellation_policy')}}/'+tid+'"><i class="ft-edit"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]

            });

            $('#cancellation_policy tfoot th').each( function (index) {
                if(index != 6) {
                    var title = $(this).text();
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
                }
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