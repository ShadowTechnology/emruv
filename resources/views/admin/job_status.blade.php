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
                            Job Status
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/job_status/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="job_status" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Status Value</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                      <tr><th></th><th></th><th></th>
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
            var table = $('#job_status').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("job_status.data")}}',
                },
                columns: [
                    { data: 'status_value'},
                    { data: 'status_description'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;

                            return '<a href="{{URL::to('admin/job_status/edit')}}/'+tid+'"><i class="ft-edit"></i></a>';
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 3 }
                ]

            });

            $('#job_status tfoot th').each( function (index) {
                if(index != 3) {
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
