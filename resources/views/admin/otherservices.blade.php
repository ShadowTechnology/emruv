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
                           Service Provider Request Services
                        </h2>
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="services" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th>Service Provider Name</th>
                                        <th>Service Provider Mobile</th>
                                        <th>Requested On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                      <tr><th></th><th></th><th></th>
                                          <th></th><th></th><th></th>
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
            var table = $('#services').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("provider_request_services.data")}}',
                },
                columns: [
                    { data: 'service_name'},
                    { data: 'service_description'},
                    { data: 'user_name'},
                    /*{ data: 'mobile'}, servicers/info/58/2107110001 */
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var mobile = data.mobile;
                            var tid = data.service_provider_id;
                            var tcode = data.reg_no;
                            if(mobile != '')
                            return '<a href="{{URL::to('admin/servicers/info')}}/'+tid+'/'+tcode+'" target="_blank">'+mobile+'</a>';
                            else 
                            return '';
                        },

                    },
                    { data: 'created_at'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="javascript:void(0);" onclick="approveService('+tid+');"><i class="ft-check"></i></a>&nbsp; &nbsp; <a href="javascript:void(0);" onclick="rejectService('+tid+');"><i class="ft-x"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ]

            });

            $('#services tfoot th').each( function (index) {
                if(index != 5) {
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
        @if($rights['rights']['edit'] == 1)
        function approveService($id) {

            swal({
                    title: "Are you sure?",
                    text: "Do you want to Approve this Service",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary",
                    confirmButtonText: "Approve",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                },

                function(){

                    var request = $.ajax({
                        type: 'post',
                        url: " {{URL::to('admin/approveService')}}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        data:{

                            'id':$id,

                        },
                        encode: true
                    });
                    request.done(function (response) {

                        if(response.status =='SUCCESS'){

                            swal({
                                title: "Success",
                                text: "The Service Approved Successfully",
                                type: "success",

                            }, function () {

                                window.location.reload();

                            });

                        }else{

                            swal("Oops",response.message, "warning");
                        }

                    });
                    request.fail(function (jqXHR, textStatus) {

                        swal("Oops", "Something went to wrong..", "warning");

                    });
                });
        }

        function rejectService($id) {

            swal({
                    title: "Are you sure?",
                    text: "Do you want to Reject this Service",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary",
                    confirmButtonText: "Reject",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                },

                function(){

                    var request = $.ajax({
                        type: 'post',
                        url: " {{URL::to('admin/rejectService')}}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        data:{

                            'id':$id,

                        },
                        encode: true
                    });
                    request.done(function (response) {

                        if(response.status =='SUCCESS'){

                            swal({
                                title: "Success",
                                text: "The Service Rejected Successfully",
                                type: "success",

                            }, function () {

                                window.location.reload();

                            });

                        }else{

                            swal("Oops",response.message, "warning");
                        }

                    });
                    request.fail(function (jqXHR, textStatus) {

                        swal("Oops", "Something went to wrong..", "warning");

                    });
                });
        }
        @endif
    </script> 

@endsection
