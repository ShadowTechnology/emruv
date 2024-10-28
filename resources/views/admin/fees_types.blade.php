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
                            Fees Types
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/fees_types/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="fees_types" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Fees Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                      <tr><th></th><th></th><th></th>
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
            var table = $('#fees_types').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("fees_types.data")}}',
                },
                columns: [
                    { data: 'fees_type'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;

                            return '<a href="{{URL::to('admin/edit/fees_types')}}/'+tid+'"><i class="ft-edit"></i></a>';
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 2 }
                ]

            });
            $('#fees_types tfoot th').each( function (index) {
                if(index != 2) {
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
