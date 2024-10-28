@extends('layouts.admin_master')
@section('settings', 'active')
@section('content')

<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User Roles 
                        @if($rights['rights']['add'] == 1)
                        <a href="#" data-toggle="modal" data-target="#smallModal"><button class="btn btn-primary" style="float: right;">Add</button></a> 
                        @endif
                    </h4>        
                          
                </div>
                @if($rights['rights']['list'] == 1)
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    
                    <table class="table table-striped table-bordered tblcategory">
                      <thead>
                        <tr> 
                          <th class="no-sort">Edit</th>
                          <th>User Role</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tfoot>
                          <tr> 
                              <th></th>
                              <th></th>
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
    @if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add User Role</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/userroles')}}"
                                  method="post">

                        {{csrf_field()}}
                        <input type="hidden" name="id" id="id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Role</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="user_role" id="user_role" required>
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
@endif
@endsection

@section('scripts')

    <script>

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcategory').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("userroles.data")}}',
                },
                columns: [
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            var ref_code = data.ref_code;

                            @if($rights['rights']['edit'] == 1)
                                return '<a href="#" onclick="loadRole('+tid+')" title="Edit Role" class="btn btn-warning">Edit</a>';
                            @else 
                                return '';
                            @endif
                        },

                    },
                    { data: 'user_role'},
                    { data: 'status'},
                ],
                dom: 'Blfrtip',
                buttons: [
                    //'excel'
                    { 
                        extend: 'excel',
                        text: 'Export All',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("role_excel.data")}}/all',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'Userroles.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],
                "order": [],
                "columnDefs": [ {
                      "targets": 'no-sort',
                      "orderable": false,
                } ]

            });

            $('.tblcategory tfoot th').each( function (index) {
                var title = $(this).text();
                if(index >0) {
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

                           $('.tblcategory').DataTable().ajax.reload();

                           $('#smallModal').modal('hide');

                           $("#style-form")[0].reset();

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

        function loadRole(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/userroles')}}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data:{
                    code:id,
                },
                dataType:'json',
                encode: true
            });
            request.done(function (response) {

                $('#id').val(response.data.id);
                $('#user_role').val(response.data.user_role);
                $('#status').val(response.data.status);
                $('#smallModal').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }


    </script>

@endsection
