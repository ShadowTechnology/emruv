@extends('layouts.admin_master')
@section('users', 'active')
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
                  <h4 class="card-title">Admin Users 
                    @if($rights['rights']['add'] == 1)
                        <a href="#" data-toggle="modal" data-target="#smallModal" id="addroleuser"><button class="btn btn-primary" style="float: right;">Add Admin User</button></a> 
                    @endif
                  </h4>        
                          
                </div>
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                      <div class="table-responsicve">
                    <table class="table table-striped table-bordered tblcategory">
                      <thead>
                        <tr>
                          <th class="no-sort">Edit</th>
                          <th>User Role</th>
                          <th>Name</th>
                          <th>Email</th>
                          <th>Mobile</th>
                          <!-- <th>Joined Date</th> -->
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th></th>
                              <th></th>
                              <th></th>
                              <th></th>
                              <th></th>
                              <!-- <th></th> -->
                              <th></th>
                            </tr>
                      </tfoot>
                      <tbody>
                        
                      </tbody>
                      
                    </table></div>
                </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
    </section>
@endif

@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Admin User</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/roleusers')}}"
                                  method="post">

                        {{csrf_field()}}
                        <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Role</label>
                                <div class="form-line">
                                    <select name="userrole" id="userrole" class="form-control" required>
                                        <option value="">Select User Role</option>
                                        @if(!empty($roles))
                                            @foreach($roles as $k => $v)
                                                <option value="{{$v->ref_code}}">{{$v->user_role}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Name</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="name" id="name" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Email</label>
                                <div class="form-line">
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Mobile</label>
                                <div class="form-line">
                                    <input type="number" class="form-control" name="mobile" id="mobile" required minlength="8" maxlength="12">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Password</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="password">
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
                    "url": '{{route("roleusers.data")}}',
                },
                columns: [
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                                return '<a href="#" onclick="loadRoleUser('+tid+')" title="Edit Role User" class="btn btn-warning">Edit</a>';
                            @else 
                                return '';
                            @endif
                        },

                    },
                    { data: 'user_role', 'name':'em_userroles.user_role'},
                    { data: 'name', 'name':'users.name'},
                    { data: 'email', 'name':'users.email'},
                    { data: 'mobile', 'name':'users.mobile'}, 
                    /*{ data: 'created_at', 'name':'users.created_at'},*/
                    { data: 'status', 'name':'users.status'},
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
            $('#addroleuser').on('click', function () {
                $("#style-form")[0].reset();
            });
            @if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
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

                           $('#id').val('');
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

        function loadRoleUser(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/roleusers')}}",
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
                $('#userrole').val(response.data.user_type);
                $('#name').val(response.data.name);
                $('#email').val(response.data.email);
                $('#mobile').val(response.data.mobile);
                $('#status').val(response.data.status);
                $('#smallModal').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }


    </script>

@endsection
